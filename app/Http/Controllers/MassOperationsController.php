<?php

namespace App\Http\Controllers;

use App\Events\CheckoutableCheckedIn;
use App\Events\CheckoutableCheckedOut;
use App\Events\CheckoutableSell;
use App\Http\Requests\AssetCheckoutRequest;
use App\Http\Requests\AssetSellRequest;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Consumable;
use App\Models\ConsumableAssignment;
use App\Models\Contract;
use App\Models\MassOperation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MassOperationsController extends Controller
{
    use CheckInOutRequest;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->authorize('checkout', Asset::class);
        return view('massoperations/index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($massoperationId)
    {
        $this->authorize('checkout', Asset::class);
        $mass_operation = MassOperation::find($massoperationId);
        $assets = $mass_operation->assets;
        return view('massoperations/view', ['massoperation' => MassOperation::find($massoperationId), 'assets' => $assets]);;
    }





    /**
     * Show Bulk Checkout Page
     * @return View View to checkout multiple assets
     */
    public function showCheckout()
    {

        $this->authorize('checkout', Asset::class);

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

        return view('massoperations/bulk-checkout', ['ids' => request()->filled('purchase_bulk_id') ? $ids : []]);
    }


    /**
     * Process Multiple Checkout Request
     * @return View
     */
    public function storeCheckout(AssetCheckoutRequest $request)
    {

        $this->authorize('checkout', Asset::class);

        try {
            $admin = Auth::user();

            $target = $this->determineCheckoutTarget();

            if (! is_array($request->get('selected_assets')) and ! is_array($request->get('selected_consumables'))) {
                return redirect()->route('bulk.checkout.show')->withInput()->with('error', trans('admin/hardware/message.checkout.no_assets_selected'));
            }
            $asset_ids=[];
            if (is_array($request->get('selected_assets')) and count($request->get('selected_assets'))>0){
                $asset_ids = array_filter($request->get('selected_assets'));
            }

            if (request('checkout_to_type') == 'asset') {
                foreach ($asset_ids as $asset_id) {
                    if ($target->id == $asset_id) {
                        return redirect()->back()->with('error', 'You cannot check an asset out to itself.');
                    }
                }
            }

            $consumbales_post  = is_array($request->get('selected_consumables')) ? array_filter($request->get('selected_consumables')) : [];
            $consumbales_data = [];
            foreach ($consumbales_post as $consumbale) {
                array_push($consumbales_data, explode(":", $consumbale));
            }
            $consumbales_ids = [];
            foreach ($consumbales_post as $consumbale) {
                array_push($consumbales_ids, explode(":", $consumbale)[0]);
            }


            if (request('checkout_to_type') == 'asset') {
                foreach ($asset_ids as $asset_id) {
                    if ($target->id == $asset_id) {
                        return redirect()->back()->with('error', 'You cannot check an asset out to itself.');
                    }
                }
            }

            $checkout_at = date('Y-m-d H:i:s');
            if (($request->filled('checkout_at')) && ($request->get('checkout_at') != date('Y-m-d'))) {
                $checkout_at = e($request->get('checkout_at'));
            }

            $expected_checkin = '';

            if ($request->filled('expected_checkin')) {
                $expected_checkin = e($request->get('expected_checkin'));
            }

            $errors = [];
            DB::transaction(function () use ($target, $admin, $checkout_at, $expected_checkin, $errors, $asset_ids,$consumbales_ids,$consumbales_data, $request) {
                foreach ($asset_ids as $asset_id) {
                    $asset = Asset::findOrFail($asset_id);
                    $this->authorize('checkout', $asset);

                    $error = $asset->checkOut($target, $admin, $checkout_at, $expected_checkin, e($request->get('note')), $asset->name);

                    if ($target->location_id != '') {
                        $asset->location_id = $target->location_id;
                        $asset->unsetEventDispatcher();
                        $asset->save();
                    }

                    if ($error) {
                        array_merge_recursive($errors, $asset->getErrors()->toArray());
                    }
                }

                $consumbales_assigned_ids = [];

                foreach ($consumbales_data as $consumbale_item) {
                    $consumable = Consumable::findOrFail($consumbale_item[0]);
                    $this->authorize('checkout', $consumable);

                    $ca = new ConsumableAssignment();
                    $ca->consumable_id= $consumable->id;
                    $ca->user_id= $admin->id;
                    $ca->quantity= $consumbale_item[1];
                    $ca->comment= e($request->get('note'));
                    $ca->cost= $consumable->purchase_cost;
                    $ca->type= ConsumableAssignment::ISSUED;
                    $ca->assigned_to= $target->id;
                    $ca->assigned_type= get_class($target);
                    $ca->save();
                    $consumbales_assigned_ids[]=$ca->id;

                    event(new CheckoutableCheckedOut($consumable, $target, $admin,e($request->get('note'))));
                }

                $bitrix_task_id = intval($request->get('bitrix_task_id'));
                $mo = new MassOperation();
                $mo->operation_type = 'checkout';
                $mo->name =  "Массовая выдача от " . date('d.m.Y');
                $mo->user_id =  Auth::id();
                $mo->assignedTo()->associate($target);
                if ($bitrix_task_id>0){
                    $mo->bitrix_task_id =  $bitrix_task_id;
                }
                $mo->note =  e($request->get('note'));
                $mo->save();
                $mo->assets()->attach($asset_ids);
                $mo->consumables()->attach($consumbales_ids);
                $mo->consumables_assigments()->attach($consumbales_assigned_ids);

            });


            if (! $errors) {
                // Redirect to the new asset page
                return redirect()->to('bulk')->with('success', trans('admin/hardware/message.checkout.success'));
            }
            // Redirect to the asset management page with error
            return redirect()->route('bulk.checkout.show')->with('error', trans('admin/hardware/message.checkout.error'))->withErrors($errors);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('bulk.checkout.show')->with('error', $e->getErrors());
        }
    }


    /**
     * Show Bulk Multiple Sell Page
     * @return View View to sell multiple assets
     */
    public function showSell()
    {

        $this->authorize('checkout', Asset::class);

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
        // Filter out assets that are not deployable.
        return view('massoperations/bulk-sell', ['ids' => request()->filled('purchase_bulk_id') ? $ids : []]);
    }

    /**
     * Process Multiple Checkout Request
     * @return View
     */
    public function storeSell(AssetSellRequest $request)
    {

        $this->authorize('checkout', Asset::class);

        try {
            $admin = Auth::user();

            $target = Contract::findOrFail(request('assigned_contract'));

            if (! is_array($request->get('selected_assets')) and ! is_array($request->get('selected_consumables'))) {
                return redirect()->route('bulk.sell.show')->withInput()->with('error', trans('admin/hardware/message.checkout.no_assets_selected'));
            }

            $asset_ids=[];
            if (is_array($request->get('selected_assets')) and count($request->get('selected_assets'))>0){
                $asset_ids = array_filter($request->get('selected_assets'));
            }

            $consumbales_post  = is_array($request->get('selected_consumables')) ? array_filter($request->get('selected_consumables')) : [];
            $consumbales_data = [];
            foreach ($consumbales_post as $consumbale) {
                array_push($consumbales_data, explode(":", $consumbale));
            }
            $consumbales_ids = [];
            foreach ($consumbales_post as $consumbale) {
                array_push($consumbales_ids, explode(":", $consumbale)[0]);
            }

            $checkout_at = date('Y-m-d H:i:s');
            if (($request->filled('checkout_at')) && ($request->get('checkout_at') != date('Y-m-d'))) {
                $checkout_at = $request->get('checkout_at');
            }

            $expected_checkin = '';

            $errors = [];

            DB::transaction(function () use ($target, $admin, $checkout_at, $expected_checkin, $errors, $asset_ids,$consumbales_ids,$consumbales_data, $request) {
                $consumbales_assigned_ids = [];

                foreach ($asset_ids as $asset_id) {
                    $asset = Asset::findOrFail($asset_id);
                    $this->authorize('checkout', $asset);
                    $error = $asset->sell($target, $admin, $checkout_at, e($request->get('note')), $request->get('name'));

                    if ($error) {
                        array_merge_recursive($errors, $asset->getErrors()->toArray());
                    }
                }

                foreach ($consumbales_data as $consumbale_item) {
                    $consumable = Consumable::findOrFail($consumbale_item[0]);
                    $this->authorize('checkout', $consumable);

                    if (get_class($target) == Contract::class){
                        $contract_id = Contract::findOrFail(request('assigned_contract'))->id;
                    }

                    $ca = new ConsumableAssignment();
                    $ca->consumable_id= $consumable->id;
                    $ca->user_id= $admin->id;
                    $ca->quantity= $consumbale_item[1];
                    $ca->comment= e($request->get('note'));
                    $ca->cost= $consumable->purchase_cost;
                    $ca->type= ConsumableAssignment::SOLD;
                    $ca->assigned_to= $target->id;
                    $ca->assigned_type= get_class($target);
                    $ca->contract_id= $contract_id;
                    $ca->save();

                    $consumbales_assigned_ids[]=$ca->id;

                    event(new CheckoutableSell($consumable, $target, $admin,e($request->get('note'))));
                }

                $bitrix_task_id = intval($request->get('bitrix_task_id'));
                $mo = new MassOperation();
                $mo->operation_type = 'sell';
                $mo->name =  "Массовая продажа от " . date('d.m.Y');
                $mo->user_id =  Auth::id();
                $mo->assignedTo()->associate($target);
                if ($bitrix_task_id>0){
                    $mo->bitrix_task_id =  $bitrix_task_id;
                }
                $mo->note =  e($request->get('note'));
                $mo->save();
                $mo->assets()->attach($asset_ids);
                $mo->consumables()->attach($consumbales_ids);
                $mo->consumables_assigments()->attach($consumbales_assigned_ids);
            });

            if (! $errors) {
                // Redirect to the new asset page
                return redirect()->to('bulk')->with('success', trans('admin/hardware/message.checkout.success'));
            }
            // Redirect to the asset management page with error
            return redirect()->route('bulk.sell.show')->with('error', trans('admin/hardware/message.checkout.error'))->withErrors($errors);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('bulk.sell.show')->with('error', $e->getErrors());
        }
    }


    /**
     * Show Bulk Multiple Checkin Page
     * @return View View to checkin multiple assets
     */
    public function showCheckin()
    {
        $this->authorize('checkin', Asset::class);
        if (request()->filled('purchase_bulk_id')) {
            $assets = Company::scopeCompanyables(Asset::select('assets.*'), "company_id", "assets")
                ->with('location', 'assetstatus', 'assetlog', 'company', 'defaultLoc', 'assignedTo',
                    'model.category', 'model.manufacturer', 'model.fieldset', 'supplier');
            $assets->where('assets.purchase_id', '=', request()->input('purchase_bulk_id'))->where('status_id', '=', 2)->where('assigned_to', '!=', null);

            $ids = [];
            foreach ($assets->get() as $asset) {
                array_push($ids, $asset->id);
            }
        }
        // Filter out assets that are not deployable.
        return view('massoperations/bulk-checkin'
            , ['ids' => request()->filled('purchase_bulk_id') ? $ids : []]
        );
    }


    /**
     * Process Multiple Checkin Request
     * @return View
     */
    public function storeCheckin(Request $request)
    {
        $this->authorize('checkin', Asset::class);
        try {
            $admin = Auth::user();

            if (!is_array($request->get('selected_assets'))) {
                return redirect()->route('bulk.checkout.show')->withInput()->with('error', trans('admin/hardware/message.checkout.no_assets_selected'));
            }
            \Debugbar::info("selected_assets ok ");

            $asset_ids = array_filter($request->get('selected_assets'));
            $bitrix_task_id = intval($request->get('bitrix_task_id'));
            $location_id = null;
            if ($request->filled('location_id')) {
                $location_id = e($request->get('location_id'));
                $location = \App\Models\Location::findOrFail($location_id);
                $target = $location;
            }

            $note = e($request->get('note'));
            $checkin_at = date("Y-m-d H:i:s");
            if (($request->filled('checkin_at')) && ($request->get('checkin_at')!= date("Y-m-d"))) {
                $checkin_at = e($request->get('checkin_at'));
            }

            foreach ($asset_ids as $asset_id) {
                $asset = Asset::findOrFail($asset_id);
                if (is_null($target = $asset->assignedTo)) {
                    return redirect()->route('bulk.index')->with('error', trans('admin/hardware/message.checkin.already_checked_in'));
                }
            }

            $errors = [];
            DB::transaction(function () use ($target, $admin, $checkin_at,$location_id,$note, $errors, $asset_ids, $request) {
                foreach ($asset_ids as $asset_id) {
                    $asset = Asset::findOrFail($asset_id);
                    $this->authorize('checkin', $asset);

                    $asset->expected_checkin = null;
                    $asset->last_checkout = null;
                    $asset->assigned_to = null;
                    $asset->assignedTo()->disassociate($asset);
                    $asset->assigned_type = null;
                    $asset->accepted = null;
                    if ($request->filled('status_id')) {
                        $asset->status_id = e($request->get('status_id'));
                    }
                    if ($asset->rtd_location_id == '0') {
                        $asset->rtd_location_id = '';
                    }

                    if ($asset->location_id == '0') {
                        $asset->location_id = '';
                    }
                    $asset->location_id = $asset->rtd_location_id;

                    if ($request->filled('location_id')) {
                        $asset->location_id = $location_id;
                        $asset->rtd_location_id = $location_id;
                    }
                    if(!empty($asset->licenseseats->all())){
                        foreach ($asset->licenseseats as $seat){
                            $seat->assigned_to = null;
                            $seat->save();
                        }
                    }
                    $changed = [];

                    foreach ($asset->getRawOriginal() as $key => $value) {
                        if ($asset->getRawOriginal()[$key] != $asset->getAttributes()[$key]) {
                            $changed[$key]['old'] = $asset->getRawOriginal()[$key];
                            $changed[$key]['new'] = $asset->getAttributes()[$key];
                        }
                    }

                    $error = $asset->save();
                    event(new CheckoutableCheckedIn($asset, $target, $admin, $note, $checkin_at,$changed));

                    if ($error) {
                        array_merge_recursive($errors, $asset->getErrors()->toArray());
                    }
                }
            });


            $mo = new MassOperation();
            $mo->operation_type = 'checkin';
            $mo->name =  "Массовый возврат от " . date('d.m.Y');
            $mo->user_id =  Auth::id();
            $mo->assignedTo()->associate($target);
            if ($bitrix_task_id>0){
                $mo->bitrix_task_id =  $bitrix_task_id;
            }
            $mo->note =  $note;
            $mo->save();
            $mo->assets()->attach($asset_ids);


            if (! $errors) {
                // Redirect to the new asset page
                return redirect()->to('bulk')->with('success', trans('admin/hardware/message.checkout.success'));
            }
            // Redirect to the asset management page with error
            // Redirect to the asset management page with error
            return redirect()->route('bulk.checkout.show')->with('error', trans('admin/hardware/message.checkout.error'))->withErrors($errors);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('bulk.checkout.show')->with('error', $e->getErrors());
        }
    }


}
