<?php

namespace Tests\Feature\Purchases\Api;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\InvoiceType;
use App\Models\LegalPerson;
use App\Models\OrderItem;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class LegacyConsumableReceiptTest extends TestCase
{
    private function purchase(): Purchase
    {
        $purchase = new Purchase([
            'invoice_number' => 'Legacy receipt', 'invoice_file' => 'invoice.pdf',
            'bitrix_id' => 123, 'final_price' => 20, 'comment' => 'Legacy receipt',
            'status' => Purchase::REVIEW,
            'supplier_id' => Supplier::factory()->create()->id,
            'legal_person_id' => LegalPerson::create(['name' => 'Receipt company'])->id,
            'invoice_type_id' => InvoiceType::create(['name' => 'Receipt type'])->id,
            'consumables_json' => json_encode([
                ['name' => 'First', 'category_id' => Category::factory()->forConsumables()->create()->id, 'quantity' => 2, 'purchase_cost' => '12.50'],
                ['name' => 'Second', 'category_id' => Category::factory()->forConsumables()->create()->id, 'quantity' => 3, 'purchase_cost' => '15.25'],
            ]),
        ]);
        $this->assertTrue($purchase->save());

        return $purchase;
    }

    public function test_receipt_preserves_order_details_and_does_not_repeat_after_deletion(): void
    {
        $purchase = $this->purchase();
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.purchases.consumables_check', $purchase))
            ->assertOk()->assertStatusMessageIs('success');
        $items = Consumable::where('purchase_id', $purchase->id)->orderBy('id')->get();
        $this->assertCount(2, $items);
        $this->assertEquals([2, 3], $items->pluck('qty')->all());
        $this->assertSame(Purchase::FINISHED, $purchase->fresh()->status);
        foreach ($items as $index => $item) {
            $line = $item->orderItems()->firstOrFail();
            $this->assertEquals([12.50, 15.25][$index], $line->price);
            $this->assertEquals($item->qty, $line->qty);
            $this->assertEquals($purchase->supplier_id, $line->order->supplier_id);
            $this->assertEquals($purchase->id, $line->order->order_number);
            $this->assertSame($purchase->created_at->toDateString(), $line->order->purchase_date->toDateString());
            $this->assertTrue($item->delete());
        }
        $this->postJson(route('api.purchases.consumables_check', $purchase))
            ->assertOk()->assertStatusMessageIs('success');
        $this->assertEquals(2, Consumable::withTrashed()->where('purchase_id', $purchase->id)->count());
        $this->assertDatabaseCount('order_items', 2);
    }

    public function test_late_line_save_failure_rolls_back_stock_orders_history_and_status(): void
    {
        $purchase = $this->purchase();
        Event::listen('eloquent.saving: '.OrderItem::class, fn (OrderItem $line) => (float) $line->price === 15.25 ? false : null);
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.purchases.consumables_check', $purchase))
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertEquals(0, Consumable::withTrashed()->where('purchase_id', $purchase->id)->count());
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseMissing('action_logs', ['item_type' => Consumable::class]);
        $this->assertSame(Purchase::REVIEW, $purchase->fresh()->status);
    }

    public function test_creation_permission_is_required(): void
    {
        $purchase = $this->purchase();
        $this->actingAsForApi(User::factory()->create(['permissions' => json_encode(['purchases.edit' => 1])]))
            ->postJson(route('api.purchases.consumables_check', $purchase))->assertForbidden();
        $this->assertDatabaseCount('consumables', 0);
        $this->assertSame(Purchase::REVIEW, $purchase->fresh()->status);
    }

    public function test_catalog_lines_are_not_received_by_the_legacy_endpoint(): void
    {
        $purchase = $this->purchase();
        $lines = json_decode($purchase->consumables_json, true);
        $lines[0]['consumable_id'] = 123;
        $purchase->consumables_json = json_encode($lines);
        $this->assertTrue($purchase->save());
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.purchases.consumables_check', $purchase))
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertDatabaseCount('consumables', 0);
        $this->assertSame(Purchase::REVIEW, $purchase->fresh()->status);
    }

    public function test_hidden_company_assets_prevent_receipt(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $purchase = $this->purchase();
        Asset::factory()->for(Company::factory()->create())->create(['purchase_id' => $purchase->id]);
        $actor = User::factory()->for(Company::factory()->create())->create([
            'permissions' => json_encode(['purchases.edit' => 1, 'consumables.create' => 1]),
        ]);
        $this->actingAsForApi($actor)->postJson(route('api.purchases.consumables_check', $purchase))->assertForbidden();
        $this->assertDatabaseCount('consumables', 0);
        $this->assertSame(Purchase::REVIEW, $purchase->fresh()->status);
    }

    public function test_hidden_company_consumables_cannot_be_duplicated(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $purchase = $this->purchase();
        Consumable::factory()->for(Company::factory()->create())->create(['purchase_id' => $purchase->id]);
        $actor = User::factory()->for(Company::factory()->create())->create([
            'permissions' => json_encode(['purchases.edit' => 1, 'consumables.create' => 1]),
        ]);
        $this->actingAsForApi($actor)->postJson(route('api.purchases.consumables_check', $purchase))->assertForbidden();
        $this->assertDatabaseCount('consumables', 1);
        $this->assertSame(Purchase::REVIEW, $purchase->fresh()->status);
    }

    public function test_failed_purchase_save_rolls_back_received_items(): void
    {
        $purchase = $this->purchase();
        Event::listen('eloquent.saving: '.Purchase::class, fn (Purchase $item) => $item->id === $purchase->id ? false : null);
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.purchases.consumables_check', $purchase))
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertDatabaseCount('consumables', 0);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertSame(Purchase::REVIEW, $purchase->fresh()->status);
    }
}
