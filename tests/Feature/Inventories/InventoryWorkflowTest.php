<?php

namespace Tests\Feature\Inventories;

use App\Models\Asset;
use App\Models\Company;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\InventoryStatuslabel;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InventoryWorkflowTest extends TestCase
{
    private function item(?Asset $asset = null): InventoryItem
    {
        $asset ??= Asset::factory()->create();
        $inventory = Inventory::create([
            'name' => 'Workflow audit', 'responsible_id' => User::factory()->create()->id,
            'location_id' => Location::factory()->create()->id, 'status' => 'START',
        ]);
        $label = InventoryStatuslabel::firstOrCreate(['name' => 'Workflow found'], ['success' => 1]);
        $item = new InventoryItem([
            'name' => 'Snapshot item', 'model' => 'Snapshot model', 'category' => 'Snapshot category',
            'tag' => $asset->asset_tag, 'asset_id' => $asset->id, 'inventory_id' => $inventory->id,
            'checked' => false, 'successfully' => false, 'status_id' => $label->id,
        ]);
        $this->assertTrue($item->save());

        return $item;
    }

    public function test_search_uses_snapshot_fields(): void
    {
        $item = $this->item();
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.inventory_items.index', ['inventory_id' => $item->inventory_id, 'search' => 'Snapshot']))
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('rows.0.id', $item->id);
    }

    public function test_update_preserves_links_and_recalculates_success_and_completion(): void
    {
        $item = $this->item();
        $bad = InventoryStatuslabel::create(['name' => 'Workflow missing', 'success' => 0]);
        $this->actingAsForApi(User::factory()->superuser()->create());
        $url = route('api.inventory_items.update', $item);
        $this->patchJson($url, ['checked' => true, 'asset_id' => 999999, 'inventory_id' => 999999, 'successfully' => false])
            ->assertOk()->assertStatusMessageIs('success');
        $this->assertEquals($item->asset_id, $item->fresh()->asset_id);
        $this->assertEquals($item->inventory_id, $item->fresh()->inventory_id);
        $this->assertTrue((bool) $item->fresh()->successfully);
        $this->assertSame('FINISH_OK', $item->inventory->fresh()->status);
        $this->assertNotNull($item->asset->fresh()->last_audit_date);
        $this->patchJson($url, ['status_id' => $bad->id, 'successfully' => true])
            ->assertOk()->assertStatusMessageIs('success');
        $this->assertFalse((bool) $item->fresh()->successfully);
        $this->assertSame('FINISH_BAD', $item->inventory->fresh()->status);
        $this->patchJson($url, ['checked' => false])->assertOk()->assertStatusMessageIs('success');
        $this->assertSame('START', $item->inventory->fresh()->status);
        $this->assertNull($item->fresh()->checked_at);
    }

    public static function refused_saves(): array
    {
        return ['asset' => [Asset::class], 'inventory' => [Inventory::class]];
    }

    #[DataProvider('refused_saves')]
    public function test_save_failure_rolls_back_all_records_and_new_photo(string $model): void
    {
        Storage::fake('public');
        $item = $this->item();
        $before = $item->asset->last_audit_date;
        Event::listen('eloquent.saving: '.$model, fn () => false);
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.inventory_items.update', $item), [
                'checked' => true,
                'photo' => base64_encode(base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/l9sAAAAASUVORK5CYII=')),
            ])->assertOk()->assertStatusMessageIs('error');
        $this->assertFalse((bool) $item->fresh()->checked);
        $this->assertEquals($before, $item->asset->fresh()->last_audit_date);
        $this->assertSame('START', $item->inventory->fresh()->status);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_hidden_asset_and_missing_permission_cannot_be_updated(): void
    {
        $item = $this->item(Asset::factory()->for(Company::factory()->create())->create());
        $this->actingAsForApi(User::factory()->create())
            ->patchJson(route('api.inventory_items.update', $item), ['checked' => true])->assertForbidden();
        $this->settings->enableMultipleFullCompanySupport();
        $actor = User::factory()->for(Company::factory()->create())->create(['permissions' => json_encode(['assets.audit' => 1])]);
        $this->actingAsForApi($actor)->patchJson(route('api.inventory_items.update', $item), ['checked' => true])
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertFalse((bool) $item->fresh()->checked);
    }

    public static function coordinates(): array
    {
        return [['', false], ['0.0, 0.0', false], ['0,0', false], ['invalid', false], ['91, 20', false], ['47.0, 28.8', true]];
    }

    public function test_summary_renders_progress_details_and_photo_fallback(): void
    {
        $item = $this->item();
        $inventory = $item->inventory;
        $inventory->responsible = 'Responsible person';
        $inventory->responsible_photo = 'missing.jpg';
        $inventory->device = 'Audit device';
        $this->assertTrue($inventory->save());
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('inventories.show', $inventory))->assertOk()
            ->assertSee('class="inventory-summary-stats"', false)
            ->assertSee('aria-valuenow="0"', false)
            ->assertSee('Responsible person')->assertSee('Audit device')
            ->assertSee('onerror="this.parentElement.hidden = true;"', false);
        $item->checked = true;
        $item->successfully = true;
        $this->assertTrue($item->save());
        $this->get(route('inventories.show', $inventory))->assertOk()
            ->assertSee('aria-valuenow="100"', false);
    }

    public function test_unchecked_sibling_keeps_inventory_open_and_invalid_photo_is_rejected(): void
    {
        Storage::fake('public');
        $item = $this->item();
        $sibling = $item->replicate();
        $this->assertTrue($sibling->save());
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.inventory_items.update', $item), ['checked' => true])
            ->assertOk()->assertStatusMessageIs('success');
        $this->assertSame('START', $item->inventory->fresh()->status);
        $this->patchJson(route('api.inventory_items.update', $sibling), ['checked' => true, 'photo' => base64_encode('not an image')])
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertFalse((bool) $sibling->fresh()->checked);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_deleted_asset_cannot_be_audited_and_page_requires_permission(): void
    {
        $item = $this->item();
        $this->assertTrue($item->asset->delete());
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->patchJson(route('api.inventory_items.update', $item), ['checked' => true])
            ->assertOk()->assertStatusMessageIs('error');
        $this->assertFalse((bool) $item->fresh()->checked);
        $this->actingAs(User::factory()->create(), 'web')
            ->get(route('inventories.show', $item->inventory_id))->assertForbidden();
    }

    #[DataProvider('coordinates')]
    public function test_map_container_and_script_use_the_same_validated_coordinates(string $coords, bool $visible): void
    {
        $item = $this->item();
        $inventory = $item->inventory;
        $inventory->coords = $coords;
        $this->assertTrue($inventory->save());
        $response = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('inventories.show', $inventory))->assertOk();
        if ($visible) {
            $response->assertSee('id="map"', false)->assertSee('ymaps.ready(init)', false);
        } else {
            $response->assertDontSee('id="map"', false)->assertDontSee('ymaps.ready(init)', false);
        }
    }
}
