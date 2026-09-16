<?php

namespace Tests\Feature\Devices\Ui;

use App\Models\Asset;
use App\Models\Device;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DeviceViewTest extends TestCase
{
    public static function device_states(): array
    {
        return [[false, '47.0, 28.8', true], [true, '', false], [true, '0,0', false], [true, 'invalid', false], [true, '47.0, 28.8', true]];
    }

    #[DataProvider('device_states')]
    public function test_page_renders_summary_and_preserves_asset_details(bool $linked, string $coords, bool $map): void
    {
        $asset = $linked ? Asset::factory()->create() : null;
        $sim = $linked ? Asset::factory()->create() : null;
        $device = Device::create([
            'number' => '2048', 'model' => 'Test tablet', 'statusCode' => 'green',
            'asset_id' => $asset?->id, 'asset_sim_id' => $sim?->id,
            'coordinates' => $coords, 'batteryLevel' => 75, 'androidVersion' => '13',
            'lastUpdate' => now(),
        ]);
        $response = $this->actingAs(User::factory()->superuser()->create())
            ->get(route('devices.show', $device))->assertOk()
            ->assertSee('Test tablet')->assertSee('75%')
            ->assertSee('class="device-overview"', false)
            ->assertSee('href="#details" data-toggle="tab"', false)
            ->assertSee('id="details"', false)
            ->assertSee('.device-detail .box-body .row::before,', false)
            ->assertSee('.device-detail .box-body .row::after,', false)
            ->assertSee('.device-asset-details .box-body::before,', false)
            ->assertSee('.device-asset-details .box-body::after { content: none; display: none; }', false)
            ->assertDontSee('<details', false);
        if ($map) {
            $response->assertSee('id="map"', false)->assertSee('ymaps.ready(init)', false);
        } else {
            $response->assertDontSee('id="map"', false)->assertDontSee('ymaps.ready(init)', false);
        }
        if ($linked) {
            $response->assertSee($asset->asset_tag)->assertSee($sim->asset_tag)
                ->assertSee('class="device-detail device-asset-details"', false)
                ->assertSee('href="#asset" data-toggle="tab"', false)
                ->assertSee('href="#sim" data-toggle="tab"', false)
                ->assertSee('id="asset"', false)
                ->assertSee('id="sim"', false);
        } else {
            $response->assertDontSee('href="#asset"', false)
                ->assertDontSee('href="#sim"', false)
                ->assertDontSee('id="asset"', false)
                ->assertDontSee('id="sim"', false);
        }
    }

    public function test_page_requires_authentication(): void
    {
        $device = Device::create(['number' => '2048']);
        $this->get(route('devices.show', $device))->assertRedirect();
    }
}
