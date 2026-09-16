<?php

namespace Tests\Feature\Purchases;

use App\Actions\Purchases\DeletePurchasesAction;
use App\Http\Transformers\ContractsTransformer;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Contract;
use App\Models\InvoiceType;
use App\Models\LegalPerson;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use App\Presenters\ContractPresenter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class DeleteRejectedPurchasesTest extends TestCase
{
    private function purchase(string $status = Purchase::REJECTED): Purchase
    {
        $purchase = new Purchase([
            'invoice_number' => fake()->uuid(), 'invoice_file' => 'invoice.pdf',
            'bitrix_id' => 123, 'final_price' => 20, 'comment' => 'Deletion test',
            'supplier_id' => Supplier::factory()->create()->id,
            'legal_person_id' => LegalPerson::create(['name' => fake()->uuid()])->id,
            'invoice_type_id' => InvoiceType::create(['name' => fake()->uuid()])->id,
            'status' => $status,
        ]);
        $this->assertTrue($purchase->save());

        return $purchase;
    }

    public static function deletable_statuses(): array
    {
        return [[Purchase::REJECTED], [Purchase::ERROR]];
    }

    #[DataProvider('deletable_statuses')]
    public function test_single_delete_preserves_asset_history_and_other_purchases(string $status): void
    {
        $purchase = $this->purchase($status);
        $other = $this->purchase();
        $asset = Asset::factory()->create(['purchase_id' => $purchase->id]);
        $this->actingAs(User::factory()->superuser()->create())
            ->delete(route('purchases.destroy', $purchase))->assertRedirect(route('purchases.index'))
            ->assertSessionHasNoErrors();
        $this->assertSoftDeleted('purchases', ['id' => $purchase->id]);
        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
        $this->assertFalse($other->fresh()->trashed());
        $this->assertDatabaseHas('action_logs', ['item_type' => Asset::class, 'item_id' => $asset->id, 'action_type' => 'delete']);
        $this->delete(route('purchases.destroy', $purchase))->assertRedirect(route('purchases.index'));
        $this->assertDatabaseHas('assets', ['id' => $asset->id]);
    }

    public static function protected_statuses(): array
    {
        return [[Purchase::INPROGRESS], [Purchase::INVENTORY], [Purchase::REVIEW], [Purchase::FINISHED]];
    }

    #[DataProvider('protected_statuses')]
    public function test_single_delete_rejects_active_purchase_statuses(string $status): void
    {
        $purchase = $this->purchase($status);
        $this->actingAs(User::factory()->superuser()->create())
            ->delete(route('purchases.destroy', $purchase))->assertRedirect(route('purchases.index'))
            ->assertSessionHas('error', trans('general.purchase_delete_status_error'));
        $this->assertFalse($purchase->fresh()->trashed());
    }

    public function test_action_rechecks_persisted_status_before_deletion(): void
    {
        $purchase = $this->purchase();
        Purchase::whereKey($purchase->id)->update(['status' => Purchase::FINISHED]);
        $this->actingAs(User::factory()->superuser()->create());
        try {
            DeletePurchasesAction::run($purchase->id);
            $this->fail('Expected a status validation error');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('purchases', $exception->errors());
        }
        $this->assertFalse($purchase->fresh()->trashed());
    }

    public function test_single_delete_requires_both_permissions_and_rejects_assigned_assets(): void
    {
        $purchase = $this->purchase();
        $asset = Asset::factory()->assignedToUser()->create(['purchase_id' => $purchase->id]);
        foreach ([[], ['purchases.delete' => 1]] as $permissions) {
            $this->actingAs(User::factory()->create(['permissions' => json_encode($permissions)]))
                ->delete(route('purchases.destroy', $purchase))->assertForbidden();
        }
        $this->actingAs(User::factory()->superuser()->create())
            ->delete(route('purchases.destroy', $purchase))->assertSessionHasErrors('purchases');
        $this->assertFalse($purchase->fresh()->trashed());
        $this->assertFalse($asset->fresh()->trashed());
        $this->assertEquals($asset->assigned_to, $asset->fresh()->assigned_to);
    }

    public function test_single_delete_rejects_hidden_company_assets(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $purchase = $this->purchase();
        $asset = Asset::factory()->for(Company::factory()->create())->create(['purchase_id' => $purchase->id]);
        $actor = User::factory()->create(['permissions' => json_encode(['purchases.delete' => 1, 'assets.delete' => 1])]);
        $actor->companies()->attach(Company::factory()->create());
        $this->actingAs($actor)->delete(route('purchases.destroy', $purchase))->assertForbidden();
        $this->assertFalse($purchase->fresh()->trashed());
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'deleted_at' => null]);
    }

    public function test_single_delete_rolls_back_assets_when_purchase_refuses_deletion(): void
    {
        $purchase = $this->purchase();
        $asset = Asset::factory()->create(['purchase_id' => $purchase->id]);
        Purchase::deleting(fn (): bool => false);
        $dispatcher = Asset::getEventDispatcher();
        $this->actingAs(User::factory()->superuser()->create())
            ->delete(route('purchases.destroy', $purchase))->assertSessionHasErrors('purchases');
        $this->assertFalse($purchase->fresh()->trashed());
        $this->assertFalse($asset->fresh()->trashed());
        $this->assertSame($dispatcher, Asset::getEventDispatcher());
        $this->assertDatabaseMissing('action_logs', ['item_type' => Asset::class, 'item_id' => $asset->id, 'action_type' => 'delete']);
    }

    public function test_confirmed_post_soft_deletes_only_rejected_purchases_and_keeps_events_and_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('assets/deletion-test.png', 'image');
        $purchase = $this->purchase();
        $asset = Asset::factory()->create(['purchase_id' => $purchase->id, 'image' => 'deletion-test.png']);
        $active = $this->purchase(Purchase::INPROGRESS);
        $dispatcher = Asset::getEventDispatcher();
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('purchases.delete_all_rejected'), ['confirmed' => 1])
            ->assertRedirect(route('purchases.index'))->assertSessionHasNoErrors();
        $this->assertSoftDeleted('assets', ['id' => $asset->id]);
        $this->assertSoftDeleted('purchases', ['id' => $purchase->id]);
        $this->assertFalse($active->fresh()->trashed());
        $this->assertSame($dispatcher, Asset::getEventDispatcher());
        Storage::disk('public')->assertExists('assets/deletion-test.png');
        $this->assertDatabaseHas('action_logs', ['item_type' => Asset::class, 'item_id' => $asset->id, 'action_type' => 'delete']);
        $this->post(route('purchases.delete_all_rejected'), ['confirmed' => 1])->assertSessionHasNoErrors();
        $this->assertFalse($active->fresh()->trashed());
    }

    public function test_get_and_unconfirmed_post_cannot_delete(): void
    {
        $purchase = $this->purchase();
        $this->actingAs(User::factory()->superuser()->create())
            ->get('/purchases/delete_all_rejected')->assertRedirect();
        $this->assertFalse($purchase->fresh()->trashed());
        $this->post(route('purchases.delete_all_rejected'))->assertSessionHasErrors('confirmed');
        $this->assertFalse($purchase->fresh()->trashed());
    }

    public function test_purchase_and_asset_delete_permissions_are_required(): void
    {
        $purchase = $this->purchase();
        $asset = Asset::factory()->create(['purchase_id' => $purchase->id]);
        foreach ([[], ['purchases.delete' => 1]] as $permissions) {
            $this->actingAs(User::factory()->create(['permissions' => json_encode($permissions)]))
                ->post(route('purchases.delete_all_rejected'), ['confirmed' => 1])->assertForbidden();
            $this->assertFalse($purchase->fresh()->trashed());
            $this->assertFalse($asset->fresh()->trashed());
        }
    }

    public function test_assigned_assets_prevent_the_entire_batch(): void
    {
        $first = $this->purchase();
        $second = $this->purchase();
        $asset = Asset::factory()->assignedToUser()->create(['purchase_id' => $second->id]);
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('purchases.delete_all_rejected'), ['confirmed' => 1])->assertSessionHasErrors('purchases');
        $this->assertFalse($first->fresh()->trashed());
        $this->assertFalse($second->fresh()->trashed());
        $this->assertEquals($asset->assigned_to, $asset->fresh()->assigned_to);
    }

    public function test_hidden_company_assets_prevent_partial_deletion(): void
    {
        $this->settings->enableMultipleFullCompanySupport();
        $purchase = $this->purchase();
        $company = Company::factory()->create();
        $asset = Asset::factory()->for($company)->create(['purchase_id' => $purchase->id]);
        $actor = User::factory()->create(['permissions' => json_encode(['purchases.delete' => 1, 'assets.delete' => 1])]);
        $actor->companies()->attach(Company::factory()->create());
        $this->actingAs($actor)->post(route('purchases.delete_all_rejected'), ['confirmed' => 1])->assertForbidden();
        $this->assertDatabaseHas('assets', ['id' => $asset->id, 'deleted_at' => null]);
        $this->assertFalse($purchase->fresh()->trashed());
    }

    public function test_late_failure_rolls_back_assets_purchases_and_logs(): void
    {
        $first = $this->purchase();
        $second = $this->purchase();
        $asset = Asset::factory()->create(['purchase_id' => $first->id]);
        Purchase::deleting(function (Purchase $purchase) use ($second): void {
            if ($purchase->id === $second->id) {
                throw new RuntimeException('Late deletion failure');
            }
        });
        $this->withoutExceptionHandling();
        try {
            $this->actingAs(User::factory()->superuser()->create())
                ->post(route('purchases.delete_all_rejected'), ['confirmed' => 1]);
            $this->fail('Expected deletion failure');
        } catch (RuntimeException $exception) {
            $this->assertSame('Late deletion failure', $exception->getMessage());
        }
        $this->assertFalse($first->fresh()->trashed());
        $this->assertFalse($second->fresh()->trashed());
        $this->assertFalse($asset->fresh()->trashed());
        $this->assertDatabaseMissing('action_logs', ['item_type' => Asset::class, 'item_id' => $asset->id, 'action_type' => 'delete']);
    }

    public function test_purchase_page_has_csrf_protected_post_form_only_for_deleters(): void
    {
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('purchases.index'))->assertOk()
            ->assertSee('id="delete-rejected-purchases" method="POST"', false)
            ->assertSee('name="_token"', false)->assertSee('window.confirm', false);
        $this->actingAs(User::factory()->create(['permissions' => json_encode(['purchases.view' => 1, 'locations.view' => 1])]))
            ->get(route('purchases.index'))->assertOk()->assertDontSee('id="delete-rejected-purchases"', false);
    }

    public function test_post_requires_csrf_token_outside_the_testing_bypass(): void
    {
        $purchase = $this->purchase();
        $this->app->instance(\App\Http\Middleware\VerifyCsrfToken::class,
            new class($this->app, $this->app['encrypter']) extends \App\Http\Middleware\VerifyCsrfToken
            {
                protected function runningUnitTests(): bool
                {
                    return false;
                }
            });
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('purchases.delete_all_rejected'), ['confirmed' => 1])
            ->assertRedirect()->assertSessionHas('error', trans('general.token_expired'));
        $this->assertFalse($purchase->fresh()->trashed());
    }

    public function test_rejected_model_delete_rolls_back_preceding_assets(): void
    {
        $purchase = $this->purchase();
        $first = Asset::factory()->create(['purchase_id' => $purchase->id]);
        $second = Asset::factory()->create(['purchase_id' => $purchase->id]);
        Asset::deleting(fn (Asset $asset): bool => $asset->id !== $second->id);
        $this->actingAs(User::factory()->superuser()->create())
            ->post(route('purchases.delete_all_rejected'), ['confirmed' => 1])->assertSessionHasErrors('purchases');
        $this->assertFalse($purchase->fresh()->trashed());
        $this->assertFalse($first->fresh()->trashed());
        $this->assertFalse($second->fresh()->trashed());
    }

    public function test_contract_page_and_columns_no_longer_offer_closing_documents(): void
    {
        $contract = Contract::create(['name' => 'Contract without obsolete action', 'number' => '123']);
        $this->actingAs(User::factory()->superuser()->create())->get(route('contracts.show', $contract))
            ->assertOk()->assertDontSee('closeing_docs', false)->assertDontSee('/closesell', false)
            ->assertSee('assetsTable', false)->assertSee('consumablesCheckedoutTable', false);
        $this->assertStringNotContainsString('no_docs', ContractPresenter::dataTableLayout());
        $payload = (new ContractsTransformer)->transformContract($contract);
        $this->assertArrayNotHasKey('assets_no_docs_count', $payload);
        $this->assertArrayNotHasKey('consumable_no_docs_count', $payload);
    }
}
