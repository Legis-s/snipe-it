<?php


namespace App\Models;


use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use DateTime;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

class Request extends SnipeModel
{
    protected $presenter = 'App\Presenters\RequestPresenter';
    use Presentable;
    use SoftDeletes;

    const INPROGRESS = 'inprogress';
    const FINISHED = 'finished';
    const REVIEW = 'review';
    const INVENTORY = 'inventory';

    protected $dates = ['deleted_at'];
    protected $table = 'requests';
    protected $rules = array(
        'bitrix_id'  => 'min:1|max:10|nullable'
    );

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var boolean
     */
    protected $injectUniqueIdentifier = true;
    use ValidatingTrait;
    use UniqueUndeletedTrait;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'bitrix_id',
        'name',
        'status',
        'location_id',
        'issued',
        "user_id",
        "price",
        "type",
        "contract_id",
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['name',];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'user' => ['first_name','last_name'],
        'supplier' => ['name'],

    ];


    public function assets()
    {
        return $this->hasMany('\App\Models\Asset', 'purchase_id')
            ->whereHas('assetstatus', function ($query) {
                $query->where('status_labels.deployable', '=', 1)
                    ->orWhere('status_labels.pending', '=', 1)
                    ->orWhere('status_labels.archived', '=', 0);
            });
    }

    public function consumables()
    {
        return $this->hasMany('\App\Models\Consumable', 'purchase_id');
    }

    public function sales()
    {
        return $this->hasMany('\App\Models\Sale', 'purchase_id');
    }

    public function supplier()
    {
        return $this->belongsTo('\App\Models\Supplier');
    }

    public function invoice_type()
    {
        return $this->belongsTo('\App\Models\InvoiceType');
    }

    public function legal_person()
    {
        return $this->belongsTo('\App\Models\LegalPerson');
    }


    public function getInvoiceFile()
    {
        if ($this->invoice_file && !empty($this->invoice_file)) {
            return url('/').'/uploads/purchases/'.$this->invoice_file;
        }
        return false;
    }

    public function user()
    {
        return $this->belongsTo('\App\Models\User', 'user_id');
    }

    public function user_verified()
    {
        return $this->belongsTo('\App\Models\User', 'user_verified_id');
    }


    public function setStatusInprogress()
    {
        $this->status = $this::INPROGRESS;
    }

    public function setStatusPaid()
    {
        $this->bitrix_result_at = new DateTime();

        $assets = Asset::where('purchase_id', $this->id)->get();
        $sales = Sale::where('purchase_id', $this->id)->get();
        $need_to_inventrory = false;
        if(count($assets)>0){
            $need_to_inventrory = true;
            foreach ($assets as &$asset) {
                $asset->setStatusAfterPaid();
                $asset->save();
            }
        }
        if(count($sales)>0){
            $need_to_inventrory= true;
            foreach ($sales as &$sale) {
                $sale->setStatusAfterPaid();
                $sale->save();
            }
        }

        // меняем статус на "В процессе инвентаризации", только если еще её не было у закупки
        if ($this->status != $this::REVIEW  && $this->status != $this::FINISHED && $this->status != $this::INVENTORY ) {
            if($need_to_inventrory){
                $this->status = $this::INVENTORY;
            }else{
                $this->status = $this::REVIEW;
            }
        }

    }
    public function checkStatus($asset_new = null){

        $status_review_wait = Statuslabel::where('name', 'Ожидает проверки')->first();
        $status_review_wait_id = intval($status_review_wait->id);
        $status_ok = Statuslabel::where('name', 'Доступные')->first();
        $status_ok_id = intval($status_ok->id);

        $assets = Asset::where('purchase_id', $this->id)->get();
        $sales = Sale::where('purchase_id', $this->id)->get();
        $consumables_json = $this->consumables_json;
        $consumables = json_decode($consumables_json, true);
        $consumables_count = count($consumables);

        $all_status = "inventory";
        $consumables_status = "review";

        $asset_status = "inventory";
        $assets_count = count($assets);
        $assets_review_wait_count=0;
        $assets_status_ok_count=0;
        $sales_status = "inventory";
        $sales_count = count($sales);
        $sales_review_wait_count=0;
        $sales_status_ok_count=0;

        if($assets_count>0){
            foreach ($assets as &$asset) {
                if ($asset_new != null && $asset_new instanceof Asset) {
                    if ($asset_new->id == $asset->id){
                        $asset->status_id = $asset_new->status_id;
                    }
                }
                if($asset->status_id==$status_review_wait_id) {
                    $assets_review_wait_count++;
                }
                if($asset->status_id==$status_ok_id) {
                    $assets_status_ok_count++;
                }
            }

            if($assets_count==$assets_review_wait_count+$assets_status_ok_count){
                $asset_status = "review";
            }
            if($assets_count==$assets_status_ok_count){
                $asset_status = "finished";
            }
        }else{
            $asset_status = "finished";
        }

        if($sales_count>0){
            foreach ($sales as &$sale) {

                if ($asset_new != null && $asset_new instanceof Sale) {
                    if ($asset_new->id == $sale->id){
                        $sale->status_id = $asset_new->status_id;
                    }
                }

                if($sale->status_id==$status_review_wait_id) {
                    $sales_review_wait_count++;
                }
                if($sale->status_id==$status_ok_id) {
                    $sales_status_ok_count++;
                }
            }
            if($sales_count==$sales_review_wait_count+$sales_status_ok_count){
                $sales_status = "review";
            }
            if($sales_count==$sales_status_ok_count){
                $sales_status = "finished";
            }
        }else{
            $sales_status = "finished";
        }
        if($consumables_count>0){
            $consumable_all_count=0;
            $consumable_review_count=0;
            foreach ($consumables as &$consumable) {
                $consumable_all_count+=$consumable["quantity"];
                if (isset($consumable["reviewed"])){
                    $consumable_review_count+=$consumable["reviewed"];
                }
            }
            if($consumable_all_count==$consumable_review_count){
                $consumables_status = "finished";
            }
        }else{
            $consumables_status = "finished";
        }

        if(($asset_status=="review")||( $consumables_status=="review" )||($sales_status=="review")){
            $all_status ="review";
        }
        if(($asset_status=="inventory")||( $consumables_status=="inventory" )||($sales_status=="inventory")){
            $all_status ="inventory";
        }
        if($asset_status==$sales_status &&$asset_status == $consumables_status && $asset_status=="finished"){
            $all_status ="finished";
        }
        if ($all_status =="review"){
            $this->status = $this::REVIEW;
        }
        if ($all_status =="finished"){
            $this->status = $this::FINISHED;
            if ($asset_new){
                $this->closeBitrixTask($asset_new);
            }
        }
//        \Log::error("asset ".$asset_status);
        return $all_status;

    }

    public function closeBitrixTask($asset = null){
        /** @var \GuzzleHttp\Client $client */
        $client = new \GuzzleHttp\Client();
        if ($asset) {
            $user = null;
            $user = $asset->user_verified;
            $this->user_verified_id = $user->id;
            $this->save();
            if ($user && $user->bitrix_token && $user->bitrix_id && $this->bitrix_task_id) {
                $params1 = [
                    'query' => [
                        'taskId' => $this->bitrix_task_id
                    ]
                ];
                $raw_bitrix_token = Crypt::decryptString($user->bitrix_token);

                $client->request('POST', 'https://bitrix.legis-s.ru/rest/' . $user->bitrix_id . '/' . $raw_bitrix_token . '/tasks.task.complete/', $params1);
                $params2 = [
                    'query' => [
                        'TASKID' => $this->bitrix_task_id,
                        'FIELDS' => [
                            'POST_MESSAGE' => 'Закрыта автоматически.'
                        ]
                    ]
                ];
                $client->request('POST', 'https://bitrix.legis-s.ru/rest/' . $user->bitrix_id . '/' . $raw_bitrix_token . '/task.commentitem.add/', $params2);
            }
        }

    }
}