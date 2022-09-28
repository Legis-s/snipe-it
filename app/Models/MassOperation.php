<?php

namespace App\Models;

use App\Models\Asset;
use App\Models\Traits\Searchable;
use Illuminate\Database\Eloquent\Model;

class MassOperation extends Model
{

    protected $table = 'mass_operations';

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
        'updated_at'
    ];

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

    public function assets()
    {
        return $this->belongsToMany(Asset::class);
    }

    public function consumables()
    {
        return $this->belongsToMany(Consumable::class);
    }

    use Searchable;
}
