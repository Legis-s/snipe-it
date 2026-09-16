<?php

namespace Tests\Feature\Purchases\Api;

use App\Models\Consumable;
use App\Models\InvoiceType;
use App\Models\LegalPerson;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PurchaseConsumableLineTest extends TestCase
{
    private function purchase(): Purchase
    {
        $item = Consumable::factory()->create(['qty' => 0]);
        $purchase = new Purchase([
            'invoice_number' => 'Line edit', 'invoice_file' => 'invoice.pdf',
            'bitrix_id' => 123, 'final_price' => 20, 'comment' => 'Line edit',
            'supplier_id' => Supplier::factory()->create()->id,
            'legal_person_id' => LegalPerson::create(['name' => 'Line company'])->id,
            'invoice_type_id' => InvoiceType::create(['name' => 'Line type'])->id,
            'consumables_json' => json_encode([
                ['id' => 1, 'consumable_id' => $item->id, 'quantity' => 2, 'reviewed' => 0, 'purchase_cost' => 10],
                ['id' => 2, 'consumable_id' => $item->id, 'quantity' => 3, 'reviewed' => 0, 'purchase_cost' => 15],
            ]),
        ]);
        $this->assertTrue($purchase->save());

        return $purchase;
    }

    public function test_deleted_line_ids_are_not_reassigned_and_stale_requests_fail(): void
    {
        $purchase = $this->purchase();
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.purchases.consumables_line', $purchase), ['action' => 'delete', 'row_id' => 1])
            ->assertOk()->assertStatusMessageIs('success')->assertJsonPath('payload.consumables.0.id', 2);
        $before = $purchase->fresh()->consumables_json;
        $this->postJson(route('api.purchases.consumables_line', $purchase), ['row_id' => 1, 'quantity' => 99, 'purchase_cost' => 1])
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertSame($before, $purchase->fresh()->consumables_json);
        $this->postJson(route('api.purchases.consumables_line', $purchase), ['row_id' => 2, 'quantity' => 4, 'purchase_cost' => '12.50', 'nds' => 20])
            ->assertOk()->assertStatusMessageIs('success')->assertJsonPath('payload.consumables.0.id', 2);
        $line = json_decode($purchase->fresh()->consumables_json, true)[0];
        $this->assertSame(4, $line['quantity']);
        $this->assertSame('12.50', $line['purchase_cost']);
    }

    public function test_edit_reads_current_receipt_progress_and_cannot_delete_received_lines(): void
    {
        $purchase = $this->purchase();
        $itemId = json_decode($purchase->consumables_json, true)[0]['consumable_id'];
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.consumables.review', $itemId), ['purchase_id' => $purchase->id, 'row_id' => 2, 'quantity' => 2, 'purchase_cost' => 15])
            ->assertOk()->assertStatusMessageIs('success');
        $before = $purchase->fresh()->consumables_json;
        foreach ([['action' => 'delete'], ['quantity' => 1, 'purchase_cost' => 10]] as $payload) {
            $this->postJson(route('api.purchases.consumables_line', $purchase), ['row_id' => 2] + $payload)
                ->assertOk()->assertStatusMessageIs('error');
            $this->assertSame($before, $purchase->fresh()->consumables_json);
        }
        $this->postJson(route('api.purchases.consumables_line', $purchase), ['row_id' => 2, 'quantity' => 4, 'purchase_cost' => 20])
            ->assertOk()->assertStatusMessageIs('success')->assertJsonPath('payload.consumables.1.reviewed', 2);
    }

    public static function invalid_payloads(): array
    {
        return [
            'missing row' => [[]],
            'unknown action' => [['row_id' => 1, 'action' => 'other', 'quantity' => 2, 'purchase_cost' => 10]],
            'fractional quantity' => [['row_id' => 1, 'quantity' => 1.5, 'purchase_cost' => 10]],
            'invalid cost' => [['row_id' => 1, 'quantity' => 2, 'purchase_cost' => 'abc']],
            'negative tax' => [['row_id' => 1, 'quantity' => 2, 'purchase_cost' => 10, 'nds' => -1]],
        ];
    }

    #[DataProvider('invalid_payloads')]
    public function test_invalid_input_does_not_modify_purchase(array $payload): void
    {
        $purchase = $this->purchase();
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.purchases.consumables_line', $purchase), $payload)
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertSame($purchase->consumables_json, $purchase->fresh()->consumables_json);
    }

    public function test_save_failure_rolls_back_and_permission_is_required(): void
    {
        $purchase = $this->purchase();
        $payload = ['row_id' => 1, 'action' => 'delete'];
        $this->actingAsForApi(User::factory()->create())
            ->postJson(route('api.purchases.consumables_line', $purchase), $payload)->assertForbidden();
        Event::listen('eloquent.saving: '.Purchase::class, fn (Purchase $item) => $item->id === $purchase->id ? false : null);
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.purchases.consumables_line', $purchase), $payload)
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertSame($purchase->consumables_json, $purchase->fresh()->consumables_json);
        $this->assertEquals($purchase->status, $purchase->fresh()->status);
    }

    public function test_edit_after_merge_retains_current_consumable_reference(): void
    {
        $purchase = $this->purchase();
        $source = Consumable::findOrFail(json_decode($purchase->consumables_json, true)[0]['consumable_id']);
        $target = Consumable::factory()->create(['qty' => 0, 'company_id' => $source->company_id]);
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
            ->assertOk()->assertStatusMessageIs('success');
        $this->postJson(route('api.purchases.consumables_line', $purchase), [
            'row_id' => 2, 'quantity' => 4, 'purchase_cost' => 20, 'consumable_id' => $source->id, 'reviewed' => 99,
        ])->assertOk()->assertStatusMessageIs('success')
            ->assertJsonPath('payload.consumables.1.consumable_id', $target->id)
            ->assertJsonPath('payload.consumables.1.reviewed', 0);
    }

    public function test_duplicate_row_ids_are_rejected_without_changes(): void
    {
        $purchase = $this->purchase();
        $lines = json_decode($purchase->consumables_json, true);
        $lines[1]['id'] = 1;
        $purchase->consumables_json = json_encode($lines);
        $this->assertTrue($purchase->save());
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.purchases.consumables_line', $purchase), ['row_id' => 1, 'action' => 'delete'])
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertSame($purchase->consumables_json, $purchase->fresh()->consumables_json);
    }

    public function test_purchase_page_renders_compact_accessible_action_group(): void
    {
        $purchase = $this->purchase();
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('purchases.show', $purchase))->assertOk()
            ->assertSee("class: 'btn-group btn-group-sm'", false)
            ->assertSee("'aria-label': label", false)
            ->assertSee("style: 'display: inline-flex; white-space: nowrap;'", false)
            ->assertSee("labels.accept, 'check'", false)
            ->assertSee("labels.edit, 'pencil'", false)
            ->assertSee("labels.delete, 'trash'", false);
    }

    public function test_unaccepted_block_is_hidden_only_when_every_line_is_received(): void
    {
        $purchase = $this->purchase();
        $lines = json_decode($purchase->consumables_json, true);
        $lines[0]['reviewed'] = $lines[0]['quantity'];
        $lines[1]['reviewed'] = $lines[1]['quantity'] - 1;
        $purchase->consumables_json = json_encode($lines);
        $this->assertTrue($purchase->save());
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('purchases.show', $purchase))->assertOk()
            ->assertSee('id="unaccepted-consumables"', false)
            ->assertSee('id="consumableAssignmentTable"', false);

        $lines[1]['reviewed'] = $lines[1]['quantity'];
        $purchase->consumables_json = json_encode($lines);
        $this->assertTrue($purchase->save());
        $this->get(route('purchases.show', $purchase))->assertOk()
            ->assertDontSee('id="unaccepted-consumables"', false)
            ->assertDontSee('id="table_consumables"', false)
            ->assertSee('id="consumableAssignmentTable"', false);
    }

    public function test_received_legacy_purchase_hides_unaccepted_block(): void
    {
        $purchase = $this->purchase();
        $item = Consumable::firstOrFail();
        $purchase->consumables_json = json_encode([
            ['id' => 1, 'name' => $item->name, 'category_id' => $item->category_id, 'quantity' => 2],
        ]);
        $this->assertTrue($purchase->save());
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('purchases.show', $purchase))->assertOk()
            ->assertSee('id="unaccepted-consumables"', false);
        $item->purchase_id = $purchase->id;
        $this->assertTrue($item->save());
        $this->get(route('purchases.show', $purchase))->assertOk()
            ->assertDontSee('id="unaccepted-consumables"', false)
            ->assertSee('id="consumableAssignmentTable"', false);
    }
}
