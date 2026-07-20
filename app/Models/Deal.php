<?php


namespace App\Models;


use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\Searchable;
use App\Presenters\DealPresenter;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

class Deal extends SnipeModel
{
    use Presentable;
    use ValidatingTrait;
    use UniqueUndeletedTrait;
    use Searchable;
    use SoftDeletes;

    protected $presenter = DealPresenter::class;

    protected $table = 'deals';

    protected $rules = array(
        'name' => 'required|min:2|max:255',
    );

    protected $dates = [
        'deleted_at'
    ];


    /**
     * Whether the model should inject its identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var bool
     */
    protected $injectUniqueIdentifier = true;

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


    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'name',
        'number',
        'type',
    ];


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

    public function assets()
    {
        return $this->hasMany(Asset::class, 'deal_id');
    }

    public function consumable()
    {
        return $this->hasMany(ConsumableAssignment::class, 'deal_id');
    }


    /**
     * Establishes the asset -> deal assignment relationship
     * @return Relation
    */
    public function assignedAssets()
    {
//        return $this->morphMany(\App\Models\Asset::class, 'assigned', 'assigned_type', 'assigned_to')->withTrashed();
        return $this->morphMany(Asset::class, 'assigned', 'assigned_type', 'assigned_to')->AssetsForShow()->withTrashed();
    }

    /**
     * Establishes the consumable -> deal assignment relationship
     * @return Relation
    */
    public function assignedConsumables()
    {
        return $this->morphMany(ConsumableAssignment::class, 'assigned', 'assigned_type', 'assigned_to');
    }

}