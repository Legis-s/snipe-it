<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\Statuslabel;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AssetPurchaseWorkflowTest extends TestCase
{
    public static function workflow_permissions(): array
    {
        return [
            'viewer' => [false, false],
            'editor' => [true, false],
            'reviewer' => [false, true],
            'editor and reviewer' => [true, true],
        ];
    }

    #[DataProvider('workflow_permissions')]
    public function test_advertised_workflow_actions_match_endpoint_permissions(bool $canInventory, bool $canReview): void
    {
        $asset = Asset::factory()->create();
        $actor = User::factory()->create([
            'permissions' => json_encode([
                'assets.view' => '1',
                'assets.edit' => $canInventory ? '1' : '0',
                'assets.review' => $canReview ? '1' : '0',
            ]),
        ]);
        Statuslabel::factory()->readyToDeploy()->create(['name' => 'Ожидает проверки']);
        Statuslabel::factory()->readyToDeploy()->create(['name' => 'Доступные']);

        $this->actingAsForApi($actor)
            ->getJson(route('api.assets.index'))
            ->assertOk()
            ->assertJsonPath('rows.0.id', $asset->id)
            ->assertJsonPath('rows.0.available_actions.inventory', $canInventory)
            ->assertJsonPath('rows.0.available_actions.review', $canReview);

        foreach (['inventory' => $canInventory, 'review' => $canReview] as $operation => $allowed) {
            $response = $this->postJson(route('api.assets.'.$operation, $asset->id));

            if ($allowed) {
                $response->assertOk()->assertJsonPath('id', $asset->id);
            } else {
                $response->assertForbidden();
            }
        }
    }

    public static function operations(): array
    {
        return [
            'inventory' => ['inventory', 'Ожидает проверки', 'tag'],
            'review' => ['review', 'Доступные', 'audit'],
        ];
    }

    #[DataProvider('operations')]
    public function test_workflow_preserves_logging_and_event_dispatcher(string $operation, string $statusName, string $action): void
    {
        $status = Statuslabel::factory()->readyToDeploy()->create(['name' => $statusName]);
        $asset = Asset::factory()->create();
        $actor = User::factory()->superuser()->create();
        $dispatcher = Asset::getEventDispatcher();

        $this->actingAsForApi($actor)
            ->postJson(route('api.assets.'.$operation, $asset->id), ['asset_tag' => 'Purchase-workflow-tag'])
            ->assertOk()
            ->assertJsonPath('id', $asset->id);

        $asset->refresh();
        $this->assertEquals($status->id, $asset->status_id);
        $this->assertSame($dispatcher, Asset::getEventDispatcher());
        $this->assertSame([$action], $asset->assetlog()->whereIn('action_type', ['tag', 'audit', 'update'])->pluck('action_type')->all());
        if ($operation === 'inventory') {
            $this->assertSame('Purchase-workflow-tag', $asset->asset_tag);
        } else {
            $this->assertEquals($actor->id, $asset->user_verified_id);
            $this->assertNotNull($asset->last_audit_date);
        }

        $asset->name = 'Ordinary update after workflow';
        $this->assertTrue($asset->save());
        $this->assertTrue($asset->assetlog()->where('action_type', 'update')->exists());
    }

    #[DataProvider('operations')]
    public function test_workflow_requires_permission(string $operation, string $statusName, string $action): void
    {
        $asset = Asset::factory()->create();
        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->postJson(route('api.assets.'.$operation, $asset->id))
            ->assertForbidden();
    }

    #[DataProvider('operations')]
    public function test_missing_status_does_not_change_asset(string $operation, string $statusName, string $action): void
    {
        $asset = Asset::factory()->create();
        $original = $asset->refresh()->getAttributes();
        $dispatcher = Asset::getEventDispatcher();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.assets.'.$operation, $asset->id), ['asset_tag' => 'Not-saved'])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $this->assertSame($original, $asset->refresh()->getAttributes());
        $this->assertSame($dispatcher, Asset::getEventDispatcher());
        $this->assertFalse($asset->assetlog()->whereIn('action_type', ['tag', 'audit'])->exists());
    }

    public function test_invalid_inventory_tag_preserves_dispatcher_and_existing_asset(): void
    {
        Statuslabel::factory()->readyToDeploy()->create(['name' => 'Ожидает проверки']);
        $existing = Asset::factory()->create();
        $asset = Asset::factory()->create();
        $originalTag = $asset->asset_tag;
        $dispatcher = Asset::getEventDispatcher();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->postJson(route('api.assets.inventory', $asset->id), ['asset_tag' => $existing->asset_tag])
            ->assertOk()
            ->assertStatusMessageIs('error');

        $this->assertSame($originalTag, $asset->refresh()->asset_tag);
        $this->assertSame($dispatcher, Asset::getEventDispatcher());
        $this->assertFalse($asset->assetlog()->where('action_type', 'tag')->exists());
    }
}
