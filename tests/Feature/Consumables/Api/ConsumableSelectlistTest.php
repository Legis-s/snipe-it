<?php

namespace Tests\Feature\Consumables\Api;

use App\Models\Consumable;
use App\Models\User;
use Tests\TestCase;

class ConsumableSelectlistTest extends TestCase
{
    public function test_standard_list_includes_escaped_name_and_remaining_quantity(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());
        $item = Consumable::factory()->create(['name' => 'Paper <A>', 'qty' => 5]);
        $item->checkOut(User::factory()->create(), 2);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.consumables.selectlist'))
            ->assertOk()
            ->assertJsonPath('results.0.id', $item->id)
            ->assertJsonPath('results.0.text', '[3] Paper &lt;A&gt;')
            ->assertJsonPath('results.0.tag_color', null)
            ->assertJsonMissingPath('results.0.numRemaining')
            ->assertJsonMissingPath('results.0.disabled')
            ->assertJsonPath('total_count', 1)
            ->assertJsonPath('pagination.more', false);
    }

    public function test_availability_list_disables_exhausted_items(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());
        $available = Consumable::factory()->create(['name' => 'A available', 'qty' => 5]);
        $empty = Consumable::factory()->create(['name' => 'B exhausted', 'qty' => 1]);
        $empty->checkOut(User::factory()->create(), 1);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.consumables.selectlist', ['assetStatusType' => 'notnull']))
            ->assertOk()
            ->assertJsonPath('results.0.id', $available->id)
            ->assertJsonPath('results.0.numRemaining', 5)
            ->assertJsonMissingPath('results.0.disabled')
            ->assertJsonMissingPath('results.0.tag_color')
            ->assertJsonPath('results.1.id', $empty->id)
            ->assertJsonPath('results.1.text', '[0] B exhausted')
            ->assertJsonPath('results.1.numRemaining', 0)
            ->assertJsonPath('results.1.disabled', true)
            ->assertJsonPath('total_count', 2);
    }

    public function test_search_and_empty_pagination_are_preserved(): void
    {
        Consumable::factory()->create(['name' => 'Paper', 'qty' => 5]);
        Consumable::factory()->create(['name' => 'Ink', 'qty' => 2]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.consumables.selectlist', ['search' => 'Paper', 'page' => 2, 'assetStatusType' => 'notnull']))
            ->assertOk()
            ->assertJsonPath('results', [])
            ->assertJsonPath('total_count', 1)
            ->assertJsonPath('page', 2)
            ->assertJsonPath('page_count', 1)
            ->assertJsonPath('pagination.per_page', 50)
            ->assertJsonPath('pagination.more', false);
    }
}
