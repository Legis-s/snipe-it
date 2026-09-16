<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConsumableReceiptRequest;
use App\Http\Transformers\ConsumablesTransformer;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Purchase;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ConsumableReceiptController extends Controller
{
    /**
     * Update the specified resource in storage.
     */
    public function store(ConsumableReceiptRequest $request, int $id): JsonResponse
    {
        $consumable = DB::transaction(function () use ($request, $id): Consumable {
            $purchase = Purchase::whereKey($request->integer('purchase_id'))->lockForUpdate()->firstOrFail();
            $consumable = Consumable::withTrashed()->whereKey($id)->lockForUpdate()->firstOrFail();
            if (DB::table('consumable_merges')->where('source_id', $id)->exists()) {
                throw ValidationException::withMessages(['consumable_id' => trans('general.consumable_already_merged')]);
            }
            $lines = json_decode($purchase->consumables_json ?: '[]', true);
            $matches = collect(is_array($lines) ? $lines : [])->filter(function ($line) use ($id, $request) {
                return ($line['consumable_id'] ?? null) == $id
                    && (! $request->filled('row_id') || ($line['id'] ?? null) == $request->integer('row_id'));
            });
            $quantity = $request->integer('quantity');
            if ($matches->count() !== 1) {
                throw ValidationException::withMessages(['purchase_id' => trans('validation.exists', ['attribute' => 'purchase_id'])]);
            }
            $index = $matches->keys()->first();
            $remaining = (int) $lines[$index]['quantity'] - (int) ($lines[$index]['reviewed'] ?? 0);
            if ($quantity > $remaining) {
                throw ValidationException::withMessages(['quantity' => trans('validation.max.numeric', [
                    'attribute' => trans('general.quantity'), 'max' => $remaining,
                ])]);
            }
            if ($consumable->trashed() && ! $consumable->restore()) {
                throw ValidationException::withMessages($consumable->getErrors()->toArray());
            }
            $order = new Order([
                'order_number' => (string) $purchase->id,
                'supplier_id' => $purchase->supplier_id,
                'company_id' => $consumable->company_id,
                'purchase_date' => now()->toDateString(),
                'currency' => $purchase->currency,
            ]);
            $order->created_by = auth()->id();
            if (! $order->save()) {
                throw ValidationException::withMessages($order->getErrors()->toArray());
            }
            $line = new OrderItem([
                'order_id' => $order->id, 'item_type' => Consumable::class,
                'item_id' => $consumable->id, 'qty' => $quantity, 'price' => $request->input('purchase_cost'),
            ]);
            $line->created_by = auth()->id();
            if (! $line->save()) {
                throw ValidationException::withMessages($line->getErrors()->toArray());
            }
            $consumable->adjustQuantity($quantity, 'Purchase #'.$purchase->id, $line->id);
            $consumable->locations()->attach($consumable->id, [
                'consumable_id' => $consumable->id, 'created_by' => auth()->id(),
                'quantity' => $quantity, 'cost' => $request->input('purchase_cost'),
                'type' => ConsumableAssignment::PURCHASE, 'assigned_to' => $purchase->id,
                'assigned_type' => Purchase::class,
            ]);
            $lines[$index]['reviewed'] = (int) ($lines[$index]['reviewed'] ?? 0) + $quantity;
            $purchase->consumables_json = json_encode($lines);
            $purchase->checkStatus();
            if (! $purchase->save()) {
                throw ValidationException::withMessages($purchase->getErrors()->toArray());
            }

            return $consumable->fresh();
        });

        return response()->json(Helper::formatStandardApiResponse(
            'success', (new ConsumablesTransformer)->transformConsumable($consumable),
            trans('admin/consumables/message.update.success')
        ));
    }
}
