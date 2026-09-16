<?php

namespace Tests\Unit\Transformers;

use App\Http\Transformers\LocationsMapTransformer;
use App\Models\Location;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class LocationsMapTransformerTest extends TestCase
{
    #[DataProvider('invalid_coordinates')]
    public function test_invalid_coordinates_do_not_produce_map_features(?string $coordinates): void
    {
        $location = $this->location($coordinates, 1, 0);

        $this->assertSame([], (new LocationsMapTransformer)->transformForMap($location));
    }

    public static function invalid_coordinates(): array
    {
        return [[null], [''], ['55.7'], ['55,37,12'], ['north,37'], ['91,37'], ['55,-181'], ['1e999,37']];
    }

    #[DataProvider('asset_counts')]
    public function test_valid_features_have_numeric_coordinates_counts_and_status_colors(int $total, int $checked, string $color): void
    {
        $location = $this->location(' 55.76, 37.64 ', $total, $checked);
        $location->name = '<script>alert(1)</script>';

        $feature = (new LocationsMapTransformer)->transformForMap($location);

        $this->assertSame([55.76, 37.64], $feature['geometry']['coordinates']);
        $this->assertSame($total, $feature['assets_count']);
        $this->assertSame($checked, $feature['checked_assets_count']);
        $this->assertSame($color, $feature['options']['iconColor']);
        $this->assertStringNotContainsString('<script>', $feature['properties']['balloonContentHeader']);
        $this->assertFalse($location->relationLoaded('assets'));
    }

    public static function asset_counts(): array
    {
        return [[0, 0, '#808080'], [3, 3, '#00FF00'], [3, 1, '#FF0000']];
    }

    public function test_collection_skips_invalid_and_inactive_empty_locations_and_keeps_zero_coordinates(): void
    {
        $invalid = $this->location('invalid', 1, 0);
        $inactive = $this->location('55,37', 0, 0);
        $inactive->active = false;
        $valid = $this->location('0,0', 2, 1);

        $result = (new LocationsMapTransformer)->transformCollectionForMap(new Collection([$invalid, $inactive, $valid]));

        $this->assertSame('FeatureCollection', $result['type']);
        $this->assertCount(1, $result['features']);
        $this->assertSame([0.0, 0.0], $result['features'][0]['geometry']['coordinates']);
    }

    private function location(?string $coordinates, int $total, int $checked): Location
    {
        $location = new Location;
        $location->setRawAttributes([
            'id' => 1,
            'name' => 'Location',
            'address' => 'Address',
            'coordinates' => $coordinates,
            'active' => true,
            'object_code' => 843,
            'bitrix_id' => 12,
            'assets_count' => (string) $total,
            'checked_assets_count' => (string) $checked,
        ]);

        return $location;
    }
}
