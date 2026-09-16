<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\Location;
use Tests\TestCase;

class LocationTest extends TestCase
{
    public static function asset_location_links(): array
    {
        return [
            'current location' => ['location_id'],
            'default location' => ['rtd_location_id'],
            'assigned location' => ['assigned_to'],
        ];
    }

    /**
     * @dataProvider asset_location_links
     */
    public function test_bitrix_hiding_ignores_deleted_assets(string $foreignKey): void
    {
        $location = Location::factory()->create();
        $this->assertTrue($location->isDeletableNoGate());

        $attributes = [$foreignKey => $location->id];
        if ($foreignKey === 'assigned_to') {
            $attributes['assigned_type'] = Location::class;
        }

        $asset = Asset::factory()->create($attributes);
        $this->assertFalse($location->fresh()->isDeletableNoGate());

        $asset->delete();
        $this->assertSoftDeleted($asset);
        $this->assertTrue($location->fresh()->isDeletableNoGate());

        $asset->restore();
        $this->assertFalse($location->fresh()->isDeletableNoGate());
    }

    public function test_passes_if_not_self_parent()
    {
        $a = Location::factory()->make([
            'name' => 'Test Location',
            'id' => 1,
            'parent_id' => Location::factory()->create(['id' => 10])->id,
        ]);

        $this->assertTrue($a->isValid());
    }

    public function test_fails_if_self_parent()
    {
        $a = Location::factory()->make([
            'name' => 'Test Location',
            'id' => 1,
            'parent_id' => 1,
        ]);

        $this->assertFalse($a->isValid());
        $this->assertStringContainsString(trans('validation.non_circular', ['attribute' => 'parent id']), $a->getErrors());
    }
}
