<?php


namespace App\Http\Controllers;

use App\Helpers\Helper;
use App\Http\Requests\FileUploadRequest;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Purchase;
use App\Models\Location;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Facebook\WebDriver\AbstractWebDriverCheckboxOrRadio;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use function Livewire\str;

class PurchasesController extends Controller
{
    public function index()
    {

        $purchases_d = DB::table('purchases')
            ->select('user_id')
            ->distinct()
            ->get();
        $ids = [];
        foreach ($purchases_d as &$value) {
            array_push($ids, $value->user_id);
        }

        $purchases_s = DB::table('purchases')
            ->select('supplier_id')
            ->distinct()
            ->get();
        $ids_s = [];
        foreach ($purchases_s as &$value) {
            array_push($ids_s, $value->supplier_id);
        }

        $users = User::find($ids);
        $suppliers = Supplier::find($ids_s);
        $this->authorize('view', Location::class);
       return view('purchases/index', compact('users','suppliers'));
    }

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the locations detail page.
     * @param int $inventoryId
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     */
    public function show($purchaseId = null)
    {
        $this->authorize('view', Asset::class);

        $purchase = Purchase::find($purchaseId);
        $old = false;
        if (isset($purchase->id)) {
            $consumables_json = $purchase->consumables_json;
            $consumables = json_decode($consumables_json, true);
            if (count($consumables)>0 && isset($consumables[0]["category_id"])){
                $old = true;
            }
            return view('purchases/view', compact('purchase',"old"));
        }

        return redirect()->route('purchases.index')->with('error', trans('admin/locations/message.does_not_exist'));
    }


    /**
     * Returns a form view used to create a new location.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see PurchasesController::postCreate() method that validates and stores the data
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $this->authorize('create', Asset::class);
        return view('purchases/edit')
            ->with('item', new Purchase);
    }

    /**
     * Validates and stores a new location.
     * @see PurchasesController::getCreate() method that makes the form
     * @since [v1.0]
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(FileUploadRequest $request)
    {
        $this->authorize('create', Purchase::class);
        $purchase = new Purchase();
        $purchase->invoice_number      = $request->input('invoice_number');
        $purchase->final_price         = $request->input('final_price');
        $purchase->supplier_id         = $request->input('supplier_id');
        $purchase->legal_person_id     = $request->input('legal_person_id');
        $purchase->invoice_type_id     = $request->input('invoice_type_id');
        $purchase->comment             = $request->input('comment');
        $purchase->consumables_json    = $request->input('consumables');
        $purchase->assets_json         = $request->input('assets');
        $purchase->delivery_cost       = $request->input('delivery_cost');
        $purchase->user_id             = Auth::id();
        $currency_id = $request->input('currency_id');
        $purchase->setStatusInprogress();

        switch ($currency_id) {
            case 341:
                $purchase->currency = "руб";
                break;
            case 342:
                $purchase->currency = "usd";
                break;
            case 343:
                $purchase->currency = "eur";
                break;
        }
        $assets = json_decode($request->input('assets'), true);
        $consumables = json_decode($request->input('consumables'), true);
        $purchase = $request->handleFile($purchase, public_path().'/uploads/purchases');


        $status = Statuslabel::where('name', 'В закупке')->first();
        $settings = \App\Models\Setting::getSettings();
        if ($purchase->save()) {
            $data_list = "";
            if (count($assets)>0) {
                $asset_tag = Asset::autoincrement_asset();
                $data_list .= "Активы:"."\n";
                foreach ($assets as &$value) {
                    $model= $value["model"];
                    $model_id = $value["model_id"];
                    $purchase_cost = $value["purchase_cost"];
                    $nds = $value["nds"];
                    $warranty = $value["warranty"];
                    $quantity = $value["quantity"];
                    $location_id = null;
                    if (isset($value["location_id"]) && $value["location_id"]>0) {
                        $location_id = $value["location_id"];
                    }
                    $data_list .= "[".$value["id"]."] ".$model." - Количество: ".$quantity." Цена: ".$purchase_cost."\n";

                    $dt = new DateTime();
                    for ($i = 1; $i <= $quantity; $i++) {
                        $asset = new Asset();
                        $asset->model()->associate(AssetModel::find((int) $model_id));
                        $asset->asset_tag               = $asset_tag;
                        $asset->model_id                = $model_id;
                        $asset->order_number            = $purchase->invoice_number;
                        $asset->archived                = '0';
                        $asset->physical                = '1';
                        $asset->depreciate              = '0';
                        $asset->quality                 = 5;
                        $asset->status_id               = $status->id;
                        $asset->warranty_months         = $warranty;
                        $asset->purchase_cost           = $purchase_cost;
                        $asset->nds                     = $nds;
                        $asset->purchase_date           = $dt->format('Y-m-d H:i:s');
                        $asset->supplier_id             = $purchase->supplier_id;
                        $asset->purchase_id             = $purchase->id;
                        $asset->user_id                 = Auth::id();
                        $asset->location_id            = $location_id;


                        if (! empty($settings->audit_interval)) {
                            $asset->next_audit_date = Carbon::now()->addMonths($settings->audit_interval)->toDateString();
                        }

                        if($asset->save()){
                            if ($settings->zerofill_count > 0) {
                                $asset_tag_digits = preg_replace('/\D/', '', $asset_tag);
                                $asset_tag = preg_replace('/^0*/', '', $asset_tag_digits);
                                $asset_tag++;
                                $asset_tag =  $settings->auto_increment_prefix.Asset::zerofill($asset_tag, $settings->zerofill_count);
                            }else{
                                $asset_tag = $settings->auto_increment_prefix.$asset_tag;
                            }
                        }else{
                            \Debugbar::info($consumables);
                        }
                    }
                }
                $data_list .="\n";
            }
            if (count($consumables)>0) {
                $data_list .= "Компоненты:"."\n";
                foreach ($consumables as &$consumable) {
                    $consumable_name= $consumable["consumable"];
                    $consumable_id = $consumable["consumable_id"];
                    $purchase_cost = $consumable["purchase_cost"];
                    $quantity = $consumable["quantity"];
                    $data_list .= "[".$consumable_id."] ".$consumable_name." - Количество: ".$quantity." Цена: ".$purchase_cost."\n";
                }
            }


            if ($purchase->delivery_cost > 0) {
                $data_list .= "Стоимость доставки:  ". $purchase->delivery_cost;
            }

            $file_data = file_get_contents(public_path().'/uploads/purchases/'.$purchase->invoice_file);

            // Encode the image string data into base64
            $file_data_base64 = base64_encode($file_data);

            $user =  Auth::user();
            /** @var \GuzzleHttp\Client $client */
            $client = new \GuzzleHttp\Client();
            $params = [
                'headers' => [
                    'Content-Type' => 'multipart/form-data',
                ],
                'form_params' => [
                    "IBLOCK_TYPE_ID" => "lists",
                    "IBLOCK_ID" => 52,
                    "ELEMENT_CODE" =>"warehouse_buy_".$purchase->id,
                    "FIELDS[NAME]" => $purchase->invoice_number,
                    "FIELDS[CREATED_BY]" => $user->bitrix_id,
                    "FIELDS[PROPERTY_758]" => $purchase->invoice_type->bitrix_id, // тип платежа
                    "FIELDS[PROPERTY_141]" => $purchase->comment , //описание
                    "FIELDS[PROPERTY_142]" => $purchase->final_price , //сумма
                    "FIELDS[PROPERTY_156]" => $currency_id , //валюта
                    "FIELDS[PROPERTY_790]" => $purchase->legal_person->bitrix_id , //Юр. лицо
                    "FIELDS[PROPERTY_158]" => $purchase->supplier->name , //Поставщик название
                    "FIELDS[PROPERTY_824]" => $purchase->supplier->bitrix_id , //Поставщик bitrix_id
                    "FIELDS[PROPERTY_143][0]" => $purchase->invoice_file , //файл имя
                    "FIELDS[PROPERTY_143][1]" => $file_data_base64 , //файл base64
                    "FIELDS[PROPERTY_1132]" => $data_list , // что покупаем
                    "FIELDS[PROPERTY_1134]" => $purchase->id."" , //id заказа
                    "FIELDS[PROPERTY_1120]" => "1" , //покупка из системы

                    //2. На боевом есть еще одно поле PROPERTY_1120=Y - надо передать любое значение, означает что создана из snipe_it
                ]
            ];
            $params_json = json_encode($params);
            file_put_contents(public_path().'/uploads/purchases/'.$purchase->id.'.json', $params_json);

            $purchase->bitrix_send_json = $purchase->id.'.json';
            $purchase->save();

//            $response = $client->request('POST', 'https://bitrixdev.legis-s.ru/rest/1/lp06vc4xgkxjbo3t/lists.element.add.json/',$params);

            \Debugbar::info("send bitrix");
            if ($user->bitrix_token && $user->bitrix_id){
                $raw_bitrix_token  = Crypt::decryptString($user->bitrix_token);
                $response = $client->request('POST', 'https://bitrix.legis-s.ru/rest/'.$user->bitrix_id.'/'.$raw_bitrix_token.'/lists.element.add.json/',$params);

            }else{
                $response = $client->request('POST', 'https://bitrix.legis-s.ru/rest/722/q7e6fc3qrkiok64x/lists.element.add.json/',$params);

            }
            \Debugbar::info("cant Crypt ");
            $response = $response->getBody()->getContents();

            $purchase->bitrix_result = $response;
            $bitrix_result = json_decode($response, true);
            $bitrix_id = $bitrix_result["result"];
            $purchase->bitrix_id = $bitrix_id;
            $purchase->save();
            return redirect()->route("purchases.index")->with('success', trans('admin/locations/message.create.success'));
        }
        return redirect()->back()->withInput()->withErrors($purchase->getErrors());
    }


    /**
     * Makes a form view to edit location information.
     *
     * @see LocationsController::postCreate() method that validates and stores
     * @param int $purchaseId
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     */
    public function edit($purchaseId = null)
    {
        $this->authorize('update', Purchase::class);
        // Check if the location exists
        if (is_null($item = Purchase::find($purchaseId))) {
            return redirect()->route('purchases.index')->with('error', trans('admin/locations/message.does_not_exist'));
        }


        return view('purchases/edit', compact('item'));
    }


    /**
     * Returns a view that presents a form to clone purchase.
     * @param int $purchaseId
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     */
    public function getClone($purchaseId = null)
    {
        // Check if the asset exists
        if (is_null($purchase_to_clone = Purchase::find($purchaseId))) {
            // Redirect to the asset management page
            return redirect()->route('purchases.index')->with('error', trans('admin/hardware/message.does_not_exist'));
        }

        $this->authorize('create', $purchase_to_clone);

        $purchase = clone $purchase_to_clone;
        $purchase->id = null;

        return view('purchases/edit')
            ->with('item', $purchase);
    }



    /**
     * Validates and deletes selected purchase.
     *
     * @param int $purchaseId
     * @since [v1.0]
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy($purchaseId)
    {
        $this->authorize('delete', Purchase::class);

        if (is_null($purchase = Purchase::find($purchaseId))) {
            return redirect()->to(route('purchases.index'))->with('error', trans('admin/locations/message.not_found'));
        }
        if ($purchase->status == Purchase::REJECTED){
            $assets = Asset::where('purchase_id', $purchase->id)->get();
            foreach ($assets as &$value) {
                $value->unsetEventDispatcher();
                $value->forceDelete();
            }
            $purchase->delete();
        }else{
            return redirect()->to(route('purchases.index'))->with('error', "Нельзя удалить");
        }


        return redirect()->to(route('purchases.index'))->with('success', trans('admin/locations/message.delete.success'));
    }


    /**
     * Returns a view that presents a form to clone purchase.
     * @param int $purchaseId
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     */
    public function deleteAllRejected()
    {
//        $this->authorize('delete', Purchase::class);

        $purchases = Purchase::with('assets')->where("status",Purchase::REJECTED)->get();
        foreach ($purchases as &$purchase) {
            $pas = $purchase->assets;
            foreach($pas as &$pa){
                $pa->unsetEventDispatcher();
                $pa->forceDelete();
            }
            $purchase->delete();
        }
        $total = $purchases->count();
        return redirect()->to(route('purchases.index'))->with('success',"Dell ".$total);
    }


}