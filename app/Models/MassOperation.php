<?php

namespace App\Models;

use App\Models\Asset;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MassOperation extends SnipeModel
{

    const LOCATION = 'location';
    const ASSET = 'asset';
    const USER = 'user';
    const CONTRACT = 'contract';

    use HasFactory;
    protected $presenter = \App\Presenters\MassOperationsPresenter::class;
    use Presentable;
    use SoftDeletes;

    protected $table = 'mass_operations';
    protected $rules = [
        'operation_type' => 'max:32',
        'name' => 'max:255|nullable',
        'user_id' => 'integer',
        'contract_id' => 'integer',
        'assigned_type' => 'max:255|nullable',
        'assigned_to' => 'integer',
        'bitrix_task_id' => 'integer',
        'note' => 'max:255|nullable',
        'created_at' => 'date|max:10|min:10|nullable',
        'updated_at' => 'date|max:10|min:10|nullable'
    ];
    protected $casts = [
        'user_id'     => 'integer',
        'contract_id'     => 'integer',
    ];


    protected $fillable = [
        'operation_type',
        'name',
        'user_id',
        'contract_id',
        'assigned_type',
        'assigned_to',
        'bitrix_task_id',
        'note',
    ];


    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    use Searchable;


    /**
     * Get the target this asset is checked out to
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assignedTo()
    {
        return $this->morphTo('assigned', 'assigned_type', 'assigned_to')->withTrashed();
    }

    /**
     * Determines whether the asset is checked out to a user
     *
     * Even though we allow allow for checkout to things beyond users
     * this method is an easy way of seeing if we are checked out to a user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return bool
     */
    public function checkedOutToUser()
    {
        return $this->assignedType() === self::USER;
    }

    /**
     * Gets the lowercased name of the type of target the asset is assigned to
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return string
     */
    public function assignedType()
    {
        return strtolower(class_basename($this->assigned_type));
    }


    public function assets()
    {
        return $this->belongsToMany(Asset::class);
    }

    public function consumables()
    {
        return $this->belongsToMany(Consumable::class);
    }
    public function consumables_assigments()
    {
        return $this->belongsToMany(ConsumableAssignment::class,'cons_assignment_mass_operation');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }


}
