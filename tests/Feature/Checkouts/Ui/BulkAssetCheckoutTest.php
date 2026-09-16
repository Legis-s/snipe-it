<?php

namespace Tests\Feature\Checkouts\Ui;

use App\Mail\BulkAssetCheckoutMail;
use App\Mail\CheckoutAssetMail;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Deal;
use App\Models\InvoiceType;
use App\Models\LegalPerson;
use App\Models\Location;
use App\Models\Purchase;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\TestCase;

class BulkAssetCheckoutTest extends TestCase
{
    private function create_purchase(): Purchase
    {
        $purchase = new Purchase([
            'invoice_number' => fake()->unique()->numerify('INV-########'), 'invoice_file' => 'invoice.pdf',
            'bitrix_id' => fake()->unique()->numberBetween(1, 1000000), 'final_price' => 20, 'comment' => 'Bulk checkout test',
            'supplier_id' => Supplier::factory()->create()->id,
            'legal_person_id' => LegalPerson::create(['name' => fake()->unique()->company()])->id,
            'invoice_type_id' => InvoiceType::create(['name' => fake()->unique()->uuid()])->id,
        ]);
        $this->assertTrue($purchase->save(), (string) $purchase->getErrors());

        return $purchase;
    }

    public function test_purchase_preselects_only_available_assets(): void
    {
        $purchase = $this->create_purchase();
        $available = Asset::factory()->count(2)->create(['purchase_id' => $purchase->id]);
        Asset::factory()->assignedToUser()->create(['purchase_id' => $purchase->id]);
        Asset::factory()->create([
            'purchase_id' => $purchase->id,
            'status_id' => Statuslabel::factory()->archived()->create()->id,
        ]);
        Asset::factory()->create(['purchase_id' => $purchase->id])->delete();
        Asset::factory()->create(['purchase_id' => $this->create_purchase()->id]);

        $response = $this->actingAs(User::factory()->checkoutAssets()->create())
            ->get(route('hardware.bulkcheckout.show', ['purchase_id' => $purchase->id]))
            ->assertOk()
            ->assertSessionHas('_old_input.selected_assets', $available->pluck('id')->all());

        foreach ($available as $asset) {
            $response->assertSee('value="'.$asset->id.'" selected="selected"', false);
        }
    }

    public function test_purchase_preselection_respects_company_scope(): void
    {
        $purchase = $this->create_purchase();
        $this->settings->enableMultipleFullCompanySupport();
        $company = Company::factory()->create();
        $available = Asset::factory()->for($company)->create(['purchase_id' => $purchase->id]);
        Asset::factory()->for(Company::factory()->create())->create(['purchase_id' => $purchase->id]);

        $this->actingAs(User::factory()->forCompany($company)->checkoutAssets()->create())
            ->get(route('hardware.bulkcheckout.show', ['purchase_id' => $purchase->id]))
            ->assertOk()
            ->assertSessionHas('_old_input.selected_assets', [$available->id]);
    }

    public function test_purchase_does_not_replace_previous_asset_selection(): void
    {
        $purchase = $this->create_purchase();
        $selected = Asset::factory()->create();
        Asset::factory()->create(['purchase_id' => $purchase->id]);

        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->withSession(['_old_input' => ['selected_assets' => [$selected->id]]])
            ->get(route('hardware.bulkcheckout.show', ['purchase_id' => $purchase->id]))
            ->assertOk()
            ->assertSessionHas('_old_input.selected_assets', [$selected->id]);
    }

    public function test_purchase_preselection_requires_checkout_permission(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('hardware.bulkcheckout.show', ['purchase_id' => 123]))
            ->assertForbidden();
    }

    public function test_requires_permission()
    {
        // The target user must actually exist and be undeleted, or the
        // FormRequest's `exists_undeleted:users,id` rule on `assigned_user`
        // trips first and returns 422 before the controller's permission
        // check ever runs. Rule was added in 8acedc241f (FD-56263).
        $target = User::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => [1],
                'checkout_to_type' => 'user',
                'assigned_user' => $target->id,
                'assigned_asset' => null,
                'checkout_at' => null,
                'expected_checkin' => null,
                'note' => null,
            ])
            ->assertForbidden();
    }

    public function test_can_bulk_checkout_assets()
    {
        Mail::fake();

        $assets = Asset::factory()->requiresAcceptance()->count(2)->create();
        $user = User::factory()->create(['email' => 'someone@example.com']);

        $checkoutAt = now()->subWeek()->format('Y-m-d');
        $expectedCheckin = now()->addWeek()->format('Y-m-d');

        $this->actingAs(User::factory()->checkoutAssets()->viewAssets()->create())
            ->followingRedirects()
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => $assets->pluck('id')->toArray(),
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
                'assigned_asset' => null,
                'checkout_at' => $checkoutAt,
                'expected_checkin' => $expectedCheckin,
                'note' => null,
            ])
            ->assertOk();

        $assets = $assets->fresh();

        $assets->each(function ($asset) use ($expectedCheckin, $checkoutAt, $user) {
            $asset->assignedTo()->is($user);
            $asset->last_checkout = $checkoutAt;
            $asset->expected_checkin = $expectedCheckin;
            $this->assertHasTheseActionLogs($asset, ['create', 'checkout']); // Note: '$this' gets auto-bound in closures, so this does work.
            $this->assertDatabaseHas('checkout_acceptances', [
                'checkoutable_type' => Asset::class,
                'checkoutable_id' => $asset->id,
                'assigned_to_id' => $user->id,
                'qty' => 1,
            ]);
        });

        Mail::assertNotSent(CheckoutAssetMail::class);
        Mail::assertSent(BulkAssetCheckoutMail::class, function (BulkAssetCheckoutMail $mail) {
            return $mail->hasTo('someone@example.com');
        });
    }

    public static function dealOperations(): array
    {
        return [
            'sale' => [false, 'Продано', 'sell'],
            'rent' => [true, 'В аренде', 'rented'],
        ];
    }

    #[DataProvider('dealOperations')]
    public function test_bulk_deal_checkout_creates_only_its_action_logs(bool $rent, string $statusName, string $action): void
    {
        $status = Statuslabel::factory()->create(['name' => $statusName]);
        $assets = Asset::factory()->count(2)->create();
        $deal = Deal::create(['name' => 'Test deal']);

        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => $assets->pluck('id')->all(),
                'checkout_to_type' => 'deal',
                'assigned_deal' => $deal->id,
                'rent' => $rent,
            ])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('success');

        $assets->each(function (Asset $asset) use ($deal, $status, $action) {
            $asset->refresh();

            $this->assertTrue($asset->assignedTo()->is($deal));
            $this->assertEquals($status->id, $asset->status_id);
            $this->assertSame(1, (int) $asset->checkout_counter);
            $this->assertNull($asset->location_id);
            $this->assertNull($asset->rtd_location_id);
            $this->assertSame(
                [$action],
                $asset->assetlog()
                    ->whereIn('action_type', ['checkout', 'update', 'sell', 'rented'])
                    ->reorder('id')
                    ->pluck('action_type')
                    ->all()
            );
        });
    }

    public function test_handle_missing_model_being_included()
    {
        Mail::fake();

        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => [
                    Asset::factory()->requiresAcceptance()->create()->id,
                    9999999,
                ],
                'checkout_to_type' => 'user',
                'assigned_user' => User::factory()->create(['email' => 'someone@example.com'])->id,
                'assigned_asset' => null,
                'checkout_at' => null,
                'expected_checkin' => null,
                'note' => null,
            ])
            ->assertSessionHas('error', trans_choice('admin/hardware/message.multi-checkout.error', 2));

        try {
            Mail::assertNotSent(CheckoutAssetMail::class);
        } catch (ExpectationFailedException $e) {
            $this->fail('Asset checkout email was sent when the entire checkout failed.');
        }
    }

    public function test_bulk_checkout_sets_requestable_to_false_when_checkbox_unchecked()
    {
        $assets = Asset::factory()->count(2)->create(['requestable' => 1]);
        $targetUser = User::factory()->create();

        // Bulk checkout now mirrors the per-item checkout: `requestable`
        // is a plain checkbox and its posted value (or absence) is
        // authoritative. Omitting the field means unchecked, so every
        // selected asset ends up requestable=false regardless of its
        // prior state.
        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => $assets->pluck('id')->toArray(),
                'checkout_to_type' => 'user',
                'assigned_user' => $targetUser->id,
            ]);

        $assets->each(function (Asset $asset) {
            $this->assertFalse((bool) $asset->fresh()->requestable);
        });
    }

    public function test_bulk_checkout_sets_requestable_to_true_when_checkbox_checked()
    {
        $requestableAsset = Asset::factory()->create(['requestable' => 1]);
        $nonRequestableAsset = Asset::factory()->create(['requestable' => 0]);
        $targetUser = User::factory()->create();

        $this->actingAs(User::factory()->checkoutAssets()->create())
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => [$requestableAsset->id, $nonRequestableAsset->id],
                'checkout_to_type' => 'user',
                'assigned_user' => $targetUser->id,
                'requestable' => '1',
            ]);

        $this->assertTrue((bool) $requestableAsset->fresh()->requestable);
        $this->assertTrue((bool) $nonRequestableAsset->fresh()->requestable);
    }

    public static function checkoutTargets()
    {
        yield 'Checkout to user' => [
            function () {
                return [
                    'type' => 'user',
                    'target' => User::factory()->create(),
                ];
            },
        ];

        yield 'Checkout to asset' => [
            function () {
                return [
                    'type' => 'asset',
                    'target' => Asset::factory()->create(),
                ];
            },
        ];

        yield 'Checkout to location' => [
            function () {
                return [
                    'type' => 'location',
                    'target' => Location::factory()->create(),
                ];
            },
        ];
    }

    #[DataProvider('checkoutTargets')]
    public function test_adheres_to_full_multiple_company_support($data)
    {
        ['type' => $type, 'target' => $target] = $data();

        $this->settings->enableMultipleFullCompanySupport();

        // create two companies
        [$companyA, $companyB] = Company::factory()->count(2)->create();

        // create an asset for each company
        $assetForCompanyA = Asset::factory()->for($companyA)->create();
        $assetForCompanyB = Asset::factory()->for($companyB)->create();

        $this->assertNull($assetForCompanyA->assigned_to, 'Asset should not be assigned before attempting this test case.');
        $this->assertNull($assetForCompanyB->assigned_to, 'Asset should not be assigned before attempting this test case.');

        // attempt to bulk checkout both items to the target
        $response = $this->actingAs(User::factory()->superuser()->create())
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => [
                    $assetForCompanyA->id,
                    $assetForCompanyB->id,
                ],
                'checkout_to_type' => $type,
                "assigned_$type" => $target->id,
            ]);

        // ensure bulk checkout is blocked
        $this->assertNull($assetForCompanyA->fresh()->assigned_to, 'Asset was checked out across companies.');
        $this->assertNull($assetForCompanyB->fresh()->assigned_to, 'Asset was checked out across companies.');

        // ensure redirected back
        $response->assertRedirectToRoute('hardware.bulkcheckout.show');
    }

    #[DataProvider('checkoutTargets')]
    public function test_prevents_checkouts_of_checked_out_items($data)
    {
        ['type' => $type, 'target' => $target] = $data();

        $asset = Asset::factory()->create();
        $checkedOutAsset = Asset::factory()->assignedToUser()->create();
        $existingUserId = $checkedOutAsset->assigned_to;

        $response = $this->actingAs(User::factory()->superuser()->create())
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => [
                    $asset->id,
                    $checkedOutAsset->id,
                ],
                'checkout_to_type' => $type,
                "assigned_$type" => $target->id,
            ]);

        $this->assertEquals(
            $existingUserId,
            $checkedOutAsset->fresh()->assigned_to,
            'Asset was checked out when it should have been prevented.'
        );

        // ensure redirected back
        $response->assertRedirectToRoute('hardware.bulkcheckout.show');
    }

    /**
     * Regression: BulkAssetsController::store used to call
     *   session()->put(['checkout_to_type' => $target]);
     * with $target being the resolved Eloquent model. The checkout-selector
     * partial compares against 'user'/'asset'/'location' string literals, so
     * an object silently mismatched and no radio rendered `checked`.
     *
     * @see \App\Http\Controllers\Assets\BulkAssetsController::store
     */
    public function test_bulk_checkout_defaults_to_asset_index_when_no_redirect_option()
    {
        Mail::fake();
        $assets = Asset::factory()->count(2)->create();
        $user = User::factory()->create();

        $this->actingAs(User::factory()->checkoutAssets()->viewAssets()->create())
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => $assets->pluck('id')->toArray(),
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
            ])
            ->assertRedirect(route('hardware.index'));
    }

    public function test_bulk_checkout_returns_to_bulk_checkout_when_option_selected()
    {
        Mail::fake();
        $assets = Asset::factory()->count(2)->create();
        $user = User::factory()->create();

        $this->actingAs(User::factory()->checkoutAssets()->viewAssets()->create())
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => $assets->pluck('id')->toArray(),
                'checkout_to_type' => 'user',
                'assigned_user' => $user->id,
                'redirect_option' => 'bulk_checkout',
            ])
            ->assertRedirect(route('hardware.bulkcheckout.show'));
    }

    #[DataProvider('bulkCheckoutTargetTypesProvider')]
    public function test_bulk_checkout_stores_target_type_as_string_in_session(string $type)
    {
        [$field, $target] = match ($type) {
            'user' => ['assigned_user', User::factory()->create()->id],
            'asset' => ['assigned_asset', Asset::factory()->create()->id],
            'location' => ['assigned_location', Location::factory()->create()->id],
        };
        $asset = Asset::factory()->create();

        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('hardware.bulkcheckout.store'), [
                'selected_assets' => [$asset->id],
                'checkout_to_type' => $type,
                $field => $target,
            ]);

        $stored = session('checkout_to_type');
        $this->assertIsString($stored, 'checkout_to_type must be a string, not an Eloquent model');
        $this->assertSame($type, $stored);
    }

    public static function bulkCheckoutTargetTypesProvider(): array
    {
        return [
            'user target' => ['user'],
            'asset target' => ['asset'],
            'location target' => ['location'],
        ];
    }
}
