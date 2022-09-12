<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutNotAllowed;
use App\Helpers\Helper;
use App\Http\Controllers\CheckInOutRequest;
use App\Models\Company;
use App\Models\MassOperation;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Contract;
use App\Models\Location;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Statuslabel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BulkAssetsController extends Controller
{

    use CheckInOutRequest;
    /**
     * Display the bulk edit page.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @return View
     * @internal param int $assetId
     * @since [v2.0]
     */
    public function edit(Request $request)
    {
        $this->authorize('update', Asset::class);

        if (!$request->filled('ids')) {
            return redirect()->back()->with('error', 'No assets selected');
        }

        $asset_ids = array_keys($request->input('ids'));

        if ($request->filled('bulk_actions')) {
            switch($request->input('bulk_actions')) {
                case 'labels':
                    return view('hardware/labels')
                        ->with('assets', Asset::find($asset_ids))
                        ->with('settings', Setting::getSettings())
                        ->with('count', 0);
                case 'delete':
                    $assets = Asset::with('assignedTo', 'location')->find($asset_ids);
                    $assets->each(function ($asset) {
                        $this->authorize('delete', $asset);
                    });
                    return view('hardware/bulk-delete')->with('assets', $assets);
                case 'edit':
                    return view('hardware/bulk')
                        ->with('assets', request('ids'))
                        ->with('statuslabel_list', Helper::statusLabelList());
            }
        }
        return redirect()->back()->with('error', 'No action selected');
    }

    /**
     * Save bulk edits
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @return Redirect
     * @internal param array $assets
     * @since [v2.0]
     */
    public function update(Request $request)
    {
        $this->authorize('update', Asset::class);

        \Log::debug($request->input('ids'));

        if(!$request->filled('ids') || count($request->input('ids')) <= 0) {
            return redirect()->route("hardware.index")->with('warning', trans('No assets selected, so nothing was updated.'));
        }

        $assets = array_keys($request->input('ids'));

        if (($request->filled('purchase_date'))
            || ($request->filled('purchase_cost'))
            || ($request->filled('supplier_id'))
            || ($request->filled('order_number'))
            || ($request->filled('warranty_months'))
            || ($request->filled('rtd_location_id'))
            || ($request->filled('requestable'))
            || ($request->filled('company_id'))
            || ($request->filled('status_id'))
            || ($request->filled('model_id'))
        ) {
            foreach ($assets as $assetId) {
                $this->update_array = [];

                $this->conditionallyAddItem('purchase_date')
                    ->conditionallyAddItem('model_id')
                    ->conditionallyAddItem('order_number')
                    ->conditionallyAddItem('requestable')
                    ->conditionallyAddItem('status_id')
                    ->conditionallyAddItem('supplier_id')
                    ->conditionallyAddItem('warranty_months');

                if ($request->filled('purchase_cost')) {
                    $this->update_array['purchase_cost'] =  Helper::ParseFloat($request->input('purchase_cost'));
                }

                if ($request->filled('company_id')) {
                    $this->update_array['company_id'] =  $request->input('company_id');
                    if ($request->input('company_id')=="clear") {
                        $this->update_array['company_id'] = null;
                    }
                }

                if ($request->filled('rtd_location_id')) {
                    $this->update_array['rtd_location_id'] = $request->input('rtd_location_id');
                    if (($request->filled('update_real_loc')) && (($request->input('update_real_loc')) == '1')) {
                        $this->update_array['location_id'] = $request->input('rtd_location_id');
                    }
                }

                DB::table('assets')
                    ->where('id', $assetId)
                    ->update($this->update_array);
            } // endforeach
            return redirect()->route("hardware.index")->with('success', trans('admin/hardware/message.update.success'));
        // no values given, nothing to update
        }
        return redirect()->route("hardware.index")->with('warning', trans('admin/hardware/message.update.nothing_updated'));

    }

    /**
     * Array to store update data per item
     * @var Array
     */
    private $update_array;
    /**
     * Adds parameter to update array for an item if it exists in request
     * @param  String  $field        field name
     * @return this     Model for Chaining
     */
    protected function conditionallyAddItem($field)
    {
        if(request()->filled($field)) {
            $this->update_array[$field] = request()->input($field);
        }
        return $this;
    }

    /**
     * Save bulk deleted.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @return View
     * @internal param array $assets
     * @since [v2.0]
     */
    public function destroy(Request $request)
    {
        $this->authorize('delete', Asset::class);

        if ($request->filled('ids')) {
            $assets = Asset::find($request->get('ids'));
            foreach ($assets as $asset) {
                $update_array['deleted_at'] = date('Y-m-d H:i:s');
                $update_array['assigned_to'] = null;

                DB::table('assets')
                    ->where('id', $asset->id)
                    ->update($update_array);
            } // endforeach
            return redirect()->to("hardware")->with('success', trans('admin/hardware/message.delete.success'));
            // no values given, nothing to update
        }
        return redirect()->to("hardware")->with('info', trans('admin/hardware/message.delete.nothing_updated'));
    }

    /**
     * Show Bulk Checkout Page
     * @return View View to checkout multiple assets
     */
    public function showCheckout()
    {

        if (request()->filled('purchase_bulk_id')) {
            $assets = Company::scopeCompanyables(Asset::select('assets.*'), "company_id", "assets")
                ->with('location', 'assetstatus', 'assetlog', 'company', 'defaultLoc', 'assignedTo',
                    'model.category', 'model.manufacturer', 'model.fieldset', 'supplier');
            $assets->where('assets.purchase_id', '=', request()->input('purchase_bulk_id'))->where('status_id', '=', 2)->where('assigned_to', '=', null);

            $ids = [];
            foreach ($assets->get() as $asset) {
                array_push($ids, $asset->id);
            }
        }
//        dd($ids);

        $this->authorize('checkout', Asset::class);
        // Filter out assets that are not deployable.

        return view('hardware/bulk-checkout', ['ids' => request()->filled('purchase_bulk_id') ? $ids : []]);
    }
    /**
     * Show Bulk Multiple Sell Page
     * @return View View to sell multiple assets
     */
    public function showSell()
    {
        if (request()->filled('purchase_bulk_id')) {
            $assets = Company::scopeCompanyables(Asset::select('assets.*'), "company_id", "assets")
                ->with('location', 'assetstatus', 'assetlog', 'company', 'defaultLoc', 'assignedTo',
                    'model.category', 'model.manufacturer', 'model.fieldset', 'supplier');
            $assets->where('assets.purchase_id', '=', request()->input('purchase_bulk_id'))->where('status_id', '=', 2)->where('assigned_to', '=', null);

            $ids = [];
            foreach ($assets->get() as $asset) {
                array_push($ids, $asset->id);
            }
        }
        $this->authorize('sell', Asset::class);
        // Filter out assets that are not deployable.
        return view('hardware/bulk-sell', ['ids' => request()->filled('purchase_bulk_id') ? $ids : []]);
    }
    /**
     * Process Multiple Checkout Request
     * @return View
     */
    public function storeCheckout(Request $request)
    {
        try {
            $admin = Auth::user();

            $target = $this->determineCheckoutTarget();

            if (!is_array($request->get('selected_assets'))) {
                return redirect()->route('hardware/bulkcheckout')->withInput()->with('error', trans('admin/hardware/message.checkout.no_assets_selected'));
            }

            $asset_ids = array_filter($request->get('selected_assets'));

            foreach ($asset_ids as $asset_id) {
                if ($target->id == $asset_id && request('checkout_to_type') =='asset') {
                    return redirect()->back()->with('error', 'You cannot check an asset out to itself.');
                }
            }
            $checkout_at = date("Y-m-d H:i:s");
            if (($request->filled('checkout_at')) && ($request->get('checkout_at')!= date("Y-m-d"))) {
                $checkout_at = e($request->get('checkout_at'));
            }

            $expected_checkin = '';

            if ($request->filled('expected_checkin')) {
                $expected_checkin = e($request->get('expected_checkin'));
            }

            $errors = [];
            DB::transaction(function () use ($target, $admin, $checkout_at, $expected_checkin, $errors, $asset_ids, $request) {

                foreach ($asset_ids as $asset_id) {
                    $asset = Asset::findOrFail($asset_id);
                    $this->authorize('checkout', $asset);
                    $error = $asset->checkOut($target, $admin, $checkout_at, $expected_checkin, e($request->get('note')), null);

                    if ($target->location_id!='') {
                        $asset->location_id = $target->location_id;
                        $asset->unsetEventDispatcher();
                        $asset->save();
                    }

                    if ($error) {
                        array_merge_recursive($errors, $asset->getErrors()->toArray());
                    }
                }
            });

            $operation_type = 'checkout';
            $name = "Массовая выдача от " . date('d.m.Y');
            $user_id = Auth::id();
//            $assigned_type = ($request->get('checkout_to_type') == 'user') ? 'App\Models\User' : 'App\Models\Contract';
//            $assigned_to = ($request->get('checkout_to_type') == 'user') ? request('assigned_user') : request('assigned_contract');

            switch ($request->get('checkout_to_type')) {
                case 'location':
                    $assigned_type = 'App\Models\Location';
                    $assigned_to = request('assigned_location');
                    break;
                case 'asset':
                    $assigned_type = 'App\Models\Asset';
                    $assigned_to = request('assigned_asset');
                    break;
                case 'user':
                    $assigned_type = 'App\Models\User';
                    $assigned_to = request('assigned_user');
                    break;
            }


            $contract_id = request('assigned_contract');
            $bitrix_task_id = request('bitrix_task_id');
            $note = request('note');

            DB::transaction(function () use ($operation_type, $name, $user_id, $assigned_type, $assigned_to, $contract_id, $bitrix_task_id, $note, $asset_ids) {
                $mo = new MassOperation();
                $mo->operation_type = $operation_type;
                $mo->name = $name;
                $mo->user_id = $user_id;
                $mo->contract_id = $contract_id;
                $mo->assigned_type = $assigned_type;
                $mo->assigned_to = $assigned_to;
                $mo->bitrix_task_id = $bitrix_task_id;
                $mo->note = $note;
                $mo->save();
                $mo->assets()->attach($asset_ids);
            });

            if (!$errors) {
              // Redirect to the new asset page
                return redirect()->to("hardware")->with('success', trans('admin/hardware/message.checkout.success'));
            }
            // Redirect to the asset management page with error
            return redirect()->to("hardware/bulk-checkout")->with('error', trans('admin/hardware/message.checkout.error'))->withErrors($errors);
        } catch (ModelNotFoundException $e) {
            return redirect()->to("hardware/bulk-checkout")->with('error', $e->getErrors());
        }
    }

    /**
     * Validate and process the form data to check out an asset to a user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param AssetCheckoutRequest $request
     * @param int $assetId
     * @return Redirect
     * @since [v1.0]
     */
    public function sellAssetPost($request, $assetId, $contract_id, $note, $sold_at_req)
    {
        $this->authorize('sell', Asset::class);

            // Check if the asset exists
            if (!$asset = Asset::find($assetId)) {
                return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
            } elseif (!$asset->availableForSell()) {
                return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkout.not_available'));
            }
var_dump("ok");
            $admin_user = Auth::user();
            $sold_at= date("Y-m-d H:i:s");
            $assigned_to = null;
            $assigned_type=null;

            if ($sold_at != date("Y-m-d")) {
                $sold_at = $sold_at_req;
            }

            // если привязываем к юзеру, то пишем юзера и договор, отправляем на выдано
            // если привязываем на договор, то пишем только договор и сразу продаем
            switch (request('sell_to_type')) {
                case 'user':
                    $assigned_to = User::findOrFail(request('assigned_user'));
                    \Debugbar::info($assigned_to);
                    $assigned_type = "App\Models\User";
                    $status = Statuslabel::where('name', 'Выдано')->first();
                    $asset->status_id = $status->id;
                    $asset->contract_id = $contract_id;
                    break;
                case 'contract':
                    $assigned_to = Contract::findOrFail(request('assigned_contract'));
                    \Debugbar::info($assigned_to);
                    $assigned_type = "App\Models\Contract";
                    $asset->contract_id = $contract_id;
                    $status = Statuslabel::where('name', 'Продано')->first();
                    $asset->status_id = $status->id;
                    break;
            }
            $asset->assigned_to=$assigned_to->id;
            $asset->assigned_type=$assigned_type;

            if ($asset->save()) {
                var_dump("___");
                $log = new Actionlog();
                $log->user_id = Auth::id();
                if ($asset->assigned_type== "App\Models\User"){
                    $log->action_type = 'issued_for_sale';
                }
                if ($asset->assigned_type== "App\Models\Contract"){
                    $log->action_type = 'sell';
                }
                $log->target_type = $assigned_type;
                $log->target_id = $assigned_to->id;
                $log->item_id = $asset->id;
                $log->item_type = Asset::class;
                $service_info = json_encode($request->all()); // TODO: хотим хранить эту инфу в бд
                $log->note = $note;
                $log->save();
                $this->ss_count += 1;
            } else {
                return false;
            }




    }

    /**
     * Process Multiple Sell Request
     * @return View
     */
    public function storeSell(Request $request)
    {

        try {
            if(is_null($request->get('sell_to_type'))) {
                return redirect()->route('hardware/bulksell', ['purchase_bulk_id' => $request->get('purchase_bulk_id')])->withInput()->with('error', trans('admin/hardware/message.sell.no_type_selected'));
            }

            if ($request->get('sell_to_type') == 'user') {
                if (is_null($request->get('assigned_user'))) {
                    return redirect()->route('hardware/bulksell', ['purchase_bulk_id' => $request->get('purchase_bulk_id')])->withInput()->with('error', trans('admin/hardware/message.sell.no_user_selected'));
                } else if (is_null($request->get('assigned_contract'))) {
                    return redirect()->route('hardware/bulksell', ['purchase_bulk_id' => $request->get('purchase_bulk_id')])->withInput()->with('error', trans('admin/hardware/message.sell.no_contract_selected'));
                }
            }

            if ($request->get('sell_to_type') == 'contract' && is_null($request->get('assigned_contract'))) {
                    return redirect()->route('hardware/bulksell', ['purchase_bulk_id' => $request->get('purchase_bulk_id')])->withInput()->with('error', trans('admin/hardware/message.sell.no_contract_selected'));
            }

            if (is_null($request->get('sold_at'))) {
                return redirect()->route('hardware/bulksell', ['purchase_bulk_id' => $request->get('purchase_bulk_id')])->withInput()->with('error', trans('admin/hardware/message.sell.no_date_selected'));
            }

            if (!is_array($request->get('selected_assets'))) {
                return redirect()->route('hardware/bulksell', ['purchase_bulk_id' => $request->get('purchase_bulk_id')])->withInput()->with('error', trans('admin/hardware/message.sell.no_assets_selected'));
            }

            $admin = \Illuminate\Support\Facades\Auth::user();
            $target = $this->determineCheckoutTarget();
            $asset_ids = array_filter($request->get('selected_assets'));

            DB::transaction(function () use ($target, $admin, $asset_ids, $request) {
                $note = request('note');
                $contract_id = request('assigned_contract');
                $sold_at = request('sold_at');
                $this->ss_count = 0;

                foreach ($asset_ids as $asset_id) {

                    $this->sellAssetPost($request, $asset_id, $contract_id, $note, $sold_at);
                }

            });

            $operation_type = 'sell';
            $name = "Массовая продажа от " . date('d.m.Y');
            $user_id = Auth::id();
            $assigned_type = ($request->get('sell_to_type') == 'user') ? 'App\Models\User' : 'App\Models\Contract';
            $assigned_to = ($request->get('sell_to_type') == 'user') ? request('assigned_user') : request('assigned_contract');
            $contract_id = request('assigned_contract');
            $bitrix_task_id = request('bitrix_task_id');
            $note = request('note');

//            dd($asset_ids);
            if ($this->ss_count == count($asset_ids)) {
                DB::transaction(function () use ($operation_type, $name, $user_id, $assigned_type, $assigned_to, $contract_id, $bitrix_task_id, $note, $asset_ids) {
                    $mo = new MassOperation();
                    $mo->operation_type = $operation_type;
                    $mo->name = $name;
                    $mo->user_id = $user_id;
                    $mo->contract_id = $contract_id;
                    $mo->assigned_type = $assigned_type;
                    $mo->assigned_to = $assigned_to;
                    $mo->bitrix_task_id = $bitrix_task_id;
                    $mo->note = $note;
                    $mo->save();
                    $mo->assets()->attach($asset_ids);
                });
                if (request('sell_to_type') == 'user') {
                    return redirect()->to("hardware/bulksell")->with('success', trans('admin/hardware/message.sell.success_user'));
                } else if (request('sell_to_type') == 'contract') {
                    return redirect()->to("hardware/bulksell")->with('success', trans('admin/hardware/message.sell.success_contract'));
                }
            }

        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', trans('admin/hardware/message.sell.error'))->withErrors();
        } catch (SaleNotAllowed $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    protected function renderForConsole($e)
    {
        $this->getExceptionHandler()->renderForConsole(new ConsoleOutput, $e);
    }
}
