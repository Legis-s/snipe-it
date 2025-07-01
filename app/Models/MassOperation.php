<?php

namespace App\Models;


use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'created_by' => 'integer',
        'contract_id' => 'integer',
        'assigned_type' => 'max:255|nullable',
        'assigned_to' => 'integer',
        'bitrix_task_id' => 'integer',
        'note' => 'max:255|nullable',
        'created_at' => 'date|max:10|min:10|nullable',
        'updated_at' => 'date|max:10|min:10|nullable'
    ];
    protected $casts = [
        'contract_id' => 'integer',
    ];


    protected $fillable = [
        'operation_type',
        'name',
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
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assignedTo()
    {
        return $this->morphTo('assigned', 'assigned_type', 'assigned_to')->withTrashed();
    }

    /**
     * @return bool
     */
    public function checkedOutToUser()
    {
        return $this->assignedType() === self::USER;
    }

    /**
     * Gets the lowercased name of the type of target the asset is assigned to
     */
    public function assignedType(): string
    {
        return strtolower(class_basename($this->assigned_type));
    }


    public function assets()
    {
        return $this->belongsToMany(\App\Models\Asset::class);
    }

    public function consumables()
    {
        return $this->belongsToMany(\App\Models\Consumable::class);
    }

    public function consumables_assigments()
    {
        return $this->belongsToMany(\App\Models\ConsumableAssignment::class, 'cons_assignment_mass_operation');
    }

    public function adminuser()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

}
