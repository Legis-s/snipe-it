<?php

namespace App\Actions\Purchases;

use App\Models\Asset;
use App\Models\Company;
use App\Models\CompanyableScope;
use App\Models\Consumable;
use App\Models\Purchase;
use App\Models\Statuslabel;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReceiveLegacyConsumablesAction
{
    public static function run(int $purchaseId): Purchase
    {
        Gate::authorize('update', Purchase::class);

        return DB::transaction(function () use ($purchaseId): Purchase {
            $purchase = Purchase::lockForUpdate()->findOrFail($purchaseId);
            Gate::authorize('update', $purchase);
            $assets = $purchase->assets()->orderBy('id')->lockForUpdate()->get();
            if ($assets->count() !== $purchase->assets()->withoutGlobalScope(CompanyableScope::class)->count()) {
                throw new AuthorizationException;
            }
            $existing = Consumable::withTrashed()->where('purchase_id', $purchaseId)->orderBy('id')->lockForUpdate()->get();
            if ($existing->count() !== Consumable::withTrashed()->withoutGlobalScope(CompanyableScope::class)->where('purchase_id', $purchaseId)->count()) {
                throw new AuthorizationException;
            }
            $alreadyMerged = DB::table('consumable_merges')->where('source_purchase_id', $purchaseId)->exists();
            if ($existing->isEmpty() && ! $alreadyMerged) {
                $lines = json_decode($purchase->consumables_json ?: '[]', true);
                if (! is_array($lines)) {
                    throw ValidationException::withMessages(['consumables' => trans('validation.array', ['attribute' => 'consumables'])]);
                }
                Validator::make(['consumables' => $lines], [
                    'consumables' => 'array',
                    'consumables.*' => 'array',
                    'consumables.*.name' => 'required|string|max:255',
                    'consumables.*.category_id' => 'required|integer|exists:categories,id',
                    'consumables.*.manufacturer_id' => 'nullable|integer|exists:manufacturers,id',
                    'consumables.*.quantity' => 'required|integer|min:1|max:99999',
                    'consumables.*.purchase_cost' => 'required|numeric|min:0|max:99999999999999999.99',
                    'consumables.*.consumable_id' => 'prohibited',
                ])->validate();
                if ($lines !== []) {
                    Gate::authorize('create', Consumable::class);
                }
                foreach ($lines as $line) {
                    $consumable = new Consumable;
                    $consumable->name = $line['name'];
                    $consumable->category_id = $line['category_id'];
                    $consumable->manufacturer_id = $line['manufacturer_id'] ?? null;
                    $consumable->company_id = Company::getIdForCurrentUser(null);
                    $consumable->created_by = auth()->id();
                    $consumable->qty = $line['quantity'];
                    $consumable->purchase_id = $purchaseId;
                    if (! $consumable->save()) {
                        throw ValidationException::withMessages($consumable->getErrors()->toArray());
                    }
                    // The standard creation observer supplies the initial stock order and audit link.
                    $orderItem = $consumable->orderItems()->firstOrFail();
                    $order = $orderItem->order;
                    $order->order_number = (string) $purchaseId;
                    $order->supplier_id = $purchase->supplier_id;
                    $order->purchase_date = $purchase->created_at->toDateString();
                    $order->currency = $purchase->currency ?? $order->currency;
                    if (! $order->save()) {
                        throw ValidationException::withMessages($order->getErrors()->toArray());
                    }
                    $orderItem->price = $line['purchase_cost'];
                    if (! $orderItem->save()) {
                        throw ValidationException::withMessages($orderItem->getErrors()->toArray());
                    }
                }
            }
            $availableStatus = $assets->isNotEmpty() ? Statuslabel::where('name', 'Доступные')->firstOrFail() : null;
            if ($assets->isEmpty() || $assets->every(fn (Asset $asset): bool => $asset->status_id == $availableStatus->id)) {
                $purchase->status = Purchase::FINISHED;
            }
            if (! $purchase->save()) {
                throw ValidationException::withMessages($purchase->getErrors()->toArray());
            }

            return $purchase;
        });
    }
}
