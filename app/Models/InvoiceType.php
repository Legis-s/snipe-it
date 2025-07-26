<?php

namespace App\Models;

use App\Http\Traits\UniqueUndeletedTrait;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

class InvoiceType extends SnipeModel
{
    protected $presenter = \App\Presenters\InvoiceTypePresenter::class;
    use Presentable;
    use SoftDeletes;

    protected $table = 'invoice_types';
    protected $dates = ['deleted_at'];
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
        'name',
        'bitrix_id',
        'active',
    ];

    /**
     * Find assets with this purchase as their invoice_type_id
     *
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function purchases()
    {
        return $this->hasMany(\App\Models\Purchase::class, 'invoice_type_id');
    }
}