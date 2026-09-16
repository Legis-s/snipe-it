<?php

namespace App\Models\Traits;

use App\Models\Purchase;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasPurchaseWorkflow
{
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function user_verified(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_verified_id');
    }

    public function availableForReview(): bool
    {
        $status = Statuslabel::where('name', 'Ожидает проверки')->first();

        return $status !== null && $this->status_id == $status->id;
    }

    public function setStatusAfterPaid(): void
    {
        $status_in_purchase = Statuslabel::where('name', 'В закупке')->firstOrFail();

        // меняем статус на Ожидает инвентаризации, только если актив в статусе "В закупке"
        if ($this->status_id == $status_in_purchase->id) {
            $this->status_id = Statuslabel::where('name', 'Ожидает инвентаризации')->firstOrFail()->id;
        }
    }
}
