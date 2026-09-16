<?php

namespace Tests\Feature\Depreciations\Api;

use App\Models\Depreciation;
use App\Models\User;
use Tests\TestCase;

class DepreciationsIndexTest extends TestCase
{
    public function test_standard_index_preserves_search_and_pagination(): void
    {
        Depreciation::factory()->create(['name' => 'Scheme A']);
        $second = Depreciation::factory()->create(['name' => 'Scheme B']);
        Depreciation::factory()->create(['name' => 'Unrelated']);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.depreciations.index', [
                'search' => 'Scheme', 'sort' => 'name', 'order' => 'asc', 'offset' => 1, 'limit' => 1,
            ]))
            ->assertOk()
            ->assertJsonPath('total', 2)
            ->assertJsonCount(1, 'rows')
            ->assertJsonPath('rows.0.id', $second->id)
            ->assertJsonPath('rows.0.name', 'Scheme B');
    }

    public function test_viewing_depreciation_index_requires_permission()
    {
        $this->actingAsForApi(User::factory()->create())
            ->getJson(route('api.depreciations.index'))
            ->assertForbidden();
    }
}
