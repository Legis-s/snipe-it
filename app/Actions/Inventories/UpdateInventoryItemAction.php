<?php

namespace App\Actions\Inventories;

use App\Models\Asset;
use App\Models\Inventory;
use App\Models\InventoryItem;
use App\Models\InventoryStatuslabel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class UpdateInventoryItemAction
{
    /** @param array<string, mixed> $input */
    public static function run(int $id, array $input): InventoryItem
    {
        Gate::authorize('audit', Asset::class);
        $data = Validator::make($input, [
            'notes' => 'sometimes|nullable|string',
            'checked' => 'sometimes|boolean',
            'status_id' => 'sometimes|required|integer|exists:inventory_status_labels,id',
            'photo' => 'sometimes|nullable|string|max:14000000',
        ])->validate();
        $photoPath = null;
        try {
            return DB::transaction(function () use ($id, $data, &$photoPath): InventoryItem {
                $inventoryId = InventoryItem::findOrFail($id)->inventory_id;
                $inventory = Inventory::whereKey($inventoryId)->lockForUpdate()->firstOrFail();
                $item = InventoryItem::where('inventory_id', $inventoryId)->whereKey($id)->lockForUpdate()->firstOrFail();
                $asset = Asset::whereKey($item->asset_id)->lockForUpdate()->firstOrFail();
                Gate::authorize('audit', $asset);
                $item->fill(array_intersect_key($data, array_flip(['notes', 'checked', 'status_id'])));
                if ($item->status_id == 4 && str_starts_with($asset->asset_tag, 'it_')) {
                    $item->status_id = 1;
                }
                $label = InventoryStatuslabel::findOrFail($item->status_id);
                $item->successfully = (bool) $item->checked && (bool) $label->success;
                $item->checked_at = $item->checked ? ($item->checked_at ?? now()) : null;
                if (! empty($data['photo'])) {
                    $bytes = base64_decode($data['photo'], true);
                    $image = $bytes === false ? false : @getimagesizefromstring($bytes);
                    if (! $image || strlen($bytes) > 10 * 1024 * 1024 || ! in_array($image['mime'], ['image/jpeg', 'image/png'], true)) {
                        throw ValidationException::withMessages(['photo' => trans('validation.image', ['attribute' => 'photo'])]);
                    }
                    $filename = 'items-'.$item->id.'-'.Str::random(16).($image['mime'] === 'image/png' ? '.png' : '.jpg');
                    $photoPath = 'inventory_items/'.$filename;
                    if (! Storage::disk('public')->put($photoPath, $bytes)) {
                        throw ValidationException::withMessages(['photo' => trans('admin/locations/message.update.error')]);
                    }
                    $item->photo = $filename;
                }
                if (! $item->save()) {
                    throw ValidationException::withMessages($item->getErrors()->toArray());
                }
                if ($item->checked) {
                    $asset->last_audit_date = now();
                    if (! $asset->save()) {
                        throw ValidationException::withMessages($asset->getErrors()->toArray());
                    }
                }
                $unchecked = $inventory->inventory_items()->where(function (Builder $query): void {
                    $query->where('checked', false)->orWhereNull('checked');
                })->exists();
                $unsuccessful = $inventory->inventory_items()->where(function (Builder $query): void {
                    $query->where('successfully', false)->orWhereNull('successfully');
                })->exists();
                $inventory->status = $unchecked ? 'START' : ($unsuccessful ? 'FINISH_BAD' : 'FINISH_OK');
                if (! $inventory->save()) {
                    throw ValidationException::withMessages($inventory->getErrors()->toArray());
                }

                return $item->fresh(['status']);
            });
        } catch (Throwable $exception) {
            if ($photoPath !== null) {
                Storage::disk('public')->delete($photoPath);
            }
            throw $exception;
        }
    }
}
