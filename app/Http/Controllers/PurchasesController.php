<?php

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Location;
use App\Models\Purchase;
use App\Models\Statuslabel;
use App\Models\Supplier;
use App\Models\User;
use Carbon\Carbon;
use DateTime;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class PurchasesController extends Controller
{
    public function index(): View
    {

        $purchases_d = DB::table('purchases')
            ->select('created_by')
            ->distinct()
            ->get();
        $ids = [];
        foreach ($purchases_d as &$value) {
            array_push($ids, $value->created_by);
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
        return view('purchases/index', compact('users', 'suppliers'));
    }

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the locations detail page.
     * @param int $purchaseId
     */
    public function show(Purchase $purchase): View|RedirectResponse
    {
        $this->authorize('view', Asset::class);

        $purchase = Purchase::find($purchase->id);
        $old = false;
        if (isset($purchase->id)) {
            $consumables_json = $purchase->consumables_json;
            $consumables = json_decode($consumables_json, true);
            if (count($consumables) > 0 && isset($consumables[0]["category_id"])) {
                $old = true;
            }
            return view('purchases/view', compact('purchase', "old"));
        }

        return redirect()->route('purchases.index')->with('error', trans('admin/locations/message.does_not_exist'));
    }


    /**
     * Returns a form view used to create a new purchase.
     * @see PurchasesController::postCreate() method that validates and stores the data
     */
    public function create(): View
    {
        $this->authorize('create', Purchase::class);
        return view('purchases/edit')
            ->with('item', new Purchase);
//            ->with('depreciation_list', Helper::depreciationList());
    }

    /**
     * Validates and stores a new purchase.
     * @see PurchasesController::getCreate() method that makes the form
     */
    public function store(FileUploadRequest $request): RedirectResponse
    {
        $this->authorize('create', Purchase::class);

        $data_list = "";
        $consumables = [];
        if ($request->filled('consumables')) {
            $consumables = json_decode($request->input('consumables'), true);
        }

        if (count($consumables) > 0) {
            $data_list .= "Компоненты:" . "\n";
            foreach ($consumables as &$consumable) {
                $consumable_name = $consumable["consumable"];
                $consumable_id = $consumable["consumable_id"];
                $purchase_cost = $consumable["purchase_cost"];
                $quantity = $consumable["quantity"];
                $data_list .= "[" . $consumable_id . "] " . $consumable_name . " - Количество: " . $quantity . " Цена: " . $purchase_cost . "\n";
            }
        }
        $consumables_sorted = [];
        if (count($consumables) > 0) {
            foreach ($consumables as &$consumable) {
                $consumable_id = $consumable["consumable_id"];
                $need_add = true;
                foreach ($consumables_sorted as &$cons_sorted) {
                    if ($consumable_id == $cons_sorted["consumable_id"]) {
                        $need_add = false;
                        $cons_sorted["quantity"] = $consumable["quantity"] + $cons_sorted["quantity"];
                    }
                }
                if ($need_add) {
                    array_push($consumables_sorted, $consumable);
                }
            }
        }
        $consumables = json_encode($consumables_sorted, JSON_UNESCAPED_UNICODE);


        $purchase = new Purchase();
        $purchase->invoice_number = $request->input('invoice_number');
        $purchase->final_price = $request->input('final_price');
        $purchase->supplier_id = $request->input('supplier_id');
        $purchase->legal_person_id = $request->input('legal_person_id');
        $purchase->invoice_type_id = $request->input('invoice_type_id');
        $purchase->comment = $request->input('comment');
        $purchase->consumables_json = $consumables;
        $purchase->assets_json = $request->input('assets');
        $purchase->delivery_cost = $request->input('delivery_cost');
        $purchase->created_by = auth()->id();
        $purchase->currency = "руб";
        $purchase->setStatusInprogress();

        $assets = [];
        if ($request->filled('assets')) {
            $assets = json_decode($request->input('assets'), true);
        }

        $purchase = $request->handleFile($purchase, public_path() . '/uploads/purchases');

        $status = Statuslabel::where('name', 'В закупке')->first();
        if ($purchase->save()) {
            if (count($assets) > 0) {
                $data_list .= "Активы:" . "\n";
                foreach ($assets as &$value) {
                    \Debugbar::info("foreach json asset");
                    \Debugbar::info($value);
                    $model = $value["model"];
                    $model_id = $value["model_id"];
                    $purchase_cost = $value["purchase_cost"];
                    $nds = $value["nds"];
                    $warranty = $value["warranty"];
                    $quantity = $value["quantity"];
                    $location_id = null;
                    if (isset($value["location_id"]) && $value["location_id"] > 0) {
                        $location_id = $value["location_id"];
                    }
                    $data_list .= "[" . $value["id"] . "] " . $model . " - Количество: " . $quantity . " Цена: " . $purchase_cost . "\n";

                    $dt = new DateTime();
                    for ($i = 1; $i <= $quantity; $i++) {
                        $asset_tag = Asset::autoincrement_asset();
                        \Debugbar::info("NEW asset_tag");
                        \Debugbar::info($asset_tag);
                        $asset = new Asset();
                        $asset->model()->associate(AssetModel::find((int) $model_id));
                        $asset->asset_tag =  $asset_tag;
                        $asset->model_id = $model_id;
                        $asset->order_number = $purchase->invoice_number;
                        $asset->archived = '0';
                        $asset->physical = '1';
                        $asset->depreciate = '0';
                        $asset->quality = 5;
                        $asset->status_id = $status->id;
                        $asset->warranty_months = $warranty;
                        $asset->purchase_cost = $purchase_cost;
                        $asset->nds = $nds;
                        $asset->purchase_date = $dt->format('YYYY-MM-DD');
                        $asset->supplier_id = $purchase->supplier_id;
                        $asset->purchase_id = $purchase->id;
                        $asset->created_by    = auth()->id();
                        $asset->location_id = $location_id;


                        if ($asset->isValid() && $asset->save()) {
//                            if ($settings->zerofill_count > 0) {
//                                $asset_tag_digits = preg_replace('/\D/', '', $asset_tag);
//                                $asset_tag = preg_replace('/^0*/', '', $asset_tag_digits);
//                                $asset_tag++;
//                                $asset_tag = $settings->auto_increment_prefix . Asset::zerofill($asset_tag, $settings->zerofill_count);
//                            } else {
//                                $asset_tag = $settings->auto_increment_prefix . $asset_tag;
//                            }
                        } else {
                            \Debugbar::info($asset->getErrors()->all());
                        }
                    }
                }
                $data_list .= "\n";
            }


            if ($purchase->delivery_cost > 0) {
                $data_list .= "Стоимость доставки:  " . $purchase->delivery_cost;
            }

            $file_data = file_get_contents(public_path() . '/uploads/purchases/' . $purchase->invoice_file);

            // Encode the image string data into base64
            $file_data_base64 = base64_encode($file_data);

            $user = auth()->user();
//            \Debugbar::info("send bitrix");
//            \Debugbar::info($user);
//            \Debugbar::info($user->bitrix_token);
//            \Debugbar::info($user->bitrix_id);

            /** @var \GuzzleHttp\Client $client */
            $client = new \GuzzleHttp\Client();
            $params = [
                'headers' => [
                    'Content-Type' => 'multipart/form-data',
                ],
                'form_params' => [
                    "IBLOCK_TYPE_ID" => "lists",
                    "IBLOCK_ID" => 52,
                    "ELEMENT_CODE" => "warehouse_buy_" . $purchase->id,
                    "FIELDS[NAME]" => $purchase->invoice_number,
                    "FIELDS[CREATED_BY]" => $user->bitrix_id,
                    "FIELDS[PROPERTY_758]" => $purchase->invoice_type->bitrix_id, // тип платежа
                    "FIELDS[PROPERTY_141]" => $purchase->comment, //описание
                    "FIELDS[PROPERTY_142]" => $purchase->final_price, //сумма
                    "FIELDS[PROPERTY_1641]" => $purchase->legal_person->bitrix_id, //Юр. лицо
                    "FIELDS[PROPERTY_824]" => $purchase->supplier->bitrix_id, //Поставщик bitrix_id
                    "FIELDS[PROPERTY_143][0]" => $purchase->invoice_file, //файл имя
                    "FIELDS[PROPERTY_143][1]" => $file_data_base64, //файл base64
                    "FIELDS[PROPERTY_1132]" => $data_list, // что покупаем
                    "FIELDS[PROPERTY_1134]" => $purchase->id . "", //id заказа
                ]
            ];
            $params_json = json_encode($params);
            file_put_contents(public_path() . '/uploads/purchases/' . $purchase->id . '.json', $params_json);

            $purchase->bitrix_send_json = $purchase->id . '.json';
            $purchase->save();

//            \Debugbar::info("send bitrix");
            if ($user->bitrix_token && $user->bitrix_id) {
                try {
//                    \Debugbar::info("raw_bitrix_token");
                    $raw_bitrix_token = Crypt::decryptString($user->bitrix_token);
                    $response = $client->request('POST',  env('BITRIX_URL').'rest/' . $user->bitrix_id . '/' . $raw_bitrix_token . '/lists.element.add.json/', $params);

                } catch (DecryptException $e) {
                    $response = $client->request('POST',  env('BITRIX_URL').'rest/'.env('BITRIX_USER').'/'.env('BITRIX_KEY').'/lists.element.add.json/', $params);
                }
            } else {
                $response = $client->request('POST',  env('BITRIX_URL').'rest/'.env('BITRIX_USER').'/'.env('BITRIX_KEY').'/lists.element.add.json/', $params);
            }
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
     * @param int $purchaseId
     * @since [v1.0]
     */
    public function edit($purchaseId = null): View|RedirectResponse
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
     * @return \Illuminate\Contracts\View\View
     * @since [v1.0]
     */
    public function getClone($purchaseId): View|RedirectResponse
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
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @since [v1.0]
     */
    public function destroy($purchaseId): RedirectResponse
    {
        $this->authorize('delete', Purchase::class);

        if (is_null($purchase = Purchase::find($purchaseId))) {
            return redirect()->to(route('purchases.index'))->with('error', trans('admin/locations/message.not_found'));
        }
        if ($purchase->status == Purchase::REJECTED) {
            $assets = Asset::where('purchase_id', $purchase->id)->get();
            foreach ($assets as &$value) {
                $value->unsetEventDispatcher();
                $value->forceDelete();
            }
            $purchase->delete();
        } else {
            return redirect()->to(route('purchases.index'))->with('error', "Нельзя удалить");
        }


        return redirect()->to(route('purchases.index'))->with('success', trans('admin/locations/message.delete.success'));
    }


    public function deleteAllRejected(): View|RedirectResponse
    {
        $this->authorize('delete', Purchase::class);

        $purchases = Purchase::with('assets')->where("status", Purchase::REJECTED)->get();
        foreach ($purchases as &$purchase) {
            $pas = $purchase->assets;
            foreach ($pas as &$pa) {
                $pa->unsetEventDispatcher();
                $pa->forceDelete();
            }
            $purchase->delete();
        }
        $total = $purchases->count();
        return redirect()->to(route('purchases.index'))->with('success', "Dell " . $total);
    }

}