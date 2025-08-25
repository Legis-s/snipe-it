<?php

namespace App\Models;

use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Watson\Validating\ValidatingTrait;

class InventoryStatuslabel extends SnipeModel
{
//    use HasFactory;
    use SoftDeletes;
    use ValidatingTrait;
    use UniqueUndeletedTrait;

    protected $injectUniqueIdentifier = true;
    protected $dates = ['deleted_at'];
    protected $table = 'inventory_status_labels';
    protected $hidden = ['created_by', 'deleted_at'];


    protected $rules = array(
        'name' => 'required|string|unique_undeleted',
        'notes' => 'string|nullable',
    );

    protected $fillable = [
        'name',
        'notes',
        'success',
        'color',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['name', 'notes'];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [];

    public function inventory_items()
    {
        return $this->hasMany(\App\Models\InventoryItem::class);
    }

    public function adminuser()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }


}
