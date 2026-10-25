<?php

namespace App\Actions\Purchases;

use App\Models\Purchase;
use Illuminate\Support\Facades\Gate;

class PayPurchaseAction
{
    public static function run(int $purchaseId): Purchase
    {
        Gate::authorize('update', Purchase::class);

        return TransitionPurchaseAction::run($purchaseId, 'paid');
    }
}
