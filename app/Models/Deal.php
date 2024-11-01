<?php


namespace App\Models;


use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

class Deal extends SnipeModel
{
    protected $presenter = \App\Presenters\DealPresenter::class;
    use Presentable;


    protected $dates = ['deleted_at'];
    protected $table = 'deals';
    protected $rules = array(
        'name' => 'required|min:2|max:255',
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
        'name', //NAME
        'number', //UF_NUMBER
        'status', //STATUS_ID
        'date_start',//DATE_START
        'date_end', // DATE_END
        'bitrix_id', //ID
        'type', //ID
        'assigned_by_id',
        'summ'
    ];


    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['name', 'number', 'type',];


    public function getTypeText()
    {
        switch ($this->type) {
            case 2:
                return "Пультовая охрана";
            case 3:
                return "Техническая безопасность";
            case 13:
                return "Пультовая Монтаж";
            default:
                return "";
        }
    }

//    public function assets()
//    {
//        return $this->hasMany(\App\Models\Asset::class, 'deal_id');
//    }
//
//    public function consumable()
//    {
//        return $this->hasMany(\App\Models\ConsumableAssignment::class, 'deal_id');
//    }

}