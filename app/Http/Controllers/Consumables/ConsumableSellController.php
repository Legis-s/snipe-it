<?php

namespace App\Http\Controllers\Consumables;

use App\Events\CheckoutableCheckedOut;
use App\Events\CheckoutableSell;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCheckoutRequest;
use App\Http\Requests\AssetSellRequest;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\Contract;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class ConsumableSellController extends Controller
{

    use CheckInOutRequest;

    /**
     * Return a view to sell a consumable.
     * @see ConsumableSellController::store() method that stores the data.
     * @param int $id
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create($id)
    {

        if ($consumable = Consumable::find($id)) {

            $this->authorize('checkout', $consumable);

            // Make sure the category is valid
            if ($consumable->category) {

                // Make sure there is at least one available to sell
                if ($consumable->numRemaining() <= 0){
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
     * @see ConsumableSellController::create() method that returns the form.
     * @param int $consumableId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
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
        $admin_user = Auth::user();

        $target = Contract::findOrFail(request('assigned_contract'));
        $quantity = e($request->input('quantity'));
        $note = $request->input('note');

        $consumable->locations()->attach($consumable->id, [
            'consumable_id' => $consumable->id,
            'user_id' => $admin_user->id,
            'quantity' => $quantity,
            'comment' => $note,
            'cost' => $consumable->purchase_cost,
            'type' => ConsumableAssignment::SOLD,
            'assigned_to' => $target->id,
            'assigned_type' => get_class($target),
            'contract_id' => $target->id,
//            'note' => $request->input('note'),
        ]);


        event(new CheckoutableSell($consumable, $target, Auth::user(),$note));

        // Redirect to the new consumable page
        return redirect()->route('consumables.index')->with('success', trans('admin/consumables/message.checkout.success'));
    }

}
