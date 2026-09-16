<?php

namespace Tests\Feature\Account;

use App\Http\Controllers\ViewAssetsController;
use App\Models\Consumable;
use App\Models\User;
use Illuminate\Http\Request;
use Tests\TestCase;

class ViewAssetsTest extends TestCase
{
    public function test_requires_authentication(): void
    {
        User::factory()->create();

        $this->get(route('view-assets'))->assertRedirect(route('login'));
    }

    public function test_consumables_are_loaded_before_rendering_the_view(): void
    {
        $user = User::factory()->create();
        $consumable = Consumable::factory()->create();
        $user->consumables()->attach($consumable->id, ['created_by' => $user->id]);
        $this->actingAs($user);

        $view = app(ViewAssetsController::class)->getIndex(Request::create(route('view-assets')));
        $viewUser = $view->getData()['user'];

        $this->assertTrue($viewUser->relationLoaded('consumables'));
        $this->assertSame([$consumable->id], $viewUser->consumables->modelKeys());
    }

    public function test_page_renders_only_the_current_users_consumables(): void
    {
        $this->settings->set(['manager_view_enabled' => 0]);
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $consumable = Consumable::factory()->create(['name' => 'Personal consumable']);
        $otherConsumable = Consumable::factory()->create(['name' => 'Other employee consumable']);
        $user->consumables()->attach($consumable->id, ['created_by' => $user->id, 'note' => 'Personal checkout note']);
        $otherUser->consumables()->attach($otherConsumable->id, ['created_by' => $otherUser->id]);

        $this->actingAs($user)
            ->get(route('view-assets', ['user_id' => $otherUser->id]))
            ->assertOk()
            ->assertSee('Personal consumable')
            ->assertSee('Personal checkout note')
            ->assertDontSee('Other employee consumable');
    }
}
