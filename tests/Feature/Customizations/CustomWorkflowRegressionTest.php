<?php

namespace Tests\Feature\Customizations;

use App\Console\Commands\SyncBitrix;
use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\Device;
use App\Models\InvoiceType;
use App\Models\LegalPerson;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Response;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\DB;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class CustomWorkflowRegressionTest extends TestCase
{
    public function test_asset_model_api_ignores_obsolete_custom_user_id(): void
    {
        $actor = User::factory()->superuser()->create();
        $other = User::factory()->create();
        $this->actingAsForApi($actor);
        $response = $this->postJson(route('api.models.store'), [
            'name' => 'Legacy payload model',
            'category_id' => Category::factory()->assetLaptopCategory()->create()->id,
            'user_id' => $other->id,
        ])->assertOk()->assertStatusMessageIs('success');

        $model = AssetModel::findOrFail($response->json('payload.id'));
        $this->assertArrayNotHasKey('user_id', $model->getAttributes());
        $creator = $model->created_by;
        $this->patchJson(route('api.models.update', $model), [
            'name' => 'Updated legacy payload model',
            'user_id' => $other->id,
        ])->assertOk()->assertStatusMessageIs('success');
        $this->assertSame('Updated legacy payload model', $model->fresh()->name);
        $this->assertEquals($creator, $model->fresh()->created_by);
    }

    public static function protected_writes(): array
    {
        return [
            ['post', '/api/v1/inventories/clearallemply'],
            ['post', '/api/v1/inventories'],
            ['patch', '/api/v1/inventories/999'],
            ['patch', '/api/v1/inventory_items/999'],
            ['post', '/api/v1/bitrix_sync/users'],
            ['post', '/api/v1/bitrix_sync/locations'],
            ['post', '/api/v1/bitrix_sync/suppliers'],
            ['post', '/api/v1/bitrix_sync/legal_persons'],
            ['post', '/api/v1/bitrix_sync/invoice_types'],
            ['post', '/api/v1/purchases/999/paid'],
            ['post', '/api/v1/purchases/999/in_payment'],
            ['post', '/api/v1/purchases/999/reject'],
            ['post', '/api/v1/purchases/999/resend'],
            ['post', '/api/v1/purchases/999/consumables_check'],
            ['post', '/api/v1/purchases/999/consumables_line'],
            ['post', '/api/v1/purchases/999/bitrix_task/999'],
            ['post', '/api/v1/hardware/999/inventory'],
            ['post', '/api/v1/hardware/999/review'],
            ['post', '/api/v1/consumables/999/review'],
            ['post', '/api/v1/consumables/999/compact'],
        ];
    }

    #[DataProvider('protected_writes')]
    public function test_read_only_users_cannot_write(string $method, string $url): void
    {
        $user = User::factory()->create(['permissions' => json_encode(['purchases.view' => 1, 'locations.view' => 1])]);
        $this->actingAsForApi($user)->json(strtoupper($method), $url)->assertForbidden();
    }

    public function test_device_endpoint_returns_device_and_requires_permission(): void
    {
        $device = Device::create(['number' => 'MDM-42', 'imei' => '123456']);
        $this->actingAsForApi(User::factory()->create())
            ->getJson('/api/v1/devices/'.$device->id)->assertForbidden();
        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson('/api/v1/devices/'.$device->id)->assertOk()
            ->assertJsonPath('id', $device->id)->assertJsonPath('number', 'MDM-42');
    }

    public function test_checkout_uses_one_ledger_and_rejects_over_allocation(): void
    {
        $this->actingAs(User::factory()->superuser()->create());
        $item = Consumable::factory()->create(['qty' => 2]);
        $target = User::factory()->create();
        $this->assertTrue($item->checkOut($target, 2));
        $this->assertSame(0, $item->fresh()->numRemaining());
        $this->assertFalse($item->checkOut($target, 1));
        $this->assertFalse($item->checkOut($target, -1));
        $this->assertDatabaseCount('consumables_locations', 1);
        $this->assertDatabaseCount('consumables_users', 0);
    }

    public static function invalid_returns(): array
    {
        return [[0], [-1], [3], [1.5], ['invalid']];
    }

    #[DataProvider('invalid_returns')]
    public function test_invalid_returns_do_not_change_stock(mixed $quantity): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());
        $item = Consumable::factory()->create(['qty' => 2]);
        $item->checkOut(User::factory()->create(), 2);
        $assignment = ConsumableAssignment::firstOrFail();
        $this->postJson('/api/v1/consumableassignments/'.$assignment->id.'/return', ['quantity' => $quantity])
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertSame(2, (int) $assignment->fresh()->quantity);
        $this->assertSame(0, $item->fresh()->numRemaining());
    }

    public function test_return_requires_checkout_permission_and_restores_stock(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());
        $item = Consumable::factory()->create(['qty' => 2]);
        $item->checkOut(User::factory()->create(), 2);
        $assignment = ConsumableAssignment::firstOrFail();
        $url = '/api/v1/consumableassignments/'.$assignment->id.'/return';
        $this->actingAsForApi(User::factory()->viewConsumables()->create())
            ->postJson($url, ['quantity' => 1])->assertForbidden();
        $this->actingAsForApi(User::factory()->checkoutConsumables()->create())
            ->postJson($url, ['quantity' => 1])->assertOk()->assertStatusMessageIs('success');
        $this->assertSame(1, $item->fresh()->numRemaining());
        $this->assertSame(1, (int) $assignment->fresh()->quantity);
        $this->assertDatabaseHas('action_logs', ['action_type' => 'return', 'item_id' => $item->id, 'quantity' => 1]);
    }

    private function purchase(Consumable $item): Purchase
    {
        $purchase = new Purchase([
            'invoice_number' => 'INV-42', 'invoice_file' => 'invoice.pdf',
            'bitrix_id' => 42, 'final_price' => 20, 'comment' => 'Receipt test',
            'supplier_id' => Supplier::factory()->create()->id,
            'legal_person_id' => LegalPerson::create(['name' => 'Legal Person'])->id,
            'invoice_type_id' => InvoiceType::create(['name' => 'Invoice Type'])->id,
            'consumables_json' => json_encode([['id' => 1, 'consumable_id' => $item->id, 'quantity' => 2]]),
        ]);
        $this->assertTrue($purchase->save());

        return $purchase;
    }

    public function test_receipt_records_order_and_rejects_repeated_receipt(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());
        $item = Consumable::factory()->create(['qty' => 0]);
        $purchase = $this->purchase($item);
        $payload = ['purchase_id' => $purchase->id, 'quantity' => 2, 'purchase_cost' => 10];
        $this->postJson('/api/v1/consumables/'.$item->id.'/review', $payload)
            ->assertOk()->assertStatusMessageIs('success');
        $this->assertSame(2, (int) $item->fresh()->qty);
        $this->assertDatabaseHas('order_items', ['item_id' => $item->id, 'item_type' => Consumable::class, 'qty' => 2, 'price' => 10]);
        $this->assertSame(2, json_decode($purchase->fresh()->consumables_json, true)[0]['reviewed']);
        $this->postJson('/api/v1/consumables/'.$item->id.'/review', $payload)->assertOk()->assertStatusMessageIs('error');
        $this->assertDatabaseCount('consumables_locations', 1);
        $this->assertSame(2, (int) $item->fresh()->qty);
    }

    public function test_receipt_failure_rolls_back_order_stock_and_assignment(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());
        $item = Consumable::factory()->create(['qty' => 0]);
        $purchase = $this->purchase($item);
        DB::table('purchases')->where('id', $purchase->id)->update(['invoice_number' => '']);
        $this->postJson('/api/v1/consumables/'.$item->id.'/review', [
            'purchase_id' => $purchase->id, 'quantity' => 2, 'purchase_cost' => 10,
        ])->assertOk()->assertStatusMessageIs('error');
        $this->assertSame(0, (int) $item->fresh()->qty);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertDatabaseCount('consumables_locations', 0);
    }

    public static function merged_source_states(): array
    {
        return ['retired' => [false], 'manually restored' => [true]];
    }

    #[DataProvider('merged_source_states')]
    public function test_receipt_cannot_reactivate_a_merged_source(bool $restore): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());
        $source = Consumable::factory()->create(['company_id' => null, 'qty' => 0]);
        $target = Consumable::factory()->create(['company_id' => null, 'qty' => 0]);
        $this->postJson(route('api.consumables.compact', $target), ['id_array' => [$source->id]])
            ->assertOk()->assertStatusMessageIs('success');
        // A stale purchase edit can reintroduce the retired ID after the merge.
        $purchase = $this->purchase($source);
        $originalLines = $purchase->consumables_json;
        if ($restore) {
            $this->assertTrue($source->refresh()->restore());
        }
        $this->postJson(route('api.consumables.review', $source), [
            'purchase_id' => $purchase->id, 'quantity' => 2, 'purchase_cost' => 10,
        ])->assertOk()->assertStatusMessageIs('error')
            ->assertJsonPath('messages.consumable_id.0', trans('general.consumable_already_merged'));
        $this->assertEquals(0, $source->fresh()->qty);
        $this->assertEquals(0, $target->fresh()->qty);
        $this->assertSame(! $restore, $source->fresh()->trashed());
        $this->assertSame($originalLines, $purchase->fresh()->consumables_json);
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('consumables_locations', 0);
    }

    public function test_receipt_still_restores_an_unmerged_consumable(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());
        $item = Consumable::factory()->create(['qty' => 0]);
        $purchase = $this->purchase($item);
        $this->assertTrue($item->delete());
        $this->postJson(route('api.consumables.review', $item), [
            'purchase_id' => $purchase->id, 'quantity' => 2, 'purchase_cost' => 10,
        ])->assertOk()->assertStatusMessageIs('success');
        $this->assertFalse($item->fresh()->trashed());
        $this->assertEquals(2, $item->fresh()->qty);
    }

    public function test_bitrix_sync_never_uses_email_as_password(): void
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('request')->once()->andReturn(new Response(200, [], json_encode([
            'result' => [['ID' => 4242, 'ACTIVE' => 'Y', 'EMAIL' => 'sync@example.test', 'NAME' => 'Sync', 'LAST_NAME' => 'User']],
        ])));
        $command = new SyncBitrix;
        $command->setOutput(new OutputStyle(new ArrayInput([]), new BufferedOutput));
        (new ReflectionMethod($command, 'syncUsers'))->invoke($command, $client, 'https://example.test/rest/');
        $user = User::where('bitrix_id', 4242)->firstOrFail();
        $this->assertSame($user->noPassword(), $user->password);
    }

    public static function existing_passwords(): array
    {
        return [[true], [false]];
    }

    #[DataProvider('existing_passwords')]
    public function test_bitrix_sync_replaces_only_predictable_existing_passwords(bool $weak): void
    {
        $email = 'existing@example.test';
        $password = bcrypt($weak ? $email : 'A-strong-existing-secret');
        $user = User::factory()->create(['email' => $email, 'username' => $email, 'bitrix_id' => 77, 'password' => $password]);
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('request')->once()->andReturn(new Response(200, [], json_encode([
            'result' => [['ID' => 77, 'ACTIVE' => 'Y', 'EMAIL' => $email, 'NAME' => 'Existing', 'LAST_NAME' => 'User']],
        ])));
        $command = new SyncBitrix;
        $command->setOutput(new OutputStyle(new ArrayInput([]), new BufferedOutput));
        (new ReflectionMethod($command, 'syncUsers'))->invoke($command, $client, 'https://example.test/rest/');
        $this->assertSame($weak ? $user->noPassword() : $password, $user->fresh()->password);
    }

    public function test_receipt_rejects_consumable_not_in_purchase(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());
        $item = Consumable::factory()->create(['qty' => 0]);
        $other = Consumable::factory()->create(['qty' => 0]);
        $purchase = $this->purchase($item);
        $this->postJson('/api/v1/consumables/'.$other->id.'/review', [
            'purchase_id' => $purchase->id, 'quantity' => 1, 'purchase_cost' => 10,
        ])->assertOk()->assertStatusMessageIs('error');
        $this->assertDatabaseCount('orders', 0);
        $this->assertSame(0, (int) $other->fresh()->qty);
    }

    public function test_admin_can_run_inventory_cleanup(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson('/api/v1/inventories/clearallemply')->assertOk()->assertStatusMessageIs('success');
    }

    public function test_receipt_updates_only_selected_duplicate_line(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());
        $item = Consumable::factory()->create(['qty' => 0]);
        $purchase = $this->purchase($item);
        $purchase->consumables_json = json_encode([
            ['id' => 1, 'consumable_id' => $item->id, 'quantity' => 2],
            ['id' => 2, 'consumable_id' => $item->id, 'quantity' => 3],
        ]);
        $this->assertTrue($purchase->save());
        $this->postJson('/api/v1/consumables/'.$item->id.'/review', [
            'purchase_id' => $purchase->id, 'row_id' => 2, 'quantity' => 3, 'purchase_cost' => 10,
        ])->assertOk()->assertStatusMessageIs('success');
        $lines = json_decode($purchase->fresh()->consumables_json, true);
        $this->assertArrayNotHasKey('reviewed', $lines[0]);
        $this->assertSame(3, $lines[1]['reviewed']);
        $this->assertSame(3, (int) $item->fresh()->qty);
    }
}
