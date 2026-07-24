<?php

namespace Tests\Feature\Purchases;

use App\Models\AssetModel;
use App\Models\Consumable;
use App\Models\LegalPerson;
use App\Models\Supplier;
use App\Models\User;
use App\Services\TimewebInvoiceRecognizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Mockery;
use RuntimeException;
use Tests\TestCase;

class RecognizePurchaseInvoiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
    }

    public function test_supported_invoice_file_is_required(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->postJson(route('purchases.recognize-invoice'), [
                'invoice_file' => UploadedFile::fake()->create('invoice.txt', 10, 'text/plain'),
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('invoice_file');
    }

    public function test_invoice_file_can_use_the_configured_megabyte_limit(): void
    {
        $recognizer = Mockery::mock(TimewebInvoiceRecognizer::class);
        $recognizer->shouldReceive('recognize')->once()->andReturn(['items' => []]);
        $this->app->instance(TimewebInvoiceRecognizer::class, $recognizer);

        $this->actingAs(User::factory()->superuser()->create())
            ->postJson(route('purchases.recognize-invoice'), [
                'invoice_file' => UploadedFile::fake()->create('invoice.pdf', 2600, 'application/pdf'),
            ])
            ->assertOk();
    }

    public function test_recognized_invoice_is_mapped_to_existing_catalog_items(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'ООО Тестовый поставщик']);
        $legalPerson = LegalPerson::forceCreate([
            'name' => 'ООО "Легис-Тех"',
            'bitrix_id' => 60302,
        ]);
        $assetModel = AssetModel::factory()->create([
            'name' => 'Тестовый ноутбук',
            'model_number' => 'NB-100',
        ]);
        $consumable = Consumable::factory()->create([
            'name' => 'Тестовый картридж',
            'model_number' => 'TN-100',
        ]);

        $recognizer = Mockery::mock(TimewebInvoiceRecognizer::class);
        $recognizer->shouldReceive('recognize')->once()->andReturn([
            'purchase_name' => 'Закупка ноутбука и картриджей',
            'invoice_number' => 'INV-42',
            'final_price' => 1260,
            'delivery_cost' => 60,
            'supplier' => 'ООО Тестовый поставщик',
            'supplier_inn' => '7700000000',
            'buyer' => 'Общество с ограниченной ответственностью Легис-Тех',
            'buyer_inn' => '7700000001',
            'comment' => 'Тестовый счёт',
            'items' => [
                [
                    'type' => 'asset',
                    'name' => 'Ноутбук из счёта',
                    'model_number' => 'NB-100',
                    'quantity' => 1,
                    'unit_price' => 1000,
                    'vat_percent' => 20,
                    'warranty_months' => 24,
                ],
                [
                    'type' => 'consumable',
                    'name' => 'Картридж из счёта',
                    'model_number' => 'TN-100',
                    'quantity' => 2,
                    'unit_price' => 100,
                    'vat_percent' => 20,
                    'warranty_months' => 0,
                ],
                [
                    'type' => 'asset',
                    'name' => 'Неизвестное устройство',
                    'model_number' => 'UNKNOWN-1',
                    'quantity' => 1,
                    'unit_price' => 50,
                    'vat_percent' => 20,
                    'warranty_months' => 0,
                ],
            ],
        ]);
        $this->app->instance(TimewebInvoiceRecognizer::class, $recognizer);

        $this->actingAs(User::factory()->superuser()->create())
            ->postJson(route('purchases.recognize-invoice'), [
                'invoice_file' => UploadedFile::fake()->image('invoice.jpg'),
            ])
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.invoice_number', 'Закупка ноутбука и картриджей')
            ->assertJsonPath('data.source_invoice_number', 'INV-42')
            ->assertJsonPath('data.supplier.id', (string) $supplier->id)
            ->assertJsonPath('data.legal_person.id', (string) $legalPerson->id)
            ->assertJsonPath('data.assets.0.model_id', (string) $assetModel->id)
            ->assertJsonPath('data.assets.0.model', 'Тестовый ноутбук (NB-100)')
            ->assertJsonPath('data.assets.0.warranty', 24)
            ->assertJsonPath('data.consumables.0.consumable_id', (string) $consumable->id)
            ->assertJsonPath('data.consumables.0.quantity', 2)
            ->assertJsonPath('data.unmatched.0.model_number', 'UNKNOWN-1');
    }

    public function test_ambiguous_partial_name_match_is_only_returned_as_candidate(): void
    {
        $assetModel = AssetModel::factory()->create([
            'name' => 'Ноутбук Lenovo ThinkPad E14',
            'model_number' => '21E3007XRT',
        ]);
        AssetModel::factory()->create([
            'name' => 'Ноутбук Lenovo ThinkPad E15',
            'model_number' => '21E6007XRT',
        ]);

        $recognizer = Mockery::mock(TimewebInvoiceRecognizer::class);
        $recognizer->shouldReceive('recognize')->once()->andReturn([
            'items' => [[
                'type' => 'asset',
                'name' => 'Ноутбук Lenovo ThinkPad',
                'model_number' => '',
                'quantity' => 1,
                'unit_price' => 1000,
            ]],
        ]);
        $this->app->instance(TimewebInvoiceRecognizer::class, $recognizer);

        $this->actingAs(User::factory()->superuser()->create())
            ->postJson(route('purchases.recognize-invoice'), [
                'invoice_file' => UploadedFile::fake()->image('invoice.jpg'),
            ])
            ->assertOk()
            ->assertJsonCount(0, 'data.assets')
            ->assertJsonPath('data.unmatched.0.candidates.0.id', (string) $assetModel->id)
            ->assertJsonPath('data.unmatched.0.candidates.0.text', 'Ноутбук Lenovo ThinkPad E14 (21E3007XRT)');
    }

    public function test_unique_high_confidence_catalog_matches_are_filled_automatically(): void
    {
        $assetModel = AssetModel::factory()->create([
            'name' => 'SSD накопитель Netac SATA-III 128GB NT01SA500-128-S3X SA500',
            'model_number' => 'Netac SATA-III 128GB NT01SA500-128-S3X SA500',
        ]);
        $consumable = Consumable::factory()->create([
            'name' => 'Лента для полноцветной печати Evolis YMCKO (R5F002EAA)',
            'model_number' => 'б/н',
        ]);

        $recognizer = Mockery::mock(TimewebInvoiceRecognizer::class);
        $recognizer->shouldReceive('recognize')->once()->andReturn([
            'items' => [
                [
                    'type' => 'asset',
                    'name' => 'SSD накопитель Netac SATA-III 128GB SA500 2.5',
                    'model_number' => '',
                    'quantity' => 1,
                    'unit_price' => 1500,
                ],
                [
                    'type' => 'consumable',
                    'name' => 'Лента для полноцветной печати Evolis YMCKO, 200 отпечатков',
                    'model_number' => '',
                    'quantity' => 2,
                    'unit_price' => 5000,
                ],
            ],
        ]);
        $this->app->instance(TimewebInvoiceRecognizer::class, $recognizer);

        $this->actingAs(User::factory()->superuser()->create())
            ->postJson(route('purchases.recognize-invoice'), [
                'invoice_file' => UploadedFile::fake()->image('invoice.jpg'),
            ])
            ->assertOk()
            ->assertJsonPath('data.assets.0.model_id', (string) $assetModel->id)
            ->assertJsonPath('data.consumables.0.consumable_id', (string) $consumable->id)
            ->assertJsonCount(0, 'data.unmatched');
    }

    public function test_recognition_errors_are_returned_without_saving_purchase(): void
    {
        $recognizer = Mockery::mock(TimewebInvoiceRecognizer::class);
        $recognizer->shouldReceive('recognize')
            ->once()
            ->andThrow(new RuntimeException('Timeweb AI отклонил OPEN_AI_TOKEN.'));
        $this->app->instance(TimewebInvoiceRecognizer::class, $recognizer);

        $this->actingAs(User::factory()->superuser()->create())
            ->postJson(route('purchases.recognize-invoice'), [
                'invoice_file' => UploadedFile::fake()->image('invoice.png'),
            ])
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Timeweb AI отклонил OPEN_AI_TOKEN.');

        $this->assertDatabaseCount('purchases', 0);
    }

    public function test_empty_ai_content_is_retried_once(): void
    {
        config()->set('services.timeweb_ai.url', 'https://agent.example.test/v1');
        config()->set('services.timeweb_ai.token', 'test-token');

        Http::fakeSequence()
            ->push([
                'choices' => [[
                    'finish_reason' => 'length',
                    'message' => ['content' => null],
                ]],
                'usage' => ['completion_tokens' => 4000],
            ])
            ->push([
                'choices' => [[
                    'finish_reason' => 'stop',
                    'message' => ['content' => '{"items":[]}'],
                ]],
            ]);

        $result = app(TimewebInvoiceRecognizer::class)->recognize(
            UploadedFile::fake()->image('invoice.png')
        );

        $this->assertSame([], $result['items']);
        Http::assertSentCount(2);
    }
}
