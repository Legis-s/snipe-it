<?php

namespace Tests\Feature\Customizations;

use App\Models\InvoiceType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use Tests\TestCase;

class CustomWebRoutesTest extends TestCase
{
    public function test_bulk_asset_checkout_uses_the_standard_controller(): void
    {
        foreach (['GET' => 'showCheckout', 'POST' => 'storeCheckout'] as $method => $handler) {
            $route = Route::getRoutes()->match(Request::create('/hardware/bulkcheckout', $method));
            $this->assertSame(\App\Http\Controllers\Assets\BulkAssetsController::class.'@'.$handler, $route->getActionName());
            $this->assertContains('auth', $route->gatherMiddleware());
        }

        $handlers = collect(Route::getRoutes()->getRoutes())->map(fn ($route): string => $route->getActionName());
        $this->assertNotContains('App\\Http\\Controllers\\Api\\BulkAssetsController@bulkCheckout', $handlers);
    }

    private function custom_routes(): RouteCollection
    {
        $original = Route::getRoutes();
        Route::setRoutes(new RouteCollection);
        try {
            Route::middleware('web')->group(base_path('routes/web/custom.php'));

            return Route::getRoutes();
        } finally {
            Route::setRoutes($original);
        }
    }

    public function test_custom_web_routes_have_public_existing_handlers(): void
    {
        $missing = [];
        foreach ($this->custom_routes() as $route) {
            [$controller, $method] = explode('@', $route->getActionName());
            if (! method_exists($controller, $method) || ! (new ReflectionMethod($controller, $method))->isPublic()) {
                $missing[] = $route->uri().' -> '.$route->getActionName();
            }
        }
        $this->assertSame([], $missing);
    }

    public function test_custom_web_routes_keep_their_registered_handlers_and_names(): void
    {
        foreach ($this->custom_routes() as $route) {
            $url = preg_replace('/\{[^}]+\}/', '999', $route->uri());
            foreach ($route->methods() as $method) {
                $registered = Route::getRoutes()->match(Request::create('/'.$url, $method));
                $this->assertSame($route->getActionName(), $registered->getActionName(), $method.' '.$url);
                $this->assertSame($route->getName(), $registered->getName());
                $this->assertContains('web', $registered->gatherMiddleware());
            }
        }
    }

    public static function unsupported_writes(): array
    {
        return [
            ['POST', 'contracts'], ['PATCH', 'contracts/999'], ['DELETE', 'contracts/999'],
            ['POST', 'deals'], ['PATCH', 'deals/999'], ['DELETE', 'deals/999'],
            ['POST', 'devices'], ['PATCH', 'devices/999'], ['DELETE', 'devices/999'],
            ['POST', 'inventories'], ['PATCH', 'inventories/999'],
            ['POST', 'invoicetypes'], ['DELETE', 'invoicetypes/999'],
            ['PATCH', 'purchases/999'],
        ];
    }

    #[DataProvider('unsupported_writes')]
    public function test_unsupported_writes_are_not_dispatched(string $method, string $path): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->json($method, '/'.$path)->assertStatus(405);
    }

    public function test_existing_invoice_type_edit_form_still_renders(): void
    {
        $type = InvoiceType::create(['name' => 'Existing invoice type', 'active' => 1, 'bitrix_id' => 123]);
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('invoicetypes.edit', $type))->assertOk()
            ->assertSee(route('invoicetypes.update', $type), false)
            ->assertSee('type="hidden" name="active" value="0"', false)
            ->assertSee('value="123" class="form-control" disabled', false);
    }

    public function test_invoice_type_activity_can_be_disabled_and_reenabled_without_changing_bitrix_id(): void
    {
        $type = InvoiceType::create(['name' => 'Activity test', 'active' => 1, 'bitrix_id' => 123]);
        $this->actingAs(User::factory()->superuser()->create());
        foreach ([0, 1] as $active) {
            $this->patch(route('invoicetypes.update', $type), ['active' => $active, 'bitrix_id' => 456])
                ->assertRedirect(route('invoicetypes.index'))->assertSessionHasNoErrors();
            $this->assertEquals($active, $type->fresh()->active);
            $this->assertEquals(123, $type->fresh()->bitrix_id);
        }
    }

    public function test_invoice_type_edit_requires_permission(): void
    {
        $type = InvoiceType::create(['name' => 'Protected invoice type']);
        $this->actingAs(User::factory()->create())
            ->get(route('invoicetypes.edit', $type))->assertForbidden();
    }
}
