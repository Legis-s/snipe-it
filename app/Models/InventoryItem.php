<?php

namespace App\Models;


use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

/**
 * Model for InventoryItems.
 *
 * @version    v1.8
 */
final class InventoryItem extends SnipeModel
{
    protected $table = 'inventory_items';
    protected $presenter = \App\Presenters\InventoryItemPresenter::class;
    use Presentable;
    use Searchable;

    protected $dates = ['deleted_at','checked_at'];
    protected $rules = array(
        'model' => 'required',
        'tag' => 'required',
        'category' => 'required',
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

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'model',
        'category',
        'manufacturer',
        'serial_number',
        'tag',
        'photo',
        'checked',
        'checked_at',
        'inventory_id',
        'status_id',
        'status',
        'asset_id',
        'notes',
        'successfully',
    ];

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'name',
        'model',
        'category',
        'manufacturer',
        'serial_number',
        'tag',
        'checked',
        'checked_at',
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'model'              => ['name', 'model_number'],
        'model.category'     => ['name'],
        'model.manufacturer' => ['name'],
    ];


    public function photo_url()
    {
        return '/uploads/inventory_items/' . $this->photo;
    }

    public function inventory()
    {
        return $this->belongsTo(\App\Models\Inventory::class);
    }

    public function asset()
    {
        return $this->belongsTo(\App\Models\Asset::class)->withTrashed();
    }

    public function status()
    {
        return $this->belongsTo(\App\Models\InventoryStatuslabel::class);
    }

    public function adminuser()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
