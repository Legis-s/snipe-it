<?php

namespace App\Http\Controllers\Consumables;

use App\Helpers\Helper;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCheckoutRequest;
use App\Models\Consumable;
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
    public function create(Request $request) : View
    {
        $this->authorize('checkout', Consumable::class);

        $ids = [];
//        if (request()->filled('purchase_id')) {
//            $assets = Company::scopeCompanyables(Asset::select('assets.*'), "company_id", "assets")
//                ->with('location', 'assetstatus', 'assetlog', 'company', 'defaultLoc', 'assignedTo',
//                    'model.category', 'model.manufacturer', 'model.fieldset', 'supplier');
//            $assets->where('assets.purchase_id', '=', request()->input('purchase_id'))->where('status_id', '=', 2)->where('assigned_to', '=', null);
//
//            foreach ($assets->get() as $asset) {
//                array_push($ids, $asset->id);
//            }
//        }


        $do_not_change = ['' => trans('general.do_not_change')];
        $status_label_list = $do_not_change + Helper::deployableStatusLabelList();
        return view('consumables/bulk-checkout', ['selected_consumables' => $ids])->with('statusLabel_list', $status_label_list);
    }

    /**
     * Process Multiple Checkout Request
     */
    public function store(AssetCheckoutRequest $request) : RedirectResponse | ModelNotFoundException
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
            }else{
                return redirect()->route('consumables.bulkcheckout.show')->withInput()->with('error', trans_choice('admin/hardware/message.multi-checkout.error', $consumabl_ids))->withErrors($errors);
            }

            if (! $errors) {
                // Redirect to the new consumables page
                return redirect()->to('consumables')->with('success', trans_choice('admin/hardware/message.multi-checkout.success', $consumabl_ids));
            }
            // Redirect to the consumable management page with error
            return redirect()->route('consumables.bulkcheckout.show')->withInput()->with('error', trans_choice('admin/hardware/message.multi-checkout.error', $consumabl_ids))->withErrors($errors);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('consumables.bulkcheckout.show')->withInput()->with('error', trans_choice('admin/hardware/message.multi-checkout.error', $request->input('selected_assets')));
        }
    }
}
