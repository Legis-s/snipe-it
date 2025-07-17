<?php

namespace App\Http\Controllers\Consumables;

use App\Helpers\Helper;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Consumable;
use App\Models\Statuslabel;
use App\Models\Setting;
use App\View\Label;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\AssetCheckoutRequest;
use App\Models\CustomField;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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
            $admin = auth()->user();

            $target = $this->determineCheckoutTarget();

            if (! is_array($request->get('selected_assets'))) {
                return redirect()->route('hardware.bulkcheckout.show')->withInput()->with('error', trans('admin/hardware/message.checkout.no_assets_selected'));
            }

            $asset_ids = array_filter($request->get('selected_assets'));

            $assets = Asset::findOrFail($asset_ids);

            if (request('checkout_to_type') == 'asset') {
                foreach ($asset_ids as $asset_id) {
                    if ($target->id == $asset_id) {
                        return redirect()->back()->with('error', 'You cannot check an asset out to itself.');
                    }
                }
            }
            $checkout_at = date('Y-m-d H:i:s');
            if (($request->filled('checkout_at')) && ($request->get('checkout_at') != date('Y-m-d'))) {
                $checkout_at = $request->get('checkout_at');
            }

            $expected_checkin = '';

            if ($request->filled('expected_checkin')) {
                $expected_checkin = $request->get('expected_checkin');
            }

            $errors = [];
            DB::transaction(function () use ($target, $admin, $checkout_at, $expected_checkin, &$errors, $assets, $request) { //NOTE: $errors is passsed by reference!
                foreach ($assets as $asset) {
                    $this->authorize('checkout', $asset);

                    // See if there is a status label passed
                    if ($request->filled('status_id')) {
                        $asset->status_id = $request->get('status_id');
                    }

                    $checkout_success = $asset->checkOut($target, $admin, $checkout_at, $expected_checkin, e($request->get('note')), $asset->name, null);

                    //TODO - I think this logic is duplicated in the checkOut method?
                    if ($target->location_id != '') {
                        $asset->location_id = $target->location_id;
                        // TODO - I don't know why this is being saved without events
                        $asset::withoutEvents(function () use ($asset) {
                            $asset->save();
                        });
                    }

                    if (!$checkout_success) {
                        $errors = array_merge_recursive($errors, $asset->getErrors()->toArray());
                    }
                }
            });

            if (! $errors) {
                // Redirect to the new asset page
                return redirect()->to('consumables')->with('success', trans_choice('admin/hardware/message.multi-checkout.success', $asset_ids));
            }
            // Redirect to the asset management page with error
            return redirect()->route('consumables.bulkcheckout.show')->withInput()->with('error', trans_choice('admin/hardware/message.multi-checkout.error', $asset_ids))->withErrors($errors);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('consumables.bulkcheckout.show')->withInput()->with('error', trans_choice('admin/hardware/message.multi-checkout.error', $request->input('selected_assets')));
        }
        
    }
}
