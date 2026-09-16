<?php

namespace Tests\Feature\Consumables\Api;

use App\Enums\CheckoutRequestState;
use App\Models\Actionlog;
use App\Models\CheckoutRequest;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\InvoiceType;
use App\Models\LegalPerson;
use App\Models\OrderItem;
use App\Models\PredefinedKit;
use App\Models\Purchase;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ConsumableCompactTest extends TestCase
{
    public function test_merge_preserves_stock_both_checkout_ledgers_orders_and_files(): void
    {
        Storage::fake('local');
        Storage::fake('public');
        $actor = User::factory()->editConsumables()->deleteConsumables()->create();
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 4]);
        $source = Consumable::factory()->create(['company_id' => null, 'qty' => 6, 'image' => 'source.jpg']);
        $user = User::factory()->create();
        $source->users()->attach($user->id, ['created_by' => $actor->id, 'note' => 'Legacy checkout']);
        $assignment = new ConsumableAssignment([
            'consumable_id' => $source->id, 'type' => ConsumableAssignment::ISSUED,
            'quantity' => 2, 'assigned_type' => User::class, 'assigned_to' => $user->id,
        ]);
        $this->assertTrue($assignment->save());
        $file = Actionlog::factory()->create([
            'item_type' => Consumable::class, 'item_id' => $source->id,
            'action_type' => 'uploaded', 'filename' => 'source.pdf',
        ]);
        Storage::disk('local')->put('private_uploads/consumables/source.pdf', 'invoice');
        Storage::disk('public')->put('consumables/source.jpg', 'image');
        $sourceOrderIds = $source->orderItems()->pluck('id')->all();
        $this->assertNotEmpty($sourceOrderIds);

        $this->actingAsForApi($actor)
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
            ->assertOk()->assertStatusMessageIs('success')
            ->assertJsonPath('payload.id', $target->id);

        $target->refresh();
        $this->assertEquals(10, $target->qty);
        $this->assertSame(3, $target->numCheckedOut());
        $this->assertEquals(7, $target->numRemaining());
        $this->assertEquals($target->id, $assignment->fresh()->consumable_id);
        $this->assertEquals($target->id, $file->fresh()->item_id);
        $this->assertEquals(count($sourceOrderIds), OrderItem::whereIn('id', $sourceOrderIds)->where('item_id', $target->id)->count());
        $source->refresh();
        $this->assertTrue($source->trashed());
        $this->assertEquals(0, $source->qty);
        $this->assertSame('source.jpg', $source->image);
        $this->assertDatabaseHas('consumable_merges', [
            'source_id' => $source->id, 'target_id' => $target->id,
            'created_by' => $actor->id, 'source_purchase_id' => null,
        ]);
        Storage::disk('local')->assertExists('private_uploads/consumables/source.pdf');
        Storage::disk('public')->assertExists('consumables/source.jpg');
        $this->assertTrue(Actionlog::where('item_type', Consumable::class)->where('item_id', $source->id)
            ->where('action_type', 'delete')->where('target_id', $target->id)->exists());

        $this->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertEquals(10, $target->fresh()->qty);
    }

    public static function invalid_sources(): array
    {
        return [
            'missing' => [[]],
            'empty' => [['id_array' => []]],
            'scalar' => [['id_array' => '1']],
            'invalid id' => [['id_array' => ['invalid']]],
            'duplicates' => [['id_array' => [123, 123]]],
            'not found' => [['id_array' => [2147483647]]],
        ];
    }

    public function test_merge_history_preserves_each_step_and_rejects_restored_sources(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());
        $source = Consumable::factory()->create(['company_id' => null, 'qty' => 2]);
        $intermediate = Consumable::factory()->create(['company_id' => null, 'qty' => 3]);
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 4]);
        $this->postJson(route('api.consumables.compact', $intermediate), ['id_array' => [$source->id]])
            ->assertOk()->assertStatusMessageIs('success');
        $this->postJson(route('api.consumables.compact', $target), ['id_array' => [$intermediate->id]])
            ->assertOk()->assertStatusMessageIs('success');
        $this->assertDatabaseHas('consumable_merges', ['source_id' => $source->id, 'target_id' => $intermediate->id]);
        $this->assertDatabaseHas('consumable_merges', ['source_id' => $intermediate->id, 'target_id' => $target->id]);
        $this->assertEquals(9, $target->fresh()->qty);

        $this->assertTrue($source->refresh()->restore());
        $this->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
            ->assertOk()->assertStatusMessageIs('error')
            ->assertJsonPath('messages.id_array.0', trans('general.consumable_already_merged'));
        $this->postJson(route('api.consumables.compact', $source), ['id_array' => [$target->id]])
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertDatabaseCount('consumable_merges', 2);
        $this->assertEquals(9, $target->fresh()->qty);
        $this->assertEquals(0, $source->fresh()->qty);
    }

    #[DataProvider('invalid_sources')]
    public function test_invalid_sources_do_not_change_target(array $payload): void
    {
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 4]);
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.consumables.compact', $target), $payload)
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertEquals(4, $target->fresh()->qty);
        $this->assertFalse($target->fresh()->trashed());
    }

    public function test_cannot_merge_target_into_itself(): void
    {
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 4]);
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$target->id]])
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertEquals(4, $target->fresh()->qty);
        $this->assertFalse($target->fresh()->trashed());
    }

    public function test_both_edit_and_delete_permissions_are_required(): void
    {
        $target = Consumable::factory()->create();
        $source = Consumable::factory()->create();
        foreach ([User::factory()->viewConsumables(), User::factory()->editConsumables(), User::factory()->deleteConsumables()] as $factory) {
            $this->actingAsForApi($factory->create())
                ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
                ->assertForbidden();
        }
        $this->assertFalse($source->fresh()->trashed());
    }

    public function test_cross_company_merge_is_rejected_even_for_superuser(): void
    {
        $target = Consumable::factory()->for(Company::factory())->create(['qty' => 4]);
        $source = Consumable::factory()->for(Company::factory())->create(['qty' => 6]);
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertEquals(4, $target->fresh()->qty);
        $this->assertEquals(6, $source->fresh()->qty);
        $this->assertFalse($source->fresh()->trashed());
    }

    public function test_validation_failure_rolls_back_transferred_records(): void
    {
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 99999]);
        $source = Consumable::factory()->create(['company_id' => null, 'qty' => 6]);
        $request = CheckoutRequest::factory()->create(['requestable_type' => Consumable::class, 'requestable_id' => $source->id]);
        $kit = PredefinedKit::factory()->create();
        $kit->consumables()->attach($source->id, ['quantity' => 3]);
        $user = User::factory()->create();
        $source->users()->attach($user->id, ['created_by' => $user->id]);
        $orderIds = $source->orderItems()->pluck('id')->all();
        $assignment = new ConsumableAssignment([
            'consumable_id' => $source->id, 'type' => ConsumableAssignment::ISSUED, 'quantity' => 2,
        ]);
        $this->assertTrue($assignment->save());

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
            ->assertOk()->assertStatusMessageIs('error')
            ->assertJsonStructure(['messages' => ['qty']]);

        $this->assertEquals(99999, $target->fresh()->qty);
        $this->assertEquals($source->id, $request->fresh()->requestable_id);
        $this->assertDatabaseHas('kits_consumables', ['kit_id' => $kit->id, 'consumable_id' => $source->id, 'quantity' => 3]);
        $this->assertFalse($source->fresh()->trashed());
        $this->assertEquals(6, $source->fresh()->qty);
        $this->assertEquals($source->id, $assignment->fresh()->consumable_id);
        $this->assertEquals(1, $source->users()->count());
        $this->assertSame($orderIds, $source->orderItems()->pluck('id')->all());
    }

    public function test_audit_failure_rolls_back_retirement_and_stock_changes(): void
    {
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 4]);
        $source = Consumable::factory()->create(['company_id' => null, 'qty' => 6]);
        $user = User::factory()->create();
        $source->users()->attach($user->id, ['created_by' => $user->id]);
        Event::listen('eloquent.saving: '.Actionlog::class, function (Actionlog $log) use ($source): void {
            if ($log->action_type === 'delete' && $log->item_type === Consumable::class && $log->item_id == $source->id) {
                throw new \RuntimeException('Merge audit failed');
            }
        });
        $this->withoutExceptionHandling();

        try {
            $this->actingAsForApi(User::factory()->superuser()->create())
                ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]]);
            $this->fail('Expected audit failure');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Merge audit failed', $exception->getMessage());
        }

        $this->assertEquals(4, $target->fresh()->qty);
        $this->assertEquals(6, $source->fresh()->qty);
        $this->assertFalse($source->fresh()->trashed());
        $this->assertEquals(1, $source->users()->count());
        $this->assertFalse(Actionlog::where('item_type', Consumable::class)->where('item_id', $source->id)->where('action_type', 'delete')->exists());
        $this->assertDatabaseMissing('consumable_merges', ['source_id' => $source->id]);
    }

    public function test_fmcs_rejects_items_outside_the_actors_companies(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $company = Company::factory()->create();
        $target = Consumable::factory()->for($company)->create(['qty' => 4]);
        $source = Consumable::factory()->for($company)->create(['qty' => 6]);
        $actor = User::factory()->editConsumables()->deleteConsumables()->create();
        $actor->companies()->attach(Company::factory()->create());

        $this->actingAsForApi($actor)
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
            ->assertOk()->assertStatusMessageIs('error');

        $this->assertDatabaseHas('consumables', ['id' => $target->id, 'qty' => 4, 'deleted_at' => null]);
        $this->assertDatabaseHas('consumables', ['id' => $source->id, 'qty' => 6, 'deleted_at' => null]);
    }

    public function test_merge_sums_kit_lines_and_preserves_separate_open_requests(): void
    {
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 4]);
        $requested = Consumable::factory()->create(['company_id' => null, 'qty' => 6]);
        $kitItem = Consumable::factory()->create(['company_id' => null, 'qty' => 8]);
        $user = User::factory()->create();
        $requests = collect([$target, $requested, $kitItem])->map(fn (Consumable $item): CheckoutRequest => CheckoutRequest::factory()->create([
            'requestable_type' => Consumable::class, 'requestable_id' => $item->id,
            'user_id' => $user->id, 'quantity' => 5, 'fulfilled_quantity' => 2,
            'start_date' => '2026-09-20', 'end_date' => '2026-09-22',
        ]));
        $closed = CheckoutRequest::factory()->create([
            'requestable_type' => Consumable::class, 'requestable_id' => $requested->id,
            'state' => CheckoutRequestState::Fulfilled, 'quantity' => 2, 'fulfilled_quantity' => 2,
        ]);
        $kit = PredefinedKit::factory()->create();
        $otherKit = PredefinedKit::factory()->create();
        $kit->consumables()->attach([$target->id => ['quantity' => 2], $requested->id => ['quantity' => 3], $kitItem->id => ['quantity' => 4]]);
        $otherKit->consumables()->attach($kitItem->id, ['quantity' => 7]);
        $targetPivotId = $kit->consumables()->find($target->id)->pivot->id;

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$requested->id, $kitItem->id]])
            ->assertOk()->assertStatusMessageIs('success');
        $this->assertEquals(18, $target->fresh()->qty);
        $this->assertDatabaseHas('kits_consumables', ['id' => $targetPivotId, 'kit_id' => $kit->id, 'consumable_id' => $target->id, 'quantity' => 9]);
        $this->assertDatabaseHas('kits_consumables', ['kit_id' => $otherKit->id, 'consumable_id' => $target->id, 'quantity' => 7]);
        $this->assertEquals(1, $kit->consumables()->count());
        foreach ($requests as $request) {
            $fresh = $request->fresh();
            $this->assertEquals($target->id, $fresh->requestable_id);
            $this->assertEquals($user->id, $fresh->user_id);
            $this->assertSame(CheckoutRequestState::Pending, $fresh->state);
            $this->assertSame(5, $fresh->quantity);
            $this->assertSame(2, $fresh->fulfilled_quantity);
            $this->assertEquals($request->start_date, $fresh->start_date);
            $this->assertEquals($request->end_date, $fresh->end_date);
            $this->assertEquals($request->created_at, $fresh->created_at);
        }
        $this->assertEquals($requested->id, $closed->fresh()->requestable_id);
        $this->assertSame(CheckoutRequestState::Fulfilled, $closed->fresh()->state);
        $this->assertEquals(3, $target->openRequests()->count());
        $context = CheckoutRequest::contextForCheckout($requests[1]->id, Consumable::class, $target->id);
        $this->assertEquals($requests[1]->id, $context['checkoutRequest']->id);
        $this->assertEquals(2, $context['otherPendingRequests']->count());
        $this->assertTrue($requested->fresh()->trashed());
        $this->assertTrue($kitItem->fresh()->trashed());
        $this->postJson(route('api.consumables.compact', $target), ['id_array' => [$requested->id]])->assertStatusMessageIs('error');
        $this->assertEquals(9, $kit->consumables()->find($target->id)->pivot->quantity);
    }

    public function test_merge_requires_permission_to_edit_affected_kits(): void
    {
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 4]);
        $source = Consumable::factory()->create(['company_id' => null, 'qty' => 6]);
        $kit = PredefinedKit::factory()->create();
        $kit->consumables()->attach($source->id, ['quantity' => 3]);
        $this->actingAsForApi(User::factory()->editConsumables()->deleteConsumables()->create())
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])->assertForbidden();
        $this->assertFalse($source->fresh()->trashed());
        $this->assertEquals(4, $target->fresh()->qty);
        $this->assertDatabaseHas('kits_consumables', ['kit_id' => $kit->id, 'consumable_id' => $source->id, 'quantity' => 3]);
    }

    public function test_kit_quantity_overflow_rolls_back_merge(): void
    {
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 4]);
        $source = Consumable::factory()->create(['company_id' => null, 'qty' => 6]);
        $kit = PredefinedKit::factory()->create();
        $kit->consumables()->attach([$target->id => ['quantity' => 2147483647], $source->id => ['quantity' => 1]]);
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])->assertOk()->assertStatusMessageIs('error');
        $this->assertFalse($source->fresh()->trashed());
        $this->assertEquals(4, $target->fresh()->qty);
        $this->assertEquals(2147483647, $kit->consumables()->find($target->id)->pivot->quantity);
        $this->assertEquals(1, $kit->consumables()->find($source->id)->pivot->quantity);
    }

    public function test_failed_request_save_rolls_back_kit_and_request_moves(): void
    {
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 4]);
        $source = Consumable::factory()->create(['company_id' => null, 'qty' => 6]);
        $requests = CheckoutRequest::factory()->count(2)->create(['requestable_type' => Consumable::class, 'requestable_id' => $source->id]);
        $kit = PredefinedKit::factory()->create();
        $kit->consumables()->attach($source->id, ['quantity' => 3]);
        CheckoutRequest::saving(fn (CheckoutRequest $request): bool => $request->id !== $requests->last()->id);
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])->assertOk()->assertStatusMessageIs('error');
        foreach ($requests as $request) {
            $this->assertEquals($source->id, $request->fresh()->requestable_id);
        }
        $this->assertDatabaseHas('kits_consumables', ['kit_id' => $kit->id, 'consumable_id' => $source->id, 'quantity' => 3]);
        $this->assertFalse($source->fresh()->trashed());
        $this->assertEquals(4, $target->fresh()->qty);
    }

    public function test_legacy_purchase_links_follow_repeated_merges_and_keep_history(): void
    {
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 4]);
        $source = Consumable::factory()->create(['company_id' => null, 'qty' => 6]);
        $purchase = new Purchase([
            'invoice_number' => 'Merge-reference', 'invoice_file' => 'invoice.pdf',
            'bitrix_id' => 123, 'final_price' => 20, 'comment' => 'Merge test',
            'supplier_id' => Supplier::factory()->create()->id,
            'legal_person_id' => LegalPerson::firstOrCreate(['name' => 'Merge company'])->id,
            'invoice_type_id' => InvoiceType::firstOrCreate(['name' => 'Merge type'])->id,
            'consumables_json' => json_encode([['consumable_id' => (string) $source->id, 'quantity' => 6]]),
        ]);
        $this->assertTrue($purchase->save());
        $source->purchase_id = $purchase->id;
        $this->assertTrue($source->save());
        $otherPurchase = $this->purchaseWithLines([]);
        $target->purchase_id = $otherPurchase->id;
        $this->assertTrue($target->save());
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
            ->assertOk()->assertStatusMessageIs('success');
        $this->assertTrue($source->fresh()->trashed());
        $this->assertEquals(10, $target->fresh()->qty);
        $this->assertDatabaseHas('consumable_merges', [
            'source_id' => $source->id, 'target_id' => $target->id,
            'current_target_id' => $target->id, 'source_purchase_id' => $purchase->id,
        ]);
        $next = Consumable::factory()->create(['company_id' => null, 'qty' => 0]);
        $this->postJson(route('api.consumables.compact', $next), ['id_array' => [$target->id]])
            ->assertOk()->assertStatusMessageIs('success');
        $this->assertDatabaseHas('consumable_merges', [
            'source_id' => $source->id, 'target_id' => $target->id,
            'current_target_id' => $next->id, 'source_purchase_id' => $purchase->id,
        ]);
        $this->assertTrue($source->refresh()->restore());
        Statuslabel::factory()->create(['name' => 'Доступные']);
        foreach ([$purchase, $otherPurchase] as $linkedPurchase) {
            $this->assertSame([$next->id], $linkedPurchase->currentConsumables()->pluck('consumables.id')->all());
            $this->getJson(route('api.consumables.index', ['purchase_id' => $linkedPurchase->id]))
                ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('rows.0.id', $next->id);
            $this->getJson(route('api.purchases.show', $linkedPurchase))
                ->assertOk()->assertJsonPath('consumables_count_real', 1);
        }
        $this->getJson(route('api.purchases.index'))->assertOk()
            ->assertJsonPath('rows.0.consumables_count_real', 1);
        $actor = auth()->user();
        $this->actingAs($actor, 'web')->get(route('purchases.show', $purchase))->assertOk();
        $this->actingAsForApi($actor);
        $this->postJson(route('api.purchases.consumables_check', $purchase))
            ->assertOk()->assertStatusMessageIs('success');
        $this->assertSame(3, Consumable::withTrashed()->count());
        $this->assertEquals(10, $next->fresh()->qty);
    }

    private function purchaseWithLines(array $lines): Purchase
    {
        $purchase = new Purchase([
            'invoice_number' => 'Merge-lines', 'invoice_file' => 'invoice.pdf',
            'bitrix_id' => 123, 'final_price' => 20, 'comment' => 'Merge lines test',
            'supplier_id' => Supplier::factory()->create()->id,
            'legal_person_id' => LegalPerson::firstOrCreate(['name' => 'Merge company'])->id,
            'invoice_type_id' => InvoiceType::firstOrCreate(['name' => 'Merge type'])->id,
            'consumables_json' => json_encode($lines),
        ]);
        $this->assertTrue($purchase->save());

        return $purchase;
    }

    public function test_legacy_links_are_deduplicated_and_respect_company_scope(): void
    {
        $purchase = $this->purchaseWithLines([]);
        $company = Company::factory()->create();
        $target = Consumable::factory()->for($company)->create(['purchase_id' => $purchase->id, 'qty' => 2]);
        $source = Consumable::factory()->for($company)->create(['purchase_id' => $purchase->id, 'qty' => 3]);
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
            ->assertOk()->assertStatusMessageIs('success');
        $this->assertSame([$target->id], $purchase->currentConsumables()->pluck('consumables.id')->all());
        $this->settings->enableMultipleFullCompanySupport();
        $this->actingAsForApi(User::factory()->viewConsumables()->for(Company::factory()->create())->create())
            ->getJson(route('api.consumables.index', ['purchase_id' => $purchase->id]))
            ->assertOk()->assertJsonPath('total', 0);
        $this->assertSame(0, $purchase->currentConsumables()->count());
    }

    public function test_legacy_link_transfer_requires_purchase_permission(): void
    {
        $purchase = $this->purchaseWithLines([]);
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 2]);
        $source = Consumable::factory()->create(['company_id' => null, 'purchase_id' => $purchase->id, 'qty' => 3]);
        $this->actingAsForApi(User::factory()->editConsumables()->deleteConsumables()->create())
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
            ->assertForbidden();
        $this->assertFalse($source->fresh()->trashed());
        $this->assertDatabaseMissing('consumable_merges', ['source_id' => $source->id]);
    }

    public function test_failed_later_merge_preserves_current_purchase_target(): void
    {
        $purchase = $this->purchaseWithLines([]);
        $source = Consumable::factory()->create(['company_id' => null, 'purchase_id' => $purchase->id, 'qty' => 2]);
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 3]);
        $next = Consumable::factory()->create(['company_id' => null, 'qty' => 4]);
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
            ->assertOk()->assertStatusMessageIs('success');
        Event::listen('eloquent.saving: '.Actionlog::class, fn (Actionlog $log) => $log->action_type === 'delete' && $log->item_id == $target->id ? false : null);
        $this->postJson(route('api.consumables.compact', $next), ['id_array' => [$target->id]])
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertDatabaseHas('consumable_merges', ['source_id' => $source->id, 'current_target_id' => $target->id]);
        $this->assertDatabaseMissing('consumable_merges', ['source_id' => $target->id]);
        $this->assertSame([$target->id], $purchase->currentConsumables()->pluck('consumables.id')->all());
        $this->assertEquals(5, $target->fresh()->qty);
        $this->assertEquals(4, $next->fresh()->qty);
    }

    public function test_merge_preserves_purchase_lines_and_receives_only_selected_line(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 2]);
        $source = Consumable::factory()->create(['company_id' => null, 'qty' => 1]);
        $lines = [
            ['id' => 1, 'consumable_id' => $target->id, 'quantity' => 4, 'reviewed' => 2, 'purchase_cost' => '10.25', 'nds' => 20],
            ['id' => 2, 'consumable_id' => (string) $source->id, 'quantity' => 3, 'reviewed' => 1, 'purchase_cost' => '15.50', 'nds' => 0],
        ];
        $purchase = $this->purchaseWithLines($lines);
        $archived = $this->purchaseWithLines([$lines[1]]);
        $this->assertTrue($archived->delete());
        $this->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
            ->assertOk()->assertStatusMessageIs('success');
        $lines[1]['consumable_id'] = $target->id;
        $this->assertSame($lines, json_decode($purchase->fresh()->consumables_json, true));
        $this->assertSame([$lines[1]], json_decode($archived->fresh()->consumables_json, true));
        $this->assertTrue($archived->fresh()->trashed());
        $payload = ['purchase_id' => $purchase->id, 'row_id' => 2, 'quantity' => 2, 'purchase_cost' => '15.50'];
        $this->postJson(route('api.consumables.review', $source), $payload)
            ->assertOk()->assertStatusMessageIs('error');
        $this->postJson(route('api.consumables.review', $target), $payload)
            ->assertOk()->assertStatusMessageIs('success');
        $lines[1]['reviewed'] = 3;
        $this->assertSame($lines, json_decode($purchase->fresh()->consumables_json, true));
        $this->assertEquals(5, $target->fresh()->qty);
        $this->postJson(route('api.consumables.review', $target), $payload)
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertEquals(5, $target->fresh()->qty);

        $next = Consumable::factory()->create(['company_id' => null, 'qty' => 0]);
        $this->postJson(route('api.consumables.compact', $next), ['id_array' => [$target->id]])
            ->assertOk()->assertStatusMessageIs('success');
        foreach ($lines as &$line) {
            $line['consumable_id'] = $next->id;
        }
        unset($line);
        $this->assertSame($lines, json_decode($purchase->fresh()->consumables_json, true));
        $this->assertEquals(5, $next->fresh()->qty);
    }

    public function test_purchase_update_permission_is_required_for_link_transfer(): void
    {
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 2]);
        $source = Consumable::factory()->create(['company_id' => null, 'qty' => 1]);
        $purchase = $this->purchaseWithLines([['id' => 1, 'consumable_id' => $source->id, 'quantity' => 1]]);
        $before = $purchase->consumables_json;
        $this->actingAsForApi(User::factory()->editConsumables()->deleteConsumables()->create())
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
            ->assertForbidden();
        $this->assertSame($before, $purchase->fresh()->consumables_json);
        $this->assertFalse($source->fresh()->trashed());
        $this->assertEquals(2, $target->fresh()->qty);
    }

    public function test_late_stock_validation_failure_rolls_back_purchase_lines(): void
    {
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 99999]);
        $source = Consumable::factory()->create(['company_id' => null, 'qty' => 1]);
        $purchase = $this->purchaseWithLines([['id' => 1, 'consumable_id' => $source->id, 'quantity' => 1]]);
        $before = $purchase->consumables_json;
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertSame($before, $purchase->fresh()->consumables_json);
        $this->assertFalse($source->fresh()->trashed());
        $this->assertDatabaseMissing('consumable_merges', ['source_id' => $source->id]);
    }

    public static function ambiguous_row_ids(): array
    {
        return ['duplicate' => [1], 'missing' => [null], 'invalid' => ['invalid']];
    }

    #[DataProvider('ambiguous_row_ids')]
    public function test_ambiguous_purchase_rows_prevent_merge(mixed $rowId): void
    {
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 2]);
        $source = Consumable::factory()->create(['company_id' => null, 'qty' => 1]);
        $purchase = $this->purchaseWithLines([
            ['id' => 1, 'consumable_id' => $target->id, 'quantity' => 2],
            ['id' => $rowId, 'consumable_id' => $source->id, 'quantity' => 1],
        ]);
        $before = $purchase->consumables_json;
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertSame($before, $purchase->fresh()->consumables_json);
        $this->assertFalse($source->fresh()->trashed());
    }

    public function test_failed_purchase_save_rolls_back_preceding_purchase_transfer(): void
    {
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 2]);
        $source = Consumable::factory()->create(['company_id' => null, 'qty' => 1]);
        $lines = [['id' => 1, 'consumable_id' => $source->id, 'quantity' => 1]];
        $first = $this->purchaseWithLines($lines);
        $second = $this->purchaseWithLines($lines);
        Event::listen('eloquent.saving: '.Purchase::class, fn (Purchase $purchase) => $purchase->id === $second->id ? false : null);
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertSame($lines, json_decode($first->fresh()->consumables_json, true));
        $this->assertSame($lines, json_decode($second->fresh()->consumables_json, true));
        $this->assertFalse($source->fresh()->trashed());
        $this->assertEquals(2, $target->fresh()->qty);
        $this->assertDatabaseMissing('consumable_merges', ['source_id' => $source->id]);
    }
}
