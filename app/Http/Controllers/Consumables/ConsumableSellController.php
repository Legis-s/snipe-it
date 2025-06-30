<?php

namespace App\Http\Controllers\Consumables;


use App\Events\CheckoutableSell;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetSellRequest;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Input;

class ConsumableSellController extends Controller
{

    use CheckInOutRequest;

    /**
     * Return a view to sell a consumable.
     * @param int $id
     * @see ConsumableSellController::store() method that stores the data.
     */
    public function create($id): View|RedirectResponse
    {

        if ($consumable = Consumable::find($id)) {

            $this->authorize('checkout', $consumable);

            // Make sure the category is valid
            if ($consumable->category) {

                // Make sure there is at least one available to sell
                if ($consumable->numRemaining() <= 0) {
                    return redirect()->route('consumables.index')
                        ->with('error', trans('admin/consumables/message.checkout.unavailable'));
                }

                // Return the checkout view
                return view('consumables/sell', compact('consumable'));
            }

            // Invalid category
            return redirect()->route('consumables.edit', ['consumable' => $consumable->id])
                ->with('error', trans('general.invalid_item_category_single', ['type' => trans('general.consumable')]));
        }

        // Not found
        return redirect()->route('consumables.index')->with('error', trans('admin/consumables/message.does_not_exist'));

    }

    /**
     * Saves the checkout information
     * @param int $consumableId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @see ConsumableSellController::create() method that returns the form.
     */
    public function store(AssetSellRequest $request, $consumableId)
    {
        if (is_null($consumable = Consumable::find($consumableId))) {
            return redirect()->route('consumables.index')->with('error', trans('admin/consumables/message.not_found'));
        }

        $this->authorize('checkout', $consumable);

        // Make sure there is at least one available to sell
        if ($consumable->numRemaining() <= 0) {
            return redirect()->route('consumables.index')->with('error', trans('admin/consumables/message.checkout.unavailable'));
        }

        $target = Deal::findOrFail(request('assigned_deal'));

        $quantity = $request->input('checkout_qty');
        if (!isset($quantity) || !ctype_digit((string)$quantity) || $quantity <= 0) {
            $quantity = 1;
        }

        $consumable->locations()->attach($consumable->id, [
            'consumable_id' => $consumable->id,
            'created_by' => auth()->id(),
            'quantity' => $quantity,
            'comment' => $request->input('note'),
            'cost' => $consumable->purchase_cost,
            'type' => ConsumableAssignment::SOLD,
            'assigned_to' => $target->id,
            'assigned_type' => get_class($target),
            'deal_id' => $target->id,
        ]);


        event(new CheckoutableSell($consumable, $target, auth()->user(), $request->input('note')));

        // Redirect to the new consumable page
        return redirect()->route('consumables.index')->with('success', trans('admin/consumables/message.checkout.success'));
    }

}
