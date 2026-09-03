<?php

namespace Tests\Unit;

use App\Models\Purchase;
use PHPUnit\Framework\TestCase;

class PurchaseTest extends TestCase
{
    public function test_purchase_without_bitrix_id_displays_error_status(): void
    {
        $purchase = new Purchase([
            'bitrix_id' => null,
            'status' => Purchase::INPROGRESS,
        ]);

        $this->assertSame(Purchase::ERROR, $purchase->statusForDisplay());
    }

    public function test_purchase_with_bitrix_id_displays_saved_status(): void
    {
        $purchase = new Purchase([
            'bitrix_id' => 123,
            'status' => Purchase::INPROGRESS,
        ]);

        $this->assertSame(Purchase::INPROGRESS, $purchase->statusForDisplay());
    }

    public function test_purchase_with_error_status_can_be_deleted_with_assets(): void
    {
        $purchase = new Purchase([
            'bitrix_id' => null,
            'status' => Purchase::ERROR,
        ]);

        $this->assertTrue($purchase->canDeleteWithAssets());
    }

    public function test_active_purchase_cannot_be_deleted_with_assets(): void
    {
        $purchase = new Purchase([
            'bitrix_id' => 123,
            'status' => Purchase::INPROGRESS,
        ]);

        $this->assertFalse($purchase->canDeleteWithAssets());
    }

    public function test_cloning_can_reset_consumable_review_progress(): void
    {
        $purchase = new Purchase([
            'consumables_json' => json_encode([
                ['id' => 1, 'consumable_id' => 10, 'quantity' => 5, 'reviewed' => 3],
                ['id' => 2, 'consumable_id' => 20, 'quantity' => 2],
            ]),
        ]);

        $purchase->resetConsumablesReviewProgress();

        $consumables = json_decode($purchase->consumables_json, true);
        $this->assertSame(0, $consumables[0]['reviewed']);
        $this->assertSame(0, $consumables[1]['reviewed']);
        $this->assertSame(5, $consumables[0]['quantity']);
        $this->assertSame(2, $consumables[1]['quantity']);
    }
}
