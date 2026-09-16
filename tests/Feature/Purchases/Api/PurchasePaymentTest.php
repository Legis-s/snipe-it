<?php

namespace Tests\Feature\Purchases\Api;

use App\Models\Asset;
use App\Models\Company;
use App\Models\InvoiceType;
use App\Models\LegalPerson;
use App\Models\Purchase;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class PurchasePaymentTest extends TestCase
{
    private function purchase(): Purchase
    {
        $purchase = new Purchase([
            'invoice_number' => 'Payment-test', 'invoice_file' => 'invoice.pdf',
            'bitrix_id' => 123, 'final_price' => 20, 'comment' => 'Payment test',
            'supplier_id' => Supplier::factory()->create()->id,
            'legal_person_id' => LegalPerson::create(['name' => 'Payment company'])->id,
            'invoice_type_id' => InvoiceType::create(['name' => 'Payment type'])->id,
            'status' => Purchase::INPROGRESS,
        ]);
        $this->assertTrue($purchase->save());

        return $purchase;
    }

    public function test_payment_transitions_assets_once_and_restores_events(): void
    {
        $purchase = $this->purchase();
        $from = Statuslabel::factory()->create(['name' => 'В закупке']);
        $to = Statuslabel::factory()->create(['name' => 'Ожидает инвентаризации']);
        $asset = Asset::factory()->create(['purchase_id' => $purchase->id, 'status_id' => $from->id]);
        $unrelated = Asset::factory()->create(['purchase_id' => $purchase->id]);
        $dispatcher = Asset::getEventDispatcher();
        $assetEvents = 0;
        Asset::saving(function () use (&$assetEvents): void {
            $assetEvents++;
        });

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.purchases.paid', $purchase))->assertOk()->assertStatusMessageIs('success');
        $this->assertSame($dispatcher, Asset::getEventDispatcher());
        $this->assertSame(0, $assetEvents);
        $this->assertEquals($to->id, $asset->fresh()->status_id);
        $this->assertEquals($unrelated->status_id, $unrelated->fresh()->status_id);
        $this->assertSame(Purchase::INVENTORY, $purchase->fresh()->status);
        $timestamp = $purchase->fresh()->bitrix_result_at;
        $this->assertNotNull($timestamp);
        $updatedAt = $asset->fresh()->updated_at;

        $this->travel(1)->hours();
        $this->postJson(route('api.purchases.paid', $purchase))->assertOk()->assertStatusMessageIs('success');
        $this->assertEquals($timestamp, $purchase->fresh()->bitrix_result_at);
        $this->assertEquals($updatedAt, $asset->fresh()->updated_at);
        Asset::factory()->create();
        $this->assertSame(1, $assetEvents);
    }

    public function test_payment_without_assets_moves_to_review(): void
    {
        $purchase = $this->purchase();
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.purchases.paid', $purchase))->assertOk()->assertStatusMessageIs('success');
        $this->assertSame(Purchase::REVIEW, $purchase->fresh()->status);
    }

    public function test_payment_does_not_regress_advanced_purchase_statuses(): void
    {
        $purchase = $this->purchase();
        $this->actingAsForApi(User::factory()->superuser()->create());
        foreach ([Purchase::REVIEW, Purchase::FINISHED, Purchase::INVENTORY] as $status) {
            $purchase->status = $status;
            $this->assertTrue($purchase->save());
            $this->postJson(route('api.purchases.paid', $purchase))->assertOk()->assertStatusMessageIs('success');
            $this->assertSame($status, $purchase->fresh()->status);
        }
    }

    public function test_invalid_asset_rolls_back_preceding_asset_updates(): void
    {
        $purchase = $this->purchase();
        $from = Statuslabel::factory()->create(['name' => 'В закупке']);
        Statuslabel::factory()->create(['name' => 'Ожидает инвентаризации']);
        $first = Asset::factory()->create(['purchase_id' => $purchase->id, 'status_id' => $from->id]);
        $invalid = Asset::factory()->create(['purchase_id' => $purchase->id, 'status_id' => $from->id]);
        DB::table('assets')->where('id', $invalid->id)->update(['asset_tag' => '']);
        $dispatcher = Asset::getEventDispatcher();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.purchases.paid', $purchase))->assertOk()->assertStatusMessageIs('error');
        $this->assertEquals($from->id, $first->fresh()->status_id);
        $this->assertEquals($from->id, $invalid->fresh()->status_id);
        $this->assertNull($purchase->fresh()->bitrix_result_at);
        $this->assertSame(Purchase::INPROGRESS, $purchase->fresh()->status);
        $this->assertSame($dispatcher, Asset::getEventDispatcher());
    }

    public function test_invalid_purchase_rolls_back_asset_updates(): void
    {
        $purchase = $this->purchase();
        $from = Statuslabel::factory()->create(['name' => 'В закупке']);
        Statuslabel::factory()->create(['name' => 'Ожидает инвентаризации']);
        $asset = Asset::factory()->create(['purchase_id' => $purchase->id, 'status_id' => $from->id]);
        DB::table('purchases')->where('id', $purchase->id)->update(['comment' => '']);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.purchases.paid', $purchase))->assertOk()->assertStatusMessageIs('error');
        $this->assertEquals($from->id, $asset->fresh()->status_id);
        $this->assertNull($purchase->fresh()->bitrix_result_at);
    }

    public function test_missing_status_labels_reject_payment_without_changes(): void
    {
        $purchase = $this->purchase();
        $asset = Asset::factory()->create(['purchase_id' => $purchase->id]);
        $this->actingAsForApi(User::factory()->superuser()->create());
        $this->postJson(route('api.purchases.paid', $purchase))->assertOk()->assertStatusMessageIs('error');
        $this->assertNull($purchase->fresh()->bitrix_result_at);

        $from = Statuslabel::factory()->create(['name' => 'В закупке']);
        DB::table('assets')->where('id', $asset->id)->update(['status_id' => $from->id]);
        $this->postJson(route('api.purchases.paid', $purchase))->assertOk()->assertStatusMessageIs('error');
        $this->assertEquals($from->id, $asset->fresh()->status_id);
        $this->assertNull($purchase->fresh()->bitrix_result_at);
    }

    public function test_late_exception_rolls_back_payment_and_restores_events(): void
    {
        $purchase = $this->purchase();
        $from = Statuslabel::factory()->create(['name' => 'В закупке']);
        Statuslabel::factory()->create(['name' => 'Ожидает инвентаризации']);
        $asset = Asset::factory()->create(['purchase_id' => $purchase->id, 'status_id' => $from->id]);
        $dispatcher = Asset::getEventDispatcher();
        Purchase::saving(function (): void {
            throw new RuntimeException('Payment persistence failure');
        });
        $this->withoutExceptionHandling();

        try {
            $this->actingAsForApi(User::factory()->superuser()->create())
                ->postJson(route('api.purchases.paid', $purchase));
            $this->fail('Expected persistence failure');
        } catch (RuntimeException $exception) {
            $this->assertSame('Payment persistence failure', $exception->getMessage());
        }
        $this->assertEquals($from->id, $asset->fresh()->status_id);
        $this->assertNull($purchase->fresh()->bitrix_result_at);
        $this->assertSame($dispatcher, Asset::getEventDispatcher());
    }

    public function test_payment_requires_purchase_update_permission(): void
    {
        $purchase = $this->purchase();
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.purchases.paid', $purchase))->assertForbidden();
        $this->assertNull($purchase->fresh()->bitrix_result_at);
    }

    public function test_payment_rejects_partially_visible_purchase_assets(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $purchase = $this->purchase();
        $company = Company::factory()->create();
        $from = Statuslabel::factory()->create(['name' => 'В закупке']);
        Statuslabel::factory()->create(['name' => 'Ожидает инвентаризации']);
        $visible = Asset::factory()->for($company)->create(['purchase_id' => $purchase->id, 'status_id' => $from->id]);
        $hidden = Asset::factory()->for(Company::factory()->create())->create(['purchase_id' => $purchase->id, 'status_id' => $from->id]);
        $actor = User::factory()->create(['permissions' => json_encode(['purchases.edit' => '1'])]);
        $actor->companies()->attach($company);
        $this->assertTrue($actor->can('update', Purchase::class));
        $this->actingAsForApi($actor)
            ->postJson(route('api.purchases.paid', $purchase))->assertForbidden();
        $this->assertDatabaseHas('assets', ['id' => $visible->id, 'status_id' => $from->id]);
        $this->assertDatabaseHas('assets', ['id' => $hidden->id, 'status_id' => $from->id]);
        $this->assertNull($purchase->fresh()->bitrix_result_at);
    }
}
