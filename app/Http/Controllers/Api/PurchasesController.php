<?php


namespace App\Http\Controllers\Api;


use App\Models\Asset;
use App\Models\Consumable;
use App\Models\Statuslabel;
use DateTime;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Transformers\PurchasesTransformer;
use App\Helpers\Helper;
use App\Models\Purchase;
use Auth;
use Illuminate\Database\Eloquent\Builder;
use Crypt;

class PurchasesController extends Controller
{
    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view', Purchase::class);
        $status = Statuslabel::where('name', 'Доступные')->first();
        $purchases = Purchase::with('supplier', 'assets', 'invoice_type', 'legal_person','user','consumables')
            ->select([
                'purchases.id',
                'purchases.invoice_number',
                'purchases.invoice_file',
                'purchases.bitrix_id',
                'purchases.final_price',
                'purchases.status',
                'purchases.supplier_id',
                'purchases.legal_person_id',
                'purchases.invoice_type_id',
                'purchases.comment',
                'purchases.currency',
                'purchases.user_id',
                'purchases.user_verified_id',
                'purchases.created_at',
                'purchases.deleted_at',
                'purchases.bitrix_task_id',
                'purchases.consumables_json',
                'purchases.delivery_cost',
            ])->withCount([
                'consumables as consumables_count',
                'assets as assets_count',
                'assets as assets_count_ok' => function (Builder $query) use ($status) {
                    $query->where('status_id', $status->id);
                },
            ]);

        if ($request->filled('search')) {
            $purchases = $purchases->TextSearch($request->input('search'));

        }
        if ($request->filled('user_id')) {
            $purchases->where('user_id', '=', $request->input('user_id'));
        }
        if ($request->filled('status')) {
            $purchases->where('status', '=', $request->input('status'));
        }
        if ($request->filled('supplier')) {
            $purchases->where('supplier_id', '=', $request->input('supplier'));
        }

        $allowed_columns =
            [
                'id', 'invoice_number', 'bitrix_id', 'final_price', 'status', 'created_at',
                'deleted_at'
            ];


        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';

        if ($request->input('not_finished_status')){
            $purchases->where('status', '<>', "finished");
        }

        $purchases->orderBy($sort, $order);
        // Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
        // case we override with the actual count, so we should return 0 items.
        $offset = (($purchases) && ($request->get('offset') > $purchases->count())) ? $purchases->count() : $request->get('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');


        $total = $purchases->count();
        $purchases = $purchases->skip($offset)->take($limit)->get();
        return (new PurchasesTransformer)->transformPurchases($purchases, $total);
    }


    /**
     * Display the specified resource.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $this->authorize('view', Purchase::class);
        $status = Statuslabel::where('name', 'Доступные')->first();
        $purchise = Purchase::with('supplier', 'assets', 'invoice_type', 'legal_person','user','consumables')
            ->select([
                'purchases.id',
                'purchases.invoice_number',
                'purchases.invoice_file',
                'purchases.bitrix_id',
                'purchases.final_price',
                'purchases.status',
                'purchases.supplier_id',
                'purchases.legal_person_id',
                'purchases.invoice_type_id',
                'purchases.comment',
                'purchases.currency',
                'purchases.user_id',
                'purchases.user_verified_id',
                'purchases.created_at',
                'purchases.deleted_at',
                'purchases.bitrix_task_id',
                'purchases.consumables_json',
            ])->withCount([
                'consumables as consumables_count',
                'assets as assets_count',
                'assets as assets_count_ok' => function (Builder $query) use ($status) {
                    $query->where('status_id', $status->id);
                },
            ])
            ->findOrFail($id);
        return (new PurchasesTransformer)->transformPurchase($purchise,true);
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function paid(Request $request, $purchaseId = null)
    {
        $this->authorize('view', Purchase::class);
        $purchase = Purchase::findOrFail($purchaseId);
        $purchase->setStatusPaid();

        if ($purchase->save()) {
            return response()->json(
                Helper::formatStandardApiResponse(
                    'success',
                    (new PurchasesTransformer)->transformPurchase($purchase),
                    trans('admin/locations/message.update.success')
                )
            );
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $purchase->getErrors()));
    }

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function consumables_check(Request $request, $purchaseId = null)
    {
        $this->authorize('view', Purchase::class);
        $purchase = Purchase::findOrFail($purchaseId);

        $assets = Asset::where('purchase_id', $purchase->id)->get();
        $status_ok = Statuslabel::where('name', 'Доступные')->first();
        if (count($assets) > 0){
            $all_ok = true;
            foreach ($assets as &$asset) {
                if ($asset->status_id != $status_ok->id){
                    $all_ok = false;
                }
            }
            if($all_ok){
                $purchase->status = "finished";
            }
        }else{
            $purchase->status = "finished";
        }

        if ($purchase->save()) {
            $consumables_server = Consumable::where('purchase_id', $purchase->id)->get();
            $consumables = json_decode($purchase->consumables_json, true);
            if ($purchase->consumables_json != null && count($consumables) > 0 && count($consumables_server) == 0) {
                foreach ($consumables as &$consumable_new) {
                    $consumable_server = new Consumable();
                    $consumable_server->name = $consumable_new["name"];
                    $consumable_server->category_id = $consumable_new["category_id"];
                    if(!empty($consumable_new["model_id"])) {
                        $consumable_server->model_id = $consumable_new["model_id"];
                    }
                    $consumable_server->order_number = $purchase->id;
                    $consumable_server->manufacturer_id = $consumable_new["manufacturer_id"];
//                    $consumable_server->model_number = $consumable_new["model_number"];
                    $consumable_server->purchase_date = $purchase->created_at;
                    $consumable_server->purchase_cost = Helper::ParseFloat($consumable_new["purchase_cost"]);
                    $consumable_server->qty = Helper::ParseFloat($consumable_new["quantity"]);
                    $consumable_server->purchase_id = $purchase->id;
                    $consumable_server->save();
                }
            }
            return response()->json(
                Helper::formatStandardApiResponse(
                    'success',
                    (new PurchasesTransformer)->transformPurchase($purchase),
                    trans('admin/locations/message.update.success')
                )
            );
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $purchase->getErrors()));
    }





    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function in_payment(Request $request, $purchaseId = null)
    {
        $this->authorize('view', Purchase::class);
        $purchase = Purchase::findOrFail($purchaseId);
        $purchase->status = "in_payment";
        if ($purchase->save()) {

            return response()->json(
                Helper::formatStandardApiResponse(
                    'success',
                    (new PurchasesTransformer)->transformPurchase($purchase),
                    trans('admin/locations/message.update.success')
                )
            );
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $purchase->getErrors()));
    }


    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function bitrix_task(Request $request, $purchaseId = null,$bitrix_task= null)
    {
        $this->authorize('view', Purchase::class);
        $purchase = Purchase::findOrFail($purchaseId);
        $purchase->bitrix_task_id = $bitrix_task;
        if ($purchase->save()) {
            return response()->json(
                Helper::formatStandardApiResponse(
                    'success',
                    (new PurchasesTransformer)->transformPurchase($purchase),
                    trans('admin/locations/message.update.success')
                )
            );
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $purchase->getErrors()));
    }


    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function reject(Request $request, $purchaseId = null)
    {
        $this->authorize('view', Purchase::class);
        $purchase = Purchase::findOrFail($purchaseId);
        $purchase->status = "rejected";
        $purchase->bitrix_result_at =new DateTime();
        if ($purchase->save()) {
            $status = Statuslabel::where('name', 'Отклонено')->first();
            $assets = Asset::where('purchase_id', $purchase->id)->get();
            foreach ($assets as &$value) {
                $value->status_id = $status->id;
                $value->unsetEventDispatcher();
                $value->save();
            }

            return response()->json(
                Helper::formatStandardApiResponse(
                    'success',
                    (new PurchasesTransformer)->transformPurchase($purchase),
                    trans('admin/locations/message.update.success')
                )
            );
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $purchase->getErrors()));
    }


    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\Response
     */
    public function resend(Request $request, $purchaseId = null)
    {
        $this->authorize('view', Purchase::class);
        $purchase = Purchase::findOrFail($purchaseId);


        $file_data = file_get_contents(public_path().'/uploads/purchases/'.$purchase->bitrix_send_json);

        $params = json_decode($file_data, true);
        /** @var \GuzzleHttp\Client $client */
        $client = new \GuzzleHttp\Client();
        $user =  Auth::user();
        if ($user->bitrix_token && $user->bitrix_id){
            $raw_bitrix_token  = Crypt::decryptString($user->bitrix_token);
            $response = $client->request('POST', 'https://bitrix.legis-s.ru/rest/'.$user->bitrix_id.'/'.$raw_bitrix_token.'/lists.element.add.json/',$params);

        }else{
            $response = $client->request('POST', 'https://bitrix.legis-s.ru/rest/722/q7e6fc3qrkiok64x/lists.element.add.json/',$params);
        }
//        $response = $client->request('POST', 'https://bitrix.legis-s.ru/rest/722/q7e6fc3qrkiok64x/lists.element.add.json/',$params);

        $response = $response->getBody()->getContents();
        $bitrix_result = json_decode($response, true);
        $bitrix_id = $bitrix_result["result"];
        $purchase->bitrix_id = $bitrix_id;


        if ($purchase->save()) {
            return "ok";
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $purchase->getErrors()));
    }



}