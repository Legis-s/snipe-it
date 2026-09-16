<?php

namespace Tests\Feature\Map;

use App\Models\Asset;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Laravel\Passport\ApiTokenCookieFactory;
use Tests\TestCase;

class MapApiTest extends TestCase
{
    public function test_map_authenticates_browser_cookie_with_csrf_header(): void
    {
        $user = User::factory()->superuser()->create();
        $token = 'map-browser-csrf-token';
        $cookie = app(ApiTokenCookieFactory::class)->make($user->id, $token);

        $this->withCookie($cookie->getName(), $cookie->getValue())
            ->withCredentials()
            ->getJson(route('api.map.index'), ['X-CSRF-TOKEN' => $token])
            ->assertOk()
            ->assertJsonPath('type', 'FeatureCollection');
    }

    public function test_map_rejects_browser_cookie_without_csrf_header(): void
    {
        $user = User::factory()->superuser()->create();
        $cookie = app(ApiTokenCookieFactory::class)->make($user->id, 'map-browser-csrf-token');

        $this->withCookie($cookie->getName(), $cookie->getValue())
            ->withCredentials()
            ->getJson(route('api.map.index'))
            ->assertUnauthorized();
    }

    public function test_map_requires_location_view_permission(): void
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.map.index'))
            ->assertForbidden();
    }

    public function test_map_returns_counts_without_hydrating_assets(): void
    {
        $actor = User::factory()->superuser()->create();
        $location = Location::factory()->create([
            'active' => true,
            'object_code' => 843,
            'coordinates' => '55.76,37.64',
        ]);
        Asset::factory()->create(['location_id' => $location->id, 'last_audit_date' => now()]);
        Asset::factory()->create(['location_id' => $location->id, 'last_audit_date' => null]);
        Location::factory()->create(['active' => false, 'object_code' => 843, 'coordinates' => '55,37']);
        Location::factory()->create(['active' => true, 'object_code' => 455, 'coordinates' => '55,37']);
        Location::factory()->create(['active' => true, 'object_code' => 843, 'coordinates' => 'invalid']);
        $retrievedAssets = 0;
        Event::listen('eloquent.retrieved: '.Asset::class, function () use (&$retrievedAssets): void {
            $retrievedAssets++;
        });

        $this->actingAsForApi($actor)
            ->getJson(route('api.map.index'))
            ->assertOk()
            ->assertJsonPath('type', 'FeatureCollection')
            ->assertJsonCount(1, 'features')
            ->assertJsonPath('features.0.id', $location->id)
            ->assertJsonPath('features.0.assets_count', 2)
            ->assertJsonPath('features.0.checked_assets_count', 1);

        $this->assertSame(0, $retrievedAssets);
    }

    public function test_map_page_renders_filter_and_loading_state(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('map'))
            ->assertOk()
            ->assertSee('id="map-show-empty"', false)
            ->assertSee("'X-CSRF-TOKEN': $('meta[name=\"csrf-token\"]').attr('content')", false)
            ->assertSee('id="map-status"', false);
    }

    public function test_map_page_requires_permission(): void
    {
        $this->actingAs(User::factory()->create())->get(route('map'))->assertForbidden();
    }
}
