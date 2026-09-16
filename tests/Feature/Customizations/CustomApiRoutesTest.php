<?php

namespace Tests\Feature\Customizations;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\RouteCollection;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use Tests\TestCase;

class CustomApiRoutesTest extends TestCase
{
    private function custom_routes(): RouteCollection
    {
        $router = Route::getFacadeRoot();
        $isolated = new Router($this->app['events'], $this->app);
        Route::swap($isolated);
        try {
            Route::group(['prefix' => 'api', 'middleware' => 'auth:api'], function (): void {
                require base_path('routes/api_custom.php');
            });

            return $isolated->getRoutes();
        } finally {
            Route::swap($router);
        }
    }

    public function test_custom_routes_have_public_existing_handlers(): void
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

    public function test_custom_routes_resolve_to_their_authenticated_handlers(): void
    {
        foreach ($this->custom_routes() as $route) {
            $url = preg_replace('/\{[^}]+\}/', '999', $route->uri());
            foreach ($route->methods() as $method) {
                $registered = Route::getRoutes()->match(Request::create('/'.$url, $method));
                $this->assertSame($route->getActionName(), $registered->getActionName(), $method.' '.$url);
                $this->assertSame($route->getName(), $registered->getName());
                $this->assertContains('auth:api', $registered->gatherMiddleware());
            }
        }
    }

    public static function unsupported_writes(): array
    {
        return [
            ['POST', 'map'], ['PATCH', 'map/999'], ['DELETE', 'map/999'],
            ['POST', 'devices'], ['PATCH', 'devices/999'], ['DELETE', 'devices/999'],
            ['POST', 'purchases'], ['PATCH', 'purchases/999'], ['DELETE', 'purchases/999'],
            ['POST', 'contracts'], ['PATCH', 'contracts/999'], ['DELETE', 'contracts/999'],
            ['POST', 'deals'], ['PATCH', 'deals/999'], ['DELETE', 'deals/999'],
            ['POST', 'invoice_types'], ['PATCH', 'invoice_types/999'], ['DELETE', 'invoice_types/999'],
            ['POST', 'inventorystatuslabels'], ['PATCH', 'inventorystatuslabels/999'], ['DELETE', 'inventorystatuslabels/999'],
            ['POST', 'inventory_items'], ['DELETE', 'inventory_items/999'], ['DELETE', 'inventories/999'],
            ['POST', 'consumableassignments'], ['PATCH', 'consumableassignments/999'], ['DELETE', 'consumableassignments/999'],
            ['POST', 'hardware/999/closesell'], ['POST', 'consumableassignments/999/close_documents'],
        ];
    }

    public function test_custom_mutations_do_not_accept_get_requests(): void
    {
        foreach ($this->custom_routes() as $route) {
            if (! in_array($route->getActionMethod(), ['index', 'show', 'selectlist'], true)) {
                $this->assertNotContains('GET', $route->methods(), $route->uri());
                $this->assertNotContains('HEAD', $route->methods(), $route->uri());
            }
        }
    }

    #[DataProvider('unsupported_writes')]
    public function test_unsupported_writes_are_not_dispatched(string $method, string $path): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->json($method, '/api/v1/'.$path)->assertStatus(405);
    }
}
