<?php

namespace Tests\Feature\Depreciations\Ui;

use App\Models\Depreciation;
use App\Models\User;
use Tests\TestCase;

class DepreciationsIndexTest extends TestCase
{
    public function test_model_form_uses_standard_depreciation_options(): void
    {
        $depreciation = Depreciation::factory()->create(['name' => 'Standard depreciation option']);

        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('models.create'))
            ->assertOk()
            ->assertViewHas('depreciation_list', fn (array $options): bool => ($options[$depreciation->id] ?? null) === $depreciation->name)
            ->assertSee('Standard depreciation option');
    }

    public function test_permission_required_to_view_depreciations_list()
    {
        $this->actingAs(User::factory()->create())
            ->get(route('depreciations.index'))
            ->assertForbidden();
    }

    public function test_user_can_list_depreciations()
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('depreciations.index'))
            ->assertOk();
    }
}
