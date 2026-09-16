<?php

namespace App\Models\Traits;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;

trait HasLegacyPurchaseLinks
{
    public function scopeForLegacyPurchase(Builder $query, int|Expression $purchaseId): Builder
    {
        return $query->where(function (Builder $items) use ($purchaseId): void {
            $items->where('consumables.purchase_id', $purchaseId)
                ->orWhereExists(function (QueryBuilder $merges) use ($purchaseId): void {
                    $merges->selectRaw('1')->from('consumable_merges')
                        ->whereColumn('consumable_merges.current_target_id', 'consumables.id')
                        ->where('consumable_merges.source_purchase_id', $purchaseId);
                });
        })->whereNotExists(function (QueryBuilder $merges): void {
            $merges->selectRaw('1')->from('consumable_merges')
                ->whereColumn('consumable_merges.source_id', 'consumables.id');
        });
    }
}
