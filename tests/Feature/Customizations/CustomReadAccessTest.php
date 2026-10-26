<?php

namespace Tests\Feature\Customizations;

use App\Models\Contract;
use App\Models\Deal;
use App\Models\InvoiceType;
use App\Models\LegalPerson;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class CustomReadAccessTest extends TestCase
{
    public static function reference_resources(): array
    {
        return [
            'contracts' => ['contracts', Contract::class],
            'deals' => ['deals', Deal::class],
        ];
    }

    #[DataProvider('reference_resources')]
    public function test_reference_pages_and_api_require_location_view_permission(string $resource, string $model): void
    {
        $item = $model::create(['name' => 'Protected reference', 'type' => 2, 'summ' => 125]);
        $viewer = User::factory()->viewAssets()->create();

        $this->actingAs($viewer)->get('/'.$resource)->assertForbidden();
        $this->get('/'.$resource.'/'.$item->id)->assertForbidden();
        $this->actingAsForApi($viewer)->getJson('/api/v1/'.$resource)->assertForbidden();
        $this->getJson('/api/v1/'.$resource.'/'.$item->id)->assertForbidden();
    }

    #[DataProvider('reference_resources')]
    public function test_location_viewers_can_open_reference_pages_and_api_details(string $resource, string $model): void
    {
        $item = $model::create(['name' => 'Visible reference', 'type' => 2, 'summ' => 125]);
        $viewer = User::factory()->viewLocations()->create();

        $this->actingAs($viewer)->get('/'.$resource)->assertOk();
        $this->get('/'.$resource.'/'.$item->id)->assertOk()->assertSee('Visible reference');
        $this->actingAsForApi($viewer)->getJson('/api/v1/'.$resource.'/'.$item->id)
            ->assertOk()->assertJsonPath('id', $item->id)
            ->assertJsonPath('name', 'Visible reference')
            ->assertJsonPath('type', $item->getTypeText())->assertJsonPath('summ', 125);
    }

    public function test_purchase_pages_and_invoice_require_purchase_view_permission(): void
    {
        $purchase = $this->purchase();
        $viewer = User::factory()->viewAssets()->viewLocations()->create();

        $this->actingAs($viewer)->get(route('purchases.index'))->assertForbidden();
        $this->get(route('purchases.show', $purchase))->assertForbidden();
        $this->get(route('purchases.invoice.download', $purchase))->assertForbidden();
    }

    public function test_purchase_viewers_can_open_purchase_pages_and_download_invoice(): void
    {
        $purchase = $this->purchase();
        $viewer = User::factory()->create(['permissions' => json_encode(['purchases.view' => 1])]);
        $disk = Storage::fake('purchase-access-public');
        $disk->put('uploads/purchases/access-invoice.pdf', 'Invoice test contents');
        $originalPublicPath = public_path();

        try {
            $this->actingAs($viewer)->get(route('purchases.index'))->assertOk();
            $this->get(route('purchases.show', $purchase))->assertOk()->assertSee('ACCESS-INVOICE');
            $this->app->usePublicPath($disk->path(''));
            $this->get(route('purchases.invoice.download', $purchase))->assertDownload('access-invoice.pdf');
        } finally {
            $this->app->usePublicPath($originalPublicPath);
        }
    }

    private function purchase(): Purchase
    {
        $purchase = new Purchase([
            'invoice_number' => 'ACCESS-INVOICE', 'invoice_file' => 'access-invoice.pdf',
            'bitrix_id' => 42, 'final_price' => 125, 'comment' => 'Access regression',
            'supplier_id' => Supplier::factory()->create()->id,
            'legal_person_id' => LegalPerson::create(['name' => 'Access legal person'])->id,
            'invoice_type_id' => InvoiceType::create(['name' => 'Access invoice type'])->id,
            'assets_json' => '[]', 'consumables_json' => '[]',
        ]);
        $this->assertTrue($purchase->save());

        return $purchase;
    }
}
