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
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\ConsumableAssignment;
use Illuminate\Support\Facades\Input;

class ConsumableCheckoutController extends Controller
{
    use CheckInOutRequest;

    /**
     * Return a view to checkout a consumable to a user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     *
     * @see ConsumableCheckoutController::store() method that stores the data.
     * @since [v1.0]
     *
     * @param  int  $id
     */
    public function create($id): View|RedirectResponse
    {

        if ($consumable = Consumable::find($id)) {

            $this->authorize('checkout', $consumable);

            // Make sure the category is valid
            if ($consumable->category) {

                // Make sure there is at least one available to checkout
                if ($consumable->numRemaining() <= 0) {
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
     *
     * @see ConsumableCheckoutController::create() method that returns the form.
     * @since [v1.0]
     *
     * @param  int  $consumableId
     * @return RedirectResponse
     *
     * @throws AuthorizationException
     */
    public function store(ConsumableCheckoutRequest $request, $consumableId)
    {
        if (is_null($consumable = Consumable::find($consumableId))) {
            return redirect()->route('consumables.index')->with('error', trans('admin/consumables/message.not_found'));
        }

        $this->authorize('checkout', $consumable);
        $target = $this->determineCheckoutTarget();

        // If the quantity is not present in the request or is not a positive integer, set it to 1
        $quantity = $request->input('checkout_qty');
        if (! isset($quantity) || ! ctype_digit((string) $quantity) || $quantity <= 0) {
            $quantity = 1;
        }

        // Make sure there is at least one available to checkout
        if ($consumable->numRemaining() <= 0 || $quantity > $consumable->numRemaining()) {
            return redirect()->route('consumables.index')->with('error', trans('admin/consumables/message.checkout.unavailable', ['requested' => $quantity, 'remaining' => $consumable->numRemaining()]));
        }

        $admin_user = auth()->user();
//        $assigned_to = e($request->input('assigned_to'));

        // Check if the user exists
//        if (is_null($user = User::find($assigned_to))) {
//            // Redirect to the consumable management page with error
//            return redirect()->route('consumables.checkout.show', $consumable)->with('error', trans('admin/consumables/message.checkout.user_does_not_exist'))->withInput();
//        }
//        $consumable->locations()->attach($consumable->id, [
//            'consumable_id' => $consumable->id,
//            'created_by' => auth()->id(),
//            'quantity' => $quantity,
//            'comment' =>  $request->input('note'),
//            'cost' => $consumable->purchase_cost,
//            'type' => $type,
//            'assigned_to' => $target->id,
//            'assigned_type' => get_class($target),
//        ]);
//
//
//        // Update the consumable data
//        $consumable->assigned_to = e($request->input('assigned_to'));

//        for ($i = 0; $i < $quantity; $i++) {
//            $consumable->users()->attach($consumable->id, [
//                'consumable_id' => $consumable->id,
//                'created_by' => $admin_user->id,
//                'assigned_to' => e($request->input('assigned_to')),
//                'note' => $request->input('note'),
//            ]);
//        }

//        $consumable->checkout_qty = $quantity;

//        event(new CheckoutableCheckedOut(
//            $consumable,
//            $user,
//            auth()->user(),
//            $request->input('note'),
//            [],
//            $consumable->checkout_qty,
//        ));
//        $request->request->add(['checkout_to_type' => 'user']);
//        $request->request->add(['assigned_user' => $user->id]);

        $consumable->checkOut($target, $quantity, $request->input('note'));
        event(new CheckoutableCheckedOut(
            $consumable,
            $user,
            auth()->user(),
            $request->input('note'),
            [],
            $consumable->checkout_qty,
            $request->boolean('sign_in_place'),
        ));

        $request->request->add(['assigned_to' => $target->id]);
        $request->request->add(match ($request->input('checkout_to_type')) {
            'location' => ['assigned_location' => $target->id],
            'asset' => ['assigned_asset' => $target->id],
            'deal' => ['assigned_deal' => $target->id],
            default => ['assigned_user' => $target->id],
        });

        session()->put([
            'redirect_option' => $request->input('redirect_option'),
            'checkout_to_type' => $request->input('checkout_to_type'),
            'sign_in_place' => $request->boolean('sign_in_place'),
        ]);

        // When sign_in_place is requested, redirect to the acceptance/signature page
        // so the user can sign in person. The signature is attributed to the target user.
        if ($request->boolean('sign_in_place')) {
            $acceptance = CheckoutAcceptance::where('checkoutable_type', Consumable::class)
                ->where('checkoutable_id', $consumable->id)
                ->where('assigned_to_id', $user->id)
                ->pending()
                ->latest()
                ->first();

            // If requireAcceptance() is false the listener won't have created one; create it now.
            if (! $acceptance) {
                $acceptance = new CheckoutAcceptance;
                $acceptance->checkoutable()->associate($consumable);
                $acceptance->assignedTo()->associate($user);
                $acceptance->qty = $quantity;
                $acceptance->save();
            }

            session([
                'sign_in_place_acceptance_id' => $acceptance->id,
                'sign_in_place_item_id' => $consumable->id,
                'sign_in_place_resource_type' => 'Consumables',
            ]);

            return redirect()->route('account.accept.item', $acceptance->id)
                ->with('success', trans('admin/consumables/message.checkout.success'));
        }

        // Redirect to the new consumable page
        return Helper::getRedirectOption($request, $consumable->id, 'Consumables')
            ->with('success', trans('admin/consumables/message.checkout.success'));
    }
}
