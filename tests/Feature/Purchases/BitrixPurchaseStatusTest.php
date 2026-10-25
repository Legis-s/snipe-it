<?php

namespace Tests\Feature\Purchases;

use App\Actions\Purchases\TransitionPurchaseAction;
use App\Models\Asset;
use App\Models\InvoiceType;
use App\Models\LegalPerson;
use App\Models\Purchase;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class BitrixPurchaseStatusTest extends TestCase
{
    private function purchase(): Purchase
    {
        $purchase = new Purchase([
            'invoice_number' => 'Sync-test', 'invoice_file' => 'invoice.pdf',
            'bitrix_id' => 123, 'final_price' => 20, 'comment' => 'Status sync',
            'assets_json' => '[]', 'consumables_json' => '[]',
            'supplier_id' => Supplier::factory()->create()->id,
            'legal_person_id' => LegalPerson::create(['name' => fake()->unique()->company()])->id,
            'invoice_type_id' => InvoiceType::create(['name' => fake()->unique()->word()])->id,
            'status' => Purchase::INPROGRESS,
        ]);
        $this->assertTrue($purchase->save());

        return $purchase;
    }

    public function test_api_and_sync_process_each_result_once_in_either_order(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());
        $from = Statuslabel::factory()->create(['name' => 'В закупке']);
        $inventory = Statuslabel::factory()->create(['name' => 'Ожидает инвентаризации']);
        $rejected = Statuslabel::factory()->create(['name' => 'Отклонено']);
        foreach (['paid' => '!Оплачено', 'reject' => '!Отказ'] as $endpoint => $status) {
            foreach ([true, false] as $apiFirst) {
                $purchase = $this->purchase();
                $asset = Asset::factory()->create(['purchase_id' => $purchase->id, 'status_id' => $from->id]);
                $transition = $endpoint === 'paid' ? 'paid' : 'rejected';
                if ($apiFirst) {
                    $this->postJson(route('api.purchases.'.$endpoint, $purchase))->assertOk()->assertStatusMessageIs('success');
                } else {
                    TransitionPurchaseAction::run($purchase->id, $transition, $status);
                }
                $resultAt = $purchase->fresh()->bitrix_result_at;
                $assetUpdatedAt = $asset->fresh()->updated_at;
                $this->travel(1)->hours();
                TransitionPurchaseAction::run($purchase->id, $transition, $status);
                $updatedAt = $purchase->fresh()->updated_at;
                $this->travel(1)->hours();
                $this->postJson(route('api.purchases.'.$endpoint, $purchase))->assertOk()->assertStatusMessageIs('success');
                TransitionPurchaseAction::run($purchase->id, $transition, $status);
                $this->assertEquals($resultAt, $purchase->fresh()->bitrix_result_at);
                $this->assertEquals($updatedAt, $purchase->fresh()->updated_at);
                $this->assertEquals($assetUpdatedAt, $asset->fresh()->updated_at);
                $this->assertSame($status, $purchase->fresh()->bitrix_status);
                $this->assertEquals($endpoint === 'paid' ? $inventory->id : $rejected->id, $asset->fresh()->status_id);
            }
        }
    }

    public function test_late_payment_and_rejection_callbacks_do_not_regress_terminal_states(): void
    {
        $this->actingAsForApi(User::factory()->superuser()->create());
        foreach ([Purchase::REVIEW, Purchase::INVENTORY, Purchase::FINISHED, Purchase::REJECTED] as $state) {
            $purchase = $this->purchase();
            DB::table('purchases')->where('id', $purchase->id)->update(['status' => $state]);
            foreach (['in_payment', 'reject', 'paid'] as $endpoint) {
                $this->postJson(route('api.purchases.'.$endpoint, $purchase))->assertOk()->assertStatusMessageIs('success');
                $this->assertSame($state, $purchase->fresh()->status);
            }
        }
    }

    public function test_rejection_failure_rolls_back_assets_and_bitrix_status(): void
    {
        $purchase = $this->purchase();
        $from = Statuslabel::factory()->create();
        Statuslabel::factory()->create(['name' => 'Отклонено']);
        $asset = Asset::factory()->create(['purchase_id' => $purchase->id, 'status_id' => $from->id]);
        DB::table('purchases')->where('id', $purchase->id)->update(['comment' => '']);
        try {
            TransitionPurchaseAction::run($purchase->id, 'rejected', '!Отказ');
            $this->fail('Expected validation failure');
        } catch (\Illuminate\Validation\ValidationException $exception) {
            $this->assertArrayHasKey('comment', $exception->errors());
        }
        $this->assertEquals($from->id, $asset->fresh()->status_id);
        $this->assertSame(Purchase::INPROGRESS, $purchase->fresh()->status);
        $this->assertNull($purchase->fresh()->bitrix_status);
    }

    public function test_command_resolves_enums_handles_pagination_and_ignores_unlinked_elements(): void
    {
        $purchase = $this->purchase();
        $history = [];
        $stack = HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['result' => ['L' => ['FIELD_ID' => 'PROPERTY_562', 'DISPLAY_VALUES_FORM' => ['1281' => 'Оплата', '1284' => '!Оплачено']]]])),
            new Response(200, [], json_encode(['result' => [['ID' => '999', 'PROPERTY_562' => ['1' => '1284']]], 'next' => 50])),
            new Response(200, [], json_encode(['result' => [['ID' => '123', 'PROPERTY_562' => ['77' => '1281']]]])),
        ]));
        $stack->push(Middleware::history($history));
        $this->app->instance(Client::class, new Client(['handler' => $stack]));
        config(['services.bitrix.url' => 'https://bitrix.example', 'services.bitrix.user' => '1', 'services.bitrix.key' => 'test']);
        $this->artisan('snipeit:sync-bitrix-purchases')->assertExitCode(0);
        $this->assertSame('Оплата', $purchase->fresh()->bitrix_status);
        $this->assertSame('in_payment', $purchase->fresh()->status);
        parse_str($history[1]['request']->getUri()->getQuery(), $query);
        $this->assertEquals(['123'], $query['FILTER']['ID']);
        parse_str($history[2]['request']->getUri()->getQuery(), $query);
        $this->assertSame('50', $query['start']);
        Statuslabel::factory()->create(['name' => 'Доступные']);
        $this->actingAsForApi(User::factory()->superuser()->create())
            ->getJson(route('api.purchases.show', $purchase))->assertOk()->assertJsonPath('bitrix_status', 'Оплата');
    }

    public function test_command_applies_paid_and_rejected_and_preserves_other_statuses(): void
    {
        Statuslabel::factory()->create(['name' => 'В закупке']);
        Statuslabel::factory()->create(['name' => 'Ожидает инвентаризации']);
        Statuslabel::factory()->create(['name' => 'Отклонено']);
        foreach (['!Оплачено' => Purchase::REVIEW, '!Отказ' => Purchase::REJECTED, 'Согласование' => Purchase::INPROGRESS] as $label => $state) {
            $purchase = $this->purchase();
            $this->app->instance(Client::class, new Client(['handler' => HandlerStack::create(new MockHandler([
                new Response(200, [], json_encode(['result' => ['L' => ['FIELD_ID' => 'PROPERTY_562', 'DISPLAY_VALUES_FORM' => ['9000' => $label]]]])),
                new Response(200, [], json_encode(['result' => [['ID' => '123', 'PROPERTY_562' => ['value' => '9000']]]])),
            ]))]));
            config(['services.bitrix.url' => 'https://bitrix.example', 'services.bitrix.user' => '1', 'services.bitrix.key' => 'test']);
            $this->artisan('snipeit:sync-bitrix-purchases')->assertExitCode(0);
            $this->assertSame($label, $purchase->fresh()->bitrix_status);
            $this->assertSame($state, $purchase->fresh()->status);
            $purchase->delete();
        }
    }

    public function test_unknown_enum_does_not_clear_status_or_change_purchase(): void
    {
        $purchase = $this->purchase();
        TransitionPurchaseAction::run($purchase->id, null, 'Согласование');
        $this->app->instance(Client::class, new Client(['handler' => HandlerStack::create(new MockHandler([
            new Response(200, [], json_encode(['result' => ['L' => ['FIELD_ID' => 'PROPERTY_562', 'DISPLAY_VALUES_FORM' => ['1284' => '!Оплачено']]]])),
            new Response(200, [], json_encode(['result' => [['ID' => '123', 'PROPERTY_562' => ['value' => 'unknown']]]])),
        ]))]));
        config(['services.bitrix.url' => 'https://bitrix.example', 'services.bitrix.user' => '1', 'services.bitrix.key' => 'test']);
        $this->artisan('snipeit:sync-bitrix-purchases')->assertExitCode(1);
        $this->assertSame('Согласование', $purchase->fresh()->bitrix_status);
        $this->assertSame(Purchase::INPROGRESS, $purchase->fresh()->status);
    }

    public function test_purchase_card_displays_escaped_bitrix_status(): void
    {
        $purchase = $this->purchase();
        Statuslabel::factory()->create(['name' => 'Доступные']);
        TransitionPurchaseAction::run($purchase->id, null, '<b>Согласование</b>');
        $this->actingAs(User::factory()->superuser()->create())
            ->get(route('purchases.show', $purchase))->assertOk()
            ->assertSee(trans('general.bitrix_status'))->assertSee('<b>Согласование</b>');
    }

    public function test_command_rejects_invalid_rest_response_without_modifying_purchase(): void
    {
        $purchase = $this->purchase();
        $this->app->instance(Client::class, new Client(['handler' => HandlerStack::create(new MockHandler([
            new Response(200, [], '{"error":"ACCESS_DENIED"}'),
        ]))]));
        config(['services.bitrix.url' => 'https://bitrix.example', 'services.bitrix.user' => '1', 'services.bitrix.key' => 'test']);
        $this->artisan('snipeit:sync-bitrix-purchases')->assertExitCode(1);
        $this->assertNull($purchase->fresh()->bitrix_status);
        $this->assertSame(Purchase::INPROGRESS, $purchase->fresh()->status);
    }
}
