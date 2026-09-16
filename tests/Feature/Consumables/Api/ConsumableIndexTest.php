<?php

namespace Tests\Feature\Consumables\Api;

use App\Models\Category;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\Location;
use App\Models\Manufacturer;
use App\Models\Supplier;
use App\Models\User;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ConsumableIndexTest extends TestCase
{
    public static function stock_sort_orders(): array
    {
        return [
            ['remaining', 'asc'],
            ['remaining', 'desc'],
            ['percent_remaining', 'asc'],
            ['percent_remaining', 'desc'],
        ];
    }

    #[DataProvider('stock_sort_orders')]
    public function test_stock_sorting_counts_legacy_and_custom_checkouts(string $sort, string $order): void
    {
        $user = User::factory()->superuser()->create();
        $empty = Consumable::factory()->create(['qty' => 0]);
        $mixed = Consumable::factory()->create(['qty' => 10]);
        $untouched = Consumable::factory()->create(['qty' => 4]);
        $large = Consumable::factory()->create(['qty' => 100]);
        $this->assertTrue((new ConsumableAssignment([
            'consumable_id' => $large->id,
            'type' => ConsumableAssignment::ISSUED,
            'quantity' => 95,
        ]))->save());
        $mixed->users()->attach($user->id, ['created_by' => $user->id]);

        foreach ([ConsumableAssignment::ISSUED => 3, ConsumableAssignment::SOLD => 4, ConsumableAssignment::MANUALLY => 100] as $type => $quantity) {
            $assignment = new ConsumableAssignment([
                'consumable_id' => $mixed->id,
                'type' => $type,
                'quantity' => $quantity,
            ]);
            $this->assertTrue($assignment->save());
        }

        $expected = $sort === 'remaining'
            ? [$empty->id, $mixed->id, $untouched->id, $large->id]
            : [$empty->id, $large->id, $mixed->id, $untouched->id];
        if ($order === 'desc') {
            $expected = array_reverse($expected);
        }

        $response = $this->actingAsForApi($user)
            ->getJson(route('api.consumables.index', ['sort' => $sort, 'order' => $order]))
            ->assertOk()
            ->assertJsonPath('total', 4);

        $this->assertSame($expected, array_column($response->json('rows'), 'id'));
        $rows = collect($response->json('rows'))->keyBy('id');
        $this->assertEquals(2, $rows[$mixed->id]['remaining']);
        $this->assertEquals(0, $rows[$empty->id]['remaining']);
        $this->assertEquals(4, $rows[$untouched->id]['remaining']);
        $this->assertEquals(5, $rows[$large->id]['remaining']);
        $this->assertEquals(20, $rows[$mixed->id]['percent_remaining']);
        $this->assertEquals(5, $rows[$large->id]['percent_remaining']);
    }

    public function test_location_tab_returns_consumables_with_default_supplier(): void
    {
        $location = Location::factory()->create();
        $supplier = Supplier::factory()->create();
        $consumable = Consumable::factory()->create([
            'location_id' => $location->id,
            'default_supplier_id' => $supplier->id,
        ]);
        Consumable::factory()->create(['location_id' => Location::factory()->create()->id]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.consumables.index', ['location_id' => $location->id]))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('rows.0.id', $consumable->id)
            ->assertJsonPath('rows.0.supplier.id', $supplier->id);
    }

    public function test_consumable_index_adheres_to_company_scoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $consumableA = Consumable::factory()->for($companyA)->create();
        $consumableB = Consumable::factory()->for($companyB)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->viewConsumables()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->viewConsumables()->make());

        $this->settings->disableMultipleFullCompanySupport();

        $this->actingAsForApi($superUser)
            ->getJson(route('api.consumables.index'))
            ->assertResponseContainsInRows($consumableA)
            ->assertResponseContainsInRows($consumableB);

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.consumables.index'))
            ->assertResponseContainsInRows($consumableA)
            ->assertResponseContainsInRows($consumableB);

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.consumables.index'))
            ->assertResponseContainsInRows($consumableA)
            ->assertResponseContainsInRows($consumableB);

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAsForApi($superUser)
            ->getJson(route('api.consumables.index'))
            ->assertResponseContainsInRows($consumableA)
            ->assertResponseContainsInRows($consumableB);

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.consumables.index'))
            ->assertResponseContainsInRows($consumableA)
            ->assertResponseDoesNotContainInRows($consumableB);

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.consumables.index'))
            ->assertResponseDoesNotContainInRows($consumableA)
            ->assertResponseContainsInRows($consumableB);
    }

    public function test_consumable_index_returns_expected_search_results()
    {
        Consumable::factory()->count(10)->create();
        Consumable::factory()->count(1)->create(['name' => 'My Test Consumable']);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.consumables.index', [
                    'search' => 'My Test Consumable',
                    'sort' => 'name',
                    'order' => 'asc',
                    'offset' => '0',
                    'limit' => '20',
                ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson([
                'total' => 1,
            ]);

    }

    public function test_consumable_index_filters_all_supported_exact_fields()
    {
        $user = User::factory()->superuser()->create();

        $targetCompany = Company::factory()->create();
        $otherCompany = Company::factory()->create();
        $targetCategory = Category::factory()->create();
        $otherCategory = Category::factory()->create();
        $targetManufacturer = Manufacturer::factory()->create();
        $otherManufacturer = Manufacturer::factory()->create();
        $targetSupplier = Supplier::factory()->create();
        $otherSupplier = Supplier::factory()->create();
        $targetLocation = Location::factory()->create();
        $otherLocation = Location::factory()->create();

        $targetConsumable = Consumable::factory()->create([
            'name' => 'Target Consumable',
            'company_id' => $targetCompany->id,
            'category_id' => $targetCategory->id,
            'model_number' => 'CONS-MODEL-A',
            'manufacturer_id' => $targetManufacturer->id,
            'default_supplier_id' => $targetSupplier->id,
            'location_id' => $targetLocation->id,
            'notes' => 'CONS-NOTES-A',
        ]);

        $otherConsumable = Consumable::factory()->create([
            'name' => 'Other Consumable',
            'company_id' => $otherCompany->id,
            'category_id' => $otherCategory->id,
            'model_number' => 'CONS-MODEL-B',
            'manufacturer_id' => $otherManufacturer->id,
            'default_supplier_id' => $otherSupplier->id,
            'location_id' => $otherLocation->id,
            'notes' => 'CONS-NOTES-B',
        ]);

        // order_number was dropped from the filters list when the parent
        // column was renamed to legacy_order_number and current order
        // numbers moved to the QuantityAdjust action_log per event.
        $filters = [
            'name' => 'Target Consumable',
            'company_id' => $targetCompany->id,
            'category_id' => $targetCategory->id,
            'model_number' => 'CONS-MODEL-A',
            'manufacturer_id' => $targetManufacturer->id,
            'supplier_id' => $targetSupplier->id,
            'location_id' => $targetLocation->id,
            'notes' => 'CONS-NOTES-A',
        ];

        foreach ($filters as $filterKey => $filterValue) {
            $this->actingAsForApi($user)
                ->getJson(route('api.consumables.index', [$filterKey => $filterValue]))
                ->assertOk()
                ->assertResponseContainsInRows($targetConsumable)
                ->assertResponseDoesNotContainInRows($otherConsumable);
        }
    }
}
