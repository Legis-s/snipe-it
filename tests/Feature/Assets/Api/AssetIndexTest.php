<?php

namespace Tests\Feature\Assets\Api;

use App\Models\Asset;
use App\Models\Company;
use App\Models\CustomField;
use App\Models\Deal;
use App\Models\InvoiceType;
use App\Models\LegalPerson;
use App\Models\Location;
use App\Models\Purchase;
use App\Models\Setting;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class AssetIndexTest extends TestCase
{
    public function test_custom_filters_include_archived_assets_without_changing_shared_settings(): void
    {
        $this->settings->set(['show_archived_in_list' => '0']);
        $purchase = new Purchase([
            'invoice_number' => 'Archive-filter-invoice', 'invoice_file' => 'invoice.pdf',
            'bitrix_id' => 123, 'final_price' => 20, 'comment' => 'Archive filter test',
            'supplier_id' => Supplier::factory()->create()->id,
            'legal_person_id' => LegalPerson::create(['name' => 'Archive filter company'])->id,
            'invoice_type_id' => InvoiceType::create(['name' => 'Archive filter type'])->id,
        ]);
        $this->assertTrue($purchase->save(), (string) $purchase->getErrors());
        $deal = Deal::create(['name' => 'Archive filter deal']);
        $archivedStatus = Statuslabel::factory()->archived()->create();
        $archived = Asset::factory()->create([
            'name' => 'Archived purchase asset',
            'status_id' => $archivedStatus->id,
            'purchase_id' => $purchase->id,
            'assigned_type' => Deal::class,
            'assigned_to' => $deal->id,
        ]);
        $unrelated = Asset::factory()->create(['name' => 'Unrelated archived asset', 'status_id' => $archivedStatus->id]);
        $ordinary = Asset::factory()->create(['name' => 'Ordinary asset']);
        $settings = Setting::getSettings();
        $original = $settings->getAttributes();
        $this->actingAsForApi(User::factory()->superuser()->create());

        foreach (['purchase_id' => $purchase->id, 'deal_id' => $deal->id] as $filter => $id) {
            $this->getJson(route('api.assets.index', [$filter => $id]))
                ->assertOk()
                ->assertJsonPath('total', 1)
                ->assertResponseContainsInRows($archived)
                ->assertResponseDoesNotContainInRows($unrelated);

            $this->assertSame($original, $settings->getAttributes());
            $this->assertSame($original, Setting::getSettings()->getAttributes());

            $this->getJson(route('api.assets.index'))
                ->assertOk()
                ->assertResponseContainsInRows($ordinary)
                ->assertResponseDoesNotContainInRows($archived)
                ->assertResponseDoesNotContainInRows($unrelated);
        }
    }

    public function test_asset_api_index_returns_expected_assets()
    {
        Asset::factory()->count(3)->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.assets.index', [
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
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    public function test_asset_api_index_returns_display_upcoming_audits_due()
    {
        Asset::factory()->count(3)->create(['next_audit_date' => Carbon::now()->format('Y-m-d')]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.assets.list-upcoming', ['action' => 'audits', 'upcoming_status' => 'due']))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    public function test_asset_api_index_returns_overdue_for_audit()
    {
        Asset::factory()->count(3)->create(['next_audit_date' => Carbon::now()->subDays(1)->format('Y-m-d')]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.assets.list-upcoming', ['action' => 'audits', 'upcoming_status' => 'overdue']))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    public function test_asset_api_index_returns_due_or_overdue_for_audit()
    {
        Asset::factory()->count(3)->create(['next_audit_date' => Carbon::now()->format('Y-m-d')]);
        Asset::factory()->count(2)->create(['next_audit_date' => Carbon::now()->subDays(1)->format('Y-m-d')]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.assets.list-upcoming', ['action' => 'audits', 'upcoming_status' => 'due-or-overdue']))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 5)->etc());
    }

    public function test_asset_api_index_returns_due_for_expected_checkin()
    {
        Asset::factory()->count(3)->create(['assigned_to' => '1', 'assigned_type' => User::class, 'expected_checkin' => Carbon::now()->format('Y-m-d')]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(
                route('api.assets.list-upcoming', ['action' => 'checkins', 'upcoming_status' => 'due'])
            )
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    public function test_asset_api_index_returns_overdue_for_expected_checkin()
    {
        Asset::factory()->count(3)->create(['assigned_to' => '1', 'assigned_type' => User::class, 'expected_checkin' => Carbon::now()->subDays(1)->format('Y-m-d')]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.list-upcoming', ['action' => 'checkins', 'upcoming_status' => 'overdue']))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    public function test_asset_api_index_returns_due_or_overdue_for_expected_checkin()
    {
        Asset::factory()->count(3)->create(['assigned_to' => '1', 'assigned_type' => User::class, 'expected_checkin' => Carbon::now()->subDays(1)->format('Y-m-d')]);
        Asset::factory()->count(2)->create(['assigned_to' => '1', 'assigned_type' => User::class, 'expected_checkin' => Carbon::now()->format('Y-m-d')]);

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.list-upcoming', ['action' => 'checkins', 'upcoming_status' => 'due-or-overdue']))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 5)->etc());
    }

    public function test_asset_api_index_adheres_to_company_scoping()
    {
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        $assetA = Asset::factory()->for($companyA)->create();
        $assetB = Asset::factory()->for($companyB)->create();

        $superUser = $companyA->users()->save(User::factory()->superuser()->make());
        $userInCompanyA = $companyA->users()->save(User::factory()->viewAssets()->make());
        $userInCompanyB = $companyB->users()->save(User::factory()->viewAssets()->make());

        $this->settings->disableMultipleFullCompanySupport();

        $this->actingAsForApi($superUser)
            ->getJson(route('api.assets.index'))
            ->assertResponseContainsInRows($assetA, 'asset_tag')
            ->assertResponseContainsInRows($assetB, 'asset_tag');

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.assets.index'))
            ->assertResponseContainsInRows($assetA, 'asset_tag')
            ->assertResponseContainsInRows($assetB, 'asset_tag');

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.assets.index'))
            ->assertResponseContainsInRows($assetA, 'asset_tag')
            ->assertResponseContainsInRows($assetB, 'asset_tag');

        $this->settings->enableMultipleFullCompanySupport();

        $this->actingAsForApi($superUser)
            ->getJson(route('api.assets.index'))
            ->assertResponseContainsInRows($assetA, 'asset_tag')
            ->assertResponseContainsInRows($assetB, 'asset_tag');

        $this->actingAsForApi($userInCompanyA)
            ->getJson(route('api.assets.index'))
            ->assertResponseContainsInRows($assetA, 'asset_tag')
            ->assertResponseDoesNotContainInRows($assetB, 'asset_tag');

        $this->actingAsForApi($userInCompanyB)
            ->getJson(route('api.assets.index'))
            ->assertResponseDoesNotContainInRows($assetA, 'asset_tag')
            ->assertResponseContainsInRows($assetB, 'asset_tag');
    }

    public function test_assets_can_be_filtered_by_custom_field()
    {
        $field = CustomField::factory()->create();

        $matchingAssets = Asset::factory()->count(3)->hasMultipleCustomFields([$field])->create();
        foreach ($matchingAssets as $asset) {
            $asset->{$field->db_column_name()} = 'target-value';
            $asset->save();
        }

        // These assets have a null value for the custom field column and should not be returned
        Asset::factory()->count(2)->create();

        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.index', [
                $field->db_column_name() => 'target-value',
            ]))
            ->assertOk()
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    public function test_gracefully_handles_malformed_filter()
    {
        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson(route('api.assets.index', [
                // filter should be a json encoded array and not a string
                'filter' => 'asset_tag:12345',
            ]))
            ->assertStatusMessageIs('error')
            ->assertJson(function (AssertableJson $json) {
                $json->has('messages.filter')->etc();
            });
    }

    public function test_returns_result_via_filter()
    {

        Asset::factory()->count(3)->create(['name' => 'MY AWESOME ASSET NAME']);
        $this->actingAsForApi(User::factory()->viewAssets()->create())
            ->getJson(route('api.assets.index', [
                'filter' => '{"name":"MY AWESOME ASSET NAME"}',
            ]))
            ->assertOk()
            ->assertJsonStructure([
                'total',
                'rows',
            ])
            ->assertJson(fn (AssertableJson $json) => $json->has('rows', 3)->etc());
    }

    /**
     * Regression for a missing `break` in the API asset sort switch that
     * silently fell through from `case 'location'` into `case 'rtd_location'`,
     * applying both OrderLocation and OrderRtdLocation to the query so the
     * caller got a sort keyed on rtd_location first and location second when
     * they asked for location only. Verify sort=location alone controls the
     * result ordering.
     */
    public function test_sort_by_location_does_not_fall_through_to_rtd_location(): void
    {
        $zebraLocation = Location::factory()->create(['name' => 'Zebra Warehouse']);
        $aardvarkLocation = Location::factory()->create(['name' => 'Aardvark Warehouse']);

        // Cross the pairing between location and rtd_location so a fallthrough
        // that applied OrderRtdLocation would put the assets in a different
        // order than a clean OrderLocation.
        $assetLocatedAtAardvark = Asset::factory()->create([
            'location_id' => $aardvarkLocation->id,
            'rtd_location_id' => $zebraLocation->id,
        ]);
        $assetLocatedAtZebra = Asset::factory()->create([
            'location_id' => $zebraLocation->id,
            'rtd_location_id' => $aardvarkLocation->id,
        ]);

        $response = $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.assets.index', [
                'sort' => 'location',
                'order' => 'asc',
                'limit' => 20,
            ]))
            ->assertOk()
            ->json('rows');

        $this->assertSame(
            [$assetLocatedAtAardvark->id, $assetLocatedAtZebra->id],
            [$response[0]['id'], $response[1]['id']],
        );
    }
}
