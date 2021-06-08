<?php


namespace App\Models;


use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

class Contract  extends SnipeModel
{
    protected $presenter = 'App\Presenters\ContractPresenter';
    use Presentable;

    protected $dates = ['deleted_at'];
    protected $table = 'contracts';
    protected $rules = array(
        'name' => 'required|min:2|max:255|unique_undeleted',
        'bitrix_id' => 'min:1|nullable'
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
    ];


    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['name', 'account_number', 'comments', 'bitrix_id'];



}