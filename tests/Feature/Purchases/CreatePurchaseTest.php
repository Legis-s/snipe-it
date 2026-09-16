<?php

namespace Tests\Feature\Purchases;

use App\Models\Consumable;
use App\Models\InvoiceType;
use App\Models\LegalPerson;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CreatePurchaseTest extends TestCase
{
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
