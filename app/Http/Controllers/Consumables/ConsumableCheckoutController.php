<?php

namespace App\Http\Controllers\Consumables;

use App\Events\CheckoutableCheckedOut;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConsumableCheckoutRequest;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class ConsumableCheckoutController extends Controller
{
    use CheckInOutRequest;

    /**
     * Return a view to checkout a consumable to a user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see ConsumableCheckoutController::store() method that stores the data.
     * @since [v1.0]
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

                // Make sure there is at least one available to checkout
                if ($consumable->numRemaining() <= 0){
                    return redirect()->route('consumables.index')
                        ->with('error', trans('admin/consumables/message.checkout.unavailable'));
                }

                // Return the checkout view
                return view('consumables/checkout', compact('consumable'));
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
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see ConsumableCheckoutController::create() method that returns the form.
     * @since [v1.0]
     * @param int $consumableId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(ConsumableCheckoutRequest $request, $consumableId)
    {
        if (is_null($consumable = Consumable::find($consumableId))) {
            return redirect()->route('consumables.index')->with('error', trans('admin/consumables/message.not_found'));
        }

        $this->authorize('checkout', $consumable);

        // Make sure there is at least one available to checkout
        if ($consumable->numRemaining() <= 0) {
            return redirect()->route('consumables.index')->with('error', trans('admin/consumables/message.checkout.unavailable'));
        }

        $admin_user = Auth::user();

        $quantity = e($request->input('quantity'));
        $note = $request->input('note');


        $target = $this->determineCheckoutTarget();

        $consumable->locations()->attach($consumable->id, [
            'consumable_id' => $consumable->id,
            'user_id' => $admin_user->id,
            'quantity' => $quantity,
            'comment' => $note,
            'cost' => $consumable->purchase_cost,
            'type' => ConsumableAssignment::ISSUED,
            'assigned_to' => $target->id,
            'assigned_type' => get_class($target),
//            'note' => $request->input('note'),
        ]);


        event(new CheckoutableCheckedOut($consumable, $target, Auth::user(),$note));

        // Redirect to the new consumable page
        return redirect()->route('consumables.index')->with('success', trans('admin/consumables/message.checkout.success'));
    }
}
