<?php

namespace App\Actions\Purchases;

use App\Models\Asset;
use App\Models\CompanyableScope;
use App\Models\Purchase;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class PayPurchaseAction
{
    public static function run(int $purchaseId): Purchase
    {
        Gate::authorize('update', Purchase::class);

        return DB::transaction(function () use ($purchaseId): Purchase {
            $purchase = Purchase::lockForUpdate()->findOrFail($purchaseId);
            $assets = $purchase->assets()->orderBy('id')->lockForUpdate()->get();
            if ($assets->count() !== $purchase->assets()->withoutGlobalScope(CompanyableScope::class)->count()) {
                throw new AuthorizationException;
            }

            foreach ($assets as $asset) {
                $asset->setStatusAfterPaid();
                if (! $asset->isDirty('status_id')) {
                    continue;
                }

                // Quiet saves suppress Watson validation and the observer's Bitrix workflow.
                if (! $asset->isValid() || ! Asset::withoutEvents(fn (): bool => $asset->save())) {
                    throw ValidationException::withMessages($asset->getErrors()->toArray());
                }
            }

            $purchase->bitrix_result_at ??= now();
            if (! in_array($purchase->status, [Purchase::REVIEW, Purchase::FINISHED, Purchase::INVENTORY], true)) {
                $purchase->status = $assets->isEmpty() ? Purchase::REVIEW : Purchase::INVENTORY;
            }
            if (! $purchase->save()) {
                throw ValidationException::withMessages($purchase->getErrors()->toArray());
            }

            return $purchase;
        });
    }
}
