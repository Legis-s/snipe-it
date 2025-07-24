<?php

namespace App\Http\Controllers\Consumables;

use App\Helpers\Helper;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCheckoutRequest;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\Purchase;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BulkConsumablesController extends Controller
{
    use CheckInOutRequest;

    /**
     * Show Bulk Checkout Page
     */
    public function create(Request $request): View
    {
        $this->authorize('checkout', Consumable::class);

        $ids = [];
        if (request()->filled('purchase_id')) {
            $purchase_id = $request->input('purchase_id');

            $purchase = Purchase::with('consumables')->find($purchase_id);
            $consumable_array = [];

            if ($purchase) {
                $consumable_assignments = ConsumableAssignment::with('user', 'assignedTo', 'consumable', 'contract', 'deal')
                    ->where('assigned_to', '=', $purchase->id)
                    ->where('assigned_type', '=', \App\Models\Purchase::class)
                    ->get();
                $i=1;
                foreach ($consumable_assignments as $consumable_assignment) {
                    $consumable = $consumable_assignment->consumable;
                    $q = $consumable_assignment->quantity;
                    if( $q>$consumable->numRemaining())  $q = $consumable->numRemaining();
                    if ($q>0){
                        $consumable_array[] = [
                            'id'=> $i++,
                            'consumable_id' => $consumable->id,
                            'consumable' =>"[". $consumable->numRemaining()."] ". $consumable->name,
                            'quantity' => $q,
                        ];
                        $ids[] = $consumable_assignment->consumable->id;
                    }
                }
                $consumable_json = json_encode($consumable_array);
            }
        }

        return view('consumables/bulk-checkout', ['selected_consumables' => $ids, 'consumable_json'=> $consumable_json]);
    }

    /**
     * Process Multiple Checkout Request
     */
    public function store(AssetCheckoutRequest $request): RedirectResponse|ModelNotFoundException
    {
        $this->authorize('checkout', Consumable::class);


        try {
            $target = $this->determineCheckoutTarget();

            $errors = [];
            $consumabl_ids = [];
            $consumables_json = $request->input('consumables_json');
            $consumables_array = json_decode($consumables_json, true);

            if (is_array($consumables_array)) {
                DB::transaction(function () use ($target, &$errors, $consumables_array, $consumabl_ids, $request) {
                    foreach ($consumables_array as $c_data) {
                        $consumable = Consumable::find($c_data["consumable_id"]);
                        $consumabl_ids[] = $c_data["consumable_id"];
                        $quantity = $c_data["quantity"];
                        $this->authorize('checkout', $consumable);

                        $checkout_success = $consumable->checkOut($target, $quantity, e($request->get('note')));

                        if (!$checkout_success) {
                            $errors = array_merge_recursive($errors, $consumable->getErrors()->toArray());
                        }
                    }
                });
            } else {
                return redirect()->route('consumables.bulkcheckout.show')->withInput()->with('error', trans_choice('admin/consumables/message.checkout.error', $consumabl_ids))->withErrors($errors);
            }

            if (!$errors) {
                // Redirect to the new consumables page
                return redirect()->to('consumables')->with('success', trans_choice('admin/consumables/message.checkout.success', $consumabl_ids));
            }
            // Redirect to the consumable management page with error
            return redirect()->route('consumables.bulkcheckout.show')->withInput()->with('error', trans_choice('admin/consumables/message.checkout.error', $consumabl_ids))->withErrors($errors);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('consumables.bulkcheckout.show')->withInput()->with('error', trans_choice('admin/consumables/message.checkout.error', $request->input('selected_assets')));
        }
    }
}
