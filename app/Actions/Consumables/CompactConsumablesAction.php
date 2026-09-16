<?php

namespace App\Actions\Consumables;

use App\Models\Actionlog;
use App\Models\CheckoutRequest;
use App\Models\CompanyableScope;
use App\Models\Consumable;
use App\Models\OrderItem;
use App\Models\PredefinedKit;
use App\Models\Purchase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CompactConsumablesAction
{
    /**
     * @param  array<int, int>  $sourceIds
     */
    public static function run(int $targetId, array $sourceIds): Consumable
    {
        return DB::transaction(function () use ($targetId, $sourceIds): Consumable {
            if ($sourceIds === [] || in_array($targetId, $sourceIds, true) || count(array_unique($sourceIds)) !== count($sourceIds)) {
                throw ValidationException::withMessages(['id_array' => trans('validation.distinct', ['attribute' => 'id_array'])]);
            }
            $ids = [$targetId, ...$sourceIds];
            // Receipt locks purchases before consumables; use the same order here.
            $purchases = Purchase::withTrashed()
                ->orderBy('id')->lockForUpdate()->get();
            $items = Consumable::whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            if ($items->count() !== count($ids)) {
                throw ValidationException::withMessages(['id_array' => trans('validation.exists', ['attribute' => 'id_array'])]);
            }
            if (DB::table('consumable_merges')->whereIn('source_id', $ids)->exists()) {
                throw ValidationException::withMessages(['id_array' => trans('general.consumable_already_merged')]);
            }
            $target = $items->get($targetId);
            Gate::authorize('update', $target);
            foreach ($sourceIds as $sourceId) {
                $source = $items->get($sourceId);
                Gate::authorize('update', $source);
                Gate::authorize('delete', $source);
                if ($source->company_id != $target->company_id) {
                    throw ValidationException::withMessages(['id_array' => trans('general.error_user_company')]);
                }
            }

            $legacyPurchaseIds = $items->pluck('purchase_id')->merge(
                DB::table('consumable_merges')->whereIn('current_target_id', $ids)->pluck('source_purchase_id')
            )->filter(fn ($id): bool => $id !== null)->unique();
            foreach ($legacyPurchaseIds as $purchaseId) {
                $purchase = $purchases->firstWhere('id', $purchaseId);
                if (! $purchase) {
                    throw ValidationException::withMessages(['id_array' => trans('general.consumable_merge_dependencies')]);
                }
                Gate::authorize('update', $purchase);
            }
            foreach ($purchases as $purchase) {
                if ($purchase->consumables_json === null) {
                    continue;
                }
                $lines = json_decode($purchase->consumables_json, true);
                if (! is_array($lines)) {
                    throw ValidationException::withMessages(['id_array' => trans('general.consumable_merge_dependencies')]);
                }
                $changed = false;
                foreach ($lines as &$line) {
                    if (! is_array($line)) {
                        throw ValidationException::withMessages(['id_array' => trans('general.consumable_merge_dependencies')]);
                    }
                    if (in_array((int) ($line['consumable_id'] ?? 0), $sourceIds, true)) {
                        $line['consumable_id'] = $targetId;
                        $changed = true;
                    }
                }
                unset($line);
                if (! $changed) {
                    continue;
                }
                Gate::authorize('update', $purchase);
                $targetLines = collect($lines)->filter(fn (array $line): bool => (int) ($line['consumable_id'] ?? 0) === $targetId);
                if ($targetLines->count() > 1) {
                    $rowIds = $targetLines->map(fn (array $line) => filter_var($line['id'] ?? null, FILTER_VALIDATE_INT));
                    if ($rowIds->containsStrict(false) || $rowIds->unique()->count() !== $rowIds->count()) {
                        throw ValidationException::withMessages(['id_array' => trans('general.consumable_merge_dependencies')]);
                    }
                }
                $purchase->consumables_json = json_encode($lines, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
                if (! $purchase->save()) {
                    throw ValidationException::withMessages($purchase->getErrors()->toArray());
                }
            }

            $kitRows = DB::table('kits_consumables')->whereIn('consumable_id', $ids)
                ->orderBy('kit_id')->orderBy('id')->lockForUpdate()->get();
            foreach ($kitRows->groupBy('kit_id') as $rows) {
                if (! $rows->contains(fn (object $row): bool => in_array((int) $row->consumable_id, $sourceIds, true))) {
                    continue;
                }
                $kit = PredefinedKit::whereKey($rows->first()->kit_id)->lockForUpdate()->firstOrFail();
                Gate::authorize('update', $kit);
                $quantity = $rows->sum('quantity');
                if ($rows->contains(fn (object $row): bool => $row->quantity < 1) || $quantity > 2147483647) {
                    throw ValidationException::withMessages(['id_array' => trans('validation.between.numeric', [
                        'attribute' => trans('general.quantity'), 'min' => 1, 'max' => 2147483647,
                    ])]);
                }
                $retained = $rows->firstWhere('consumable_id', $targetId) ?? $rows->first();
                DB::table('kits_consumables')->where('id', $retained->id)->update([
                    'consumable_id' => $targetId, 'quantity' => $quantity, 'updated_at' => now(),
                ]);
                DB::table('kits_consumables')->whereIn('id', $rows->pluck('id')->reject(fn (int $id): bool => $id === $retained->id))->delete();
            }

            $requests = CheckoutRequest::where('requestable_type', Consumable::class)
                ->whereIn('requestable_id', $sourceIds)->pending()->orderBy('id')->lockForUpdate()->get();
            foreach ($requests as $request) {
                $request->requestable_id = $targetId;
                if (! $request->save()) {
                    throw ValidationException::withMessages(['id_array' => trans('admin/consumables/message.update.error')]);
                }
            }

            $target->qty = $items->sum('qty');
            DB::table('consumables_users')->whereIn('consumable_id', $sourceIds)->update(['consumable_id' => $targetId]);
            DB::table('consumables_locations')->whereIn('consumable_id', $sourceIds)->update(['consumable_id' => $targetId]);
            OrderItem::withTrashed()->where('item_type', Consumable::class)->whereIn('item_id', $sourceIds)->update(['item_id' => $targetId]);
            Actionlog::withoutGlobalScope(CompanyableScope::class)->where('item_type', Consumable::class)->whereIn('item_id', $sourceIds)
                ->whereNotIn('action_type', ['create', 'delete', 'update'])->update(['item_id' => $targetId]);
            if (! $target->save()) {
                throw ValidationException::withMessages($target->getErrors()->toArray());
            }

            // A normal delete detaches checkouts and removes files outside the transaction.
            // Retain source rows/files for history; zero their stock so restore cannot duplicate it.
            Consumable::whereIn('id', $sourceIds)->update(['qty' => 0, 'deleted_at' => now()]);
            DB::table('consumable_merges')->whereIn('current_target_id', $sourceIds)
                ->update(['current_target_id' => $targetId]);
            foreach ($sourceIds as $sourceId) {
                DB::table('consumable_merges')->insert([
                    'source_id' => $sourceId,
                    'target_id' => $targetId,
                    'current_target_id' => $targetId,
                    'source_purchase_id' => $items->get($sourceId)->purchase_id,
                    'created_by' => auth()->id(),
                    'created_at' => now(),
                ]);
                $log = new Actionlog;
                $log->item_type = Consumable::class;
                $log->item_id = $sourceId;
                $log->target_type = Consumable::class;
                $log->target_id = $targetId;
                $log->created_by = auth()->id();
                $log->quantity = $items->get($sourceId)->qty;
                $log->note = trans('general.consumable_merged_into', ['id' => $targetId]);
                if (! $log->logaction('delete')) {
                    throw ValidationException::withMessages(['id_array' => trans('admin/consumables/message.update.error')]);
                }
            }

            return $target->fresh();
        });
    }
}
