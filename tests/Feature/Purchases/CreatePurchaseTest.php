<?php

namespace Tests\Feature\Purchases;

use App\Models\Consumable;
use App\Models\InvoiceType;
use App\Models\LegalPerson;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CreatePurchaseTest extends TestCase
{
    public static function bitrix_responses(): array
    {
        return [
            'success' => [200, '{"result":12345}', true],
            'unauthorized' => [401, '{"error":"INVALID_CREDENTIALS"}', false],
            'server error' => [500, 'Internal Server Error', false],
            'application error' => [200, '{"error":"ACCESS_DENIED"}', false],
        ];
    }

    #[DataProvider('bitrix_responses')]
    public function test_creation_and_retry_use_configured_url_and_correct_form_encoding(int $status, string $body, bool $success): void
    {
        config(['services.bitrix.url' => 'https://bitrix.example.test']);
        $history = [];
        $handler = HandlerStack::create(new MockHandler([
            new Response($status, [], $body),
            new Response(200, [], '{"result":12345}'),
        ]));
        $handler->push(Middleware::history($history));
        $this->app->instance(Client::class, new Client(['handler' => $handler]));

        $publicPath = public_path();
        $disk = Storage::fake('purchase-create-test');
        $user = User::factory()->superuser()->create(['bitrix_id' => 42]);
        $user->setBitrixToken('test-token');
        $user->save();
        $consumable = Consumable::factory()->create(['qty' => 3]);
        $this->app->usePublicPath($disk->path(''));

        try {
            $response = $this->actingAs($user)->post(route('purchases.store'), [
                'invoice_number' => 'Bitrix-request-test', 'final_price' => 20,
                'delivery_cost' => 0, 'comment' => 'Request test',
                'supplier_id' => Supplier::factory()->create()->id,
                'legal_person_id' => LegalPerson::create(['name' => 'Test company'])->id,
                'invoice_type_id' => InvoiceType::create(['name' => 'Test type'])->id,
                'invoice_file' => UploadedFile::fake()->createWithContent('invoice.txt', 'Test invoice'),
                'assets' => '[]',
                'consumables' => json_encode([[
                    'id' => 1, 'consumable_id' => $consumable->id,
                    'consumable' => $consumable->name, 'quantity' => 2,
                    'purchase_cost' => 10, 'nds' => 0,
                ]]),
            ]);
            $response->assertRedirect(route('purchases.index'))->assertSessionHasNoErrors();
            $purchase = Purchase::where('invoice_number', 'Bitrix-request-test')->sole();
            $this->assertSame($success ? Purchase::INPROGRESS : Purchase::ERROR, $purchase->status);

            if (! $success) {
                $response->assertSessionHas('error', trans('general.bitrix_send_failed'));
                $this->assertNull($purchase->bitrix_id);
                $path = 'uploads/purchases/'.$purchase->bitrix_send_json;
                $payload = json_decode($disk->get($path), true);
                $payload['headers']['Content-Type'] = 'multipart/form-data';
                $disk->put($path, json_encode($payload));

                $this->actingAsForApi($user)->postJson(route('api.purchases.resend', $purchase))
                    ->assertOk()->assertStatusMessageIs('success');
            } else {
                $response->assertSessionHas('success');
            }

            $this->assertCount($success ? 1 : 2, $history);
            foreach ($history as $transaction) {
                $request = $transaction['request'];
                $this->assertSame('https://bitrix.example.test/rest/42/test-token/lists.element.add.json/', (string) $request->getUri());
                $this->assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('Content-Type'));
                parse_str((string) $request->getBody(), $fields);
                $this->assertSame('Bitrix-request-test', $fields['FIELDS']['NAME']);
                $this->assertSame(base64_encode('Test invoice'), $fields['FIELDS']['PROPERTY_143'][1]);
            }
            $this->assertEquals(12345, $purchase->fresh()->bitrix_id);
            $this->assertSame(Purchase::INPROGRESS, $purchase->fresh()->status);
            $this->assertDatabaseCount('purchases', 1);
            $this->assertSame(3, $consumable->fresh()->qty);
        } finally {
            $this->app->usePublicPath($publicPath);
        }
    }

    public function test_missing_bitrix_credentials_retains_purchase_with_error_status(): void
    {
        $publicPath = public_path();
        $disk = Storage::fake('purchase-create-test');
        $consumable = Consumable::factory()->create();
        $user = User::factory()->superuser()->create(['bitrix_id' => null, 'bitrix_token' => null]);
        $this->app->usePublicPath($disk->path(''));

        try {
            $this->actingAs($user)->from(route('purchases.create'))
                ->post(route('purchases.store'), [
                    'invoice_number' => 'Creation-test',
                    'final_price' => 20,
                    'delivery_cost' => 0,
                    'comment' => 'Creation test',
                    'supplier_id' => Supplier::factory()->create()->id,
                    'legal_person_id' => LegalPerson::create(['name' => 'Creation company'])->id,
                    'invoice_type_id' => InvoiceType::create(['name' => 'Creation type'])->id,
                    'invoice_file' => UploadedFile::fake()->createWithContent('invoice.txt', 'Test invoice'),
                    'assets' => '[]',
                    'consumables' => json_encode([[
                        'id' => 1, 'consumable_id' => $consumable->id,
                        'consumable' => $consumable->name, 'quantity' => 2,
                        'purchase_cost' => 10, 'nds' => 0,
                    ]]),
                ])->assertRedirect(route('purchases.create'))
                ->assertSessionHasNoErrors()
                ->assertSessionHas('error', trans('admin/users/table.bitrix_token_invalid'));

            $purchase = Purchase::where('invoice_number', 'Creation-test')->sole();
            $this->assertSame(Purchase::ERROR, $purchase->status);
            $this->assertFileExists(public_path('uploads/purchases/'.$purchase->invoice_file));
            $this->assertSame(2, json_decode($purchase->consumables_json, true)[0]['quantity']);
        } finally {
            $this->app->usePublicPath($publicPath);
        }
    }

    public function test_page_renders(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('purchases.create'))
            ->assertOk()
            ->assertViewIs('purchases.edit')
            ->assertSee('action="'.route('purchases.store').'"', false)
            ->assertSee('name="invoice_file"', false)
            ->assertSee('name="consumables"', false);
    }

    public function test_page_requires_permission(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('purchases.create'))->assertForbidden();
    }

    public function test_store_requires_permission(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('purchases.store'), [
                'invoice_file' => UploadedFile::fake()->create('invoice.txt', 1, 'text/plain'),
            ])->assertForbidden();

        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_invoice_file_is_required_before_creating_purchase(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('purchases.store'), ['invoice_number' => 'INV-TEST'])
            ->assertSessionHasErrors('invoice_file');

        $this->assertDatabaseCount('purchases', 0);
    }
}
