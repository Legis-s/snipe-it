<?php

namespace App\Actions\Purchases;

use App\Models\Asset;
use App\Models\CompanyableScope;
use App\Models\Purchase;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class DeletePurchasesAction
{
    /**
     * Without an ID, delete only the rejected-purchase batch.
     */
    public static function run(?int $purchaseId = null): int
    {
        Gate::authorize('delete', Purchase::class);

        return DB::transaction(function () use ($purchaseId): int {
            $query = Purchase::query();
            if ($purchaseId === null) {
                $query->where('status', Purchase::REJECTED);
            } else {
                $query->whereKey($purchaseId);
            }
            $purchases = $query->orderBy('id')->lockForUpdate()->get();
            if ($purchaseId !== null && $purchases->isEmpty()) {
                throw (new ModelNotFoundException)->setModel(Purchase::class, [$purchaseId]);
            }
            foreach ($purchases as $purchase) {
                Gate::authorize('delete', $purchase);
                if (! $purchase->canDeleteWithAssets()) {
                    throw ValidationException::withMessages(['purchases' => trans('general.purchase_delete_status_error')]);
                }
            }
            $assets = Asset::whereIn('purchase_id', $purchases->modelKeys())->orderBy('id')->lockForUpdate()->get();
            if ($assets->count() !== Asset::withoutGlobalScope(CompanyableScope::class)->whereIn('purchase_id', $purchases->modelKeys())->count()) {
                throw new AuthorizationException;
            }
            foreach ($assets as $asset) {
                Gate::authorize('delete', $asset);
                if ($asset->assigned_to !== null || $asset->assigned_type !== null) {
                    throw ValidationException::withMessages(['purchases' => trans('general.purchase_delete_assigned')]);
                }
            }
            foreach ($assets as $asset) {
                if (! $asset->delete()) {
                    throw ValidationException::withMessages(['purchases' => trans('general.purchase_delete_error')]);
                }
            }
            foreach ($purchases as $purchase) {
                if (! $purchase->delete()) {
                    throw ValidationException::withMessages(['purchases' => trans('general.purchase_delete_error')]);
                }
            }

            return $purchases->count();
        });
    }
}
