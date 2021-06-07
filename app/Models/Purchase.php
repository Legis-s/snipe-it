<?php


namespace App\Models;


use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

class Purchase extends SnipeModel
{
    protected $presenter = 'App\Presenters\PurchasePresenter';
    use Presentable;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $table = 'purchases';
    protected $rules = array(
        'invoice_number'        => 'required|min:1|max:255',
        'final_price'        => 'required',
        'supplier_id'        => 'required',
        'comment'        => 'required',
        'currency'        => 'required',
        'legal_person_id'=> 'required',
        'invoice_type_id'=> 'required',
        'invoice_file'=> 'required',
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
        'invoice_number',
        'invoice_file',
        'bitrix_id',
        'final_price',
        'paid',
        'supplier_id',
        'legal_person_id',
        'invoice_type_id',
        'comment',
        "currency",
        "status",
        "assets_json",
        "sales_json",
        "bitrix_send_json",
        "user_id",
        "bitrix_result_at",
        "verified_at",
        "bitrix_task_id",
        "user_verified_id"
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['invoice_number', 'comment','final_price'];

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

}