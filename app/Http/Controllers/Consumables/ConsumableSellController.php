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
     * Return a view to checkout a consumable to a user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see ConsumableCheckoutController::store() method that stores the data.
     * @since [v1.0]
     * @param int $consumableId
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function create($consumableId)
    {
        if (is_null($consumable = Consumable::find($consumableId))) {
            return redirect()->route('consumables.index')->with('error', trans('admin/consumables/message.does_not_exist'));
        }
        $this->authorize('checkout', $consumable);

        return view('consumables/sell', compact('consumable'));
    }

    /**
     * Saves the checkout information
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see ConsumableCheckoutController::create() method that returns the form.
     * @since [v1.0]
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

        $target = $this->determineSellTarget();

        $admin_user = Auth::user();
        $quantity = e($request->input('quantity'));
        $note = $request->input('note');

        if (get_class($target) == Contract::class){
            $contract_id = Contract::findOrFail(request('assigned_contract'))->id;
        }else{
            $contract_id =  request('contract_id');
        }

        $consumable->locations()->attach($consumable->id, [
            'consumable_id' => $consumable->id,
            'user_id' => $admin_user->id,
            'quantity' => $quantity,
            'comment' => $note,
            'cost' => $consumable->purchase_cost,
            'type' => ConsumableAssignment::SOLD,
            'assigned_to' => $target->id,
            'assigned_type' => get_class($target),
            'contract_id' => $contract_id,
        ]);


        event(new CheckoutableSell($consumable, $target,$admin_user,$note,null));

        // Redirect to the new consumable page
        return redirect()->route('consumables.index')->with('success', trans('admin/consumables/message.checkout.success'));
    }

}
