<?php

namespace App\Http\Controllers\Consumables;

use App\Events\CheckoutableCheckedOut;
use App\Events\CheckoutableSell;
use App\Helpers\Helper;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConsumableCheckoutRequest;
use App\Models\Consumable;
use App\Models\Deal;
use App\Models\User;
use Illuminate\Http\Request;
use \Illuminate\Contracts\View\View;
use \Illuminate\Http\RedirectResponse;
use App\Models\ConsumableAssignment;
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
     */
    public function create($id) : View | RedirectResponse
    {

        if ($consumable = Consumable::find($id)) {

            $this->authorize('checkout', $consumable);

            // Make sure the category is valid
            if ($consumable->category) {

                // Make sure there is at least one available to checkout
                if ($consumable->numRemaining() <= 0){
                    return redirect()->route('consumables.index')
                        ->with('error', trans('admin/consumables/message.checkout.unavailable', ['requested' => 1, 'remaining' => $consumable->numRemaining()]));
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

        // If the quantity is not present in the request or is not a positive integer, set it to 1
        $quantity = $request->input('checkout_qty');
        if (!isset($quantity) || !ctype_digit((string)$quantity) || $quantity <= 0) {
            $quantity = 1;
        }

        // Make sure there is at least one available to checkout
        if ($consumable->numRemaining() <= 0 || $quantity > $consumable->numRemaining()) {
            return redirect()->route('consumables.index')->with('error', trans('admin/consumables/message.checkout.unavailable', ['requested' => $quantity, 'remaining' => $consumable->numRemaining() ]));
        }

        $target = $this->determineCheckoutTarget();

        $type = ConsumableAssignment::ISSUED;
        if (is_a($target, Deal::class, true)) {
            $type = ConsumableAssignment::SOLD;
        }
        $consumable->locations()->attach($consumable->id, [
            'consumable_id' => $consumable->id,
            'created_by' => auth()->id(),
            'quantity' => $quantity,
            'comment' =>  $request->input('note'),
            'cost' => $consumable->purchase_cost,
            'type' => $type,
            'assigned_to' => $target->id,
            'assigned_type' => get_class($target),
        ]);


        if (is_a($target, Deal::class, true)) {
            event(new CheckoutableSell($consumable, $target, auth()->user(), $request->input('note')));
        }else{
            event(new CheckoutableCheckedOut($consumable, $target, auth()->user(), $request->input('note')));
        }
        session()->put(['redirect_option' => $request->get('redirect_option'), 'checkout_to_type' => $request->get('checkout_to_type')]);


        // Redirect to the new consumable page
        return Helper::getRedirectOption($request, $consumable->id, 'Consumables')
            ->with('success', trans('admin/consumables/message.checkout.success'));
    }
}
