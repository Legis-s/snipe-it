<?php

namespace App\Actions\Purchases;

use App\Models\Asset;
use App\Models\CompanyableScope;
use App\Models\Purchase;
use App\Models\Statuslabel;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class TransitionPurchaseAction
{
    public static function run(int $purchaseId, ?string $transition, ?string $bitrixStatus = null): Purchase
    {
        if (! in_array($transition, [null, 'paid', 'rejected', 'in_payment'], true)) {
            throw new InvalidArgumentException('Unknown purchase transition');
        }

        return DB::transaction(function () use ($purchaseId, $transition, $bitrixStatus): Purchase {
            $purchase = Purchase::lockForUpdate()->findOrFail($purchaseId);
            if ($bitrixStatus !== null) {
                $purchase->bitrix_status = $bitrixStatus;
            }

            $isFinal = in_array($purchase->status, [Purchase::REJECTED, Purchase::INVENTORY, Purchase::REVIEW, Purchase::FINISHED, 'paid'], true);
            if ($transition !== null && ! $isFinal && $purchase->status !== $transition) {
                if ($transition === 'in_payment') {
                    $purchase->status = 'in_payment';
                } else {
                    $assets = $purchase->assets()->orderBy('id')->lockForUpdate()->get();
                    if ($assets->count() !== $purchase->assets()->withoutGlobalScope(CompanyableScope::class)->count()) {
                        throw new AuthorizationException;
                    }
                    $rejectedStatus = $transition === 'rejected' && $assets->isNotEmpty()
                        ? Statuslabel::where('name', 'Отклонено')->firstOrFail()
                        : null;
                    foreach ($assets as $asset) {
                        if ($transition === 'paid') {
                            $asset->setStatusAfterPaid();
                        } else {
                            $asset->status_id = $rejectedStatus->id;
                        }
                        if (! $asset->isDirty('status_id')) {
                            continue;
                        }
                        if (! $asset->isValid() || ! Asset::withoutEvents(fn (): bool => $asset->save())) {
                            throw ValidationException::withMessages($asset->getErrors()->toArray());
                        }
                    }
                    $purchase->bitrix_result_at ??= now();
                    $purchase->status = $transition === 'rejected' ? Purchase::REJECTED
                        : ($assets->isEmpty() ? Purchase::REVIEW : Purchase::INVENTORY);
                }
            }
            if ($purchase->isDirty() && ! $purchase->save()) {
                throw ValidationException::withMessages($purchase->getErrors()->toArray());
            }

            return $purchase;
        });
    }
}
