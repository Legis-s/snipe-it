<?php
namespace App\Models;

use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Model;
use Watson\Validating\ValidatingTrait;

class ConsumableAssignment extends Model
{
    use CompanyableTrait;
    use ValidatingTrait;

    protected $table = 'consumables_locations';

    protected $presenter = \App\Presenters\ConsumableAssignmentPresenter::class;
    use Presentable;
    use Searchable;
    protected $dates = [
        'created_at',
        'updated_at',
    ];

    const LOCATION = 'location';
    const ASSET = 'asset';
    const USER = 'user';
    const CONTRACT = 'contract';
    const DEAL = 'deal';

    const PURCHASE = 'purchase';
    const ISSUED = 'issued';
    const CONVERTED = 'converted';
    const COLLECTED = 'collected';
    const SOLD = 'sold';
    const MANUALLY = 'manually';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'quantity',
        'type',
        'comment',
        'assigned_type',
        'consumable_id',
        'assigned_to',
        'contract_id',
        'deal_id',
        'cost',
    ];

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['type', 'cost','quantity','comment'];


    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'deal_id'            => ['name'],
    ];

    public function getDisplayNameAttribute()
    {
        return $this->present()->name();
    }


    public function checkedOutToUser()
    {
        return $this->assignedType() === self::USER;
    }

    public function checkedOutToPurchase()
    {
        return $this->assignedType() === self::PURCHASE;
    }

    public function assignedType()
    {
        return strtolower(class_basename($this->assigned_type));
    }

    public function assignedTo()
    {
        return $this->morphTo('assigned', 'assigned_type', 'assigned_to');
    }


    public function assignedAssets()
    {
        return $this->morphMany(App\Models\Asset::class, 'assigned', 'assigned_type', 'assigned_to')->withTrashed();
    }


    public $rules = [
        'assigned_to'   => ['nullable', 'integer', 'required_with:assigned_type'],
        'assigned_type' => ['nullable', 'required_with:assigned_to', 'in:'.User::class.",".Location::class.",".Asset::class.",".Deal::class],
    ];

    public function consumable()
    {
        return $this->belongsTo(\App\Models\Consumable::class);
    }


    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_to');
    }

    public function adminuser()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class, 'assigned_to');
    }


    public function purchase()
    {
        return $this->belongsTo(\App\Models\Purchase::class, 'purchase_id')->withTrashed();
    }

    public function availableForReturn()
    {
        if ($this->type==$this::SOLD || $this->type==$this::ISSUED){
            return true;
        }
        return false;
    }
//    public function availableForCloseDocuments()
//    {
//        if ($this->type==$this::SOLD && $this->assigned_type=='App\Models\User'){
//            return true;
//        }
//        return false;
//    }



    /**
     * Query builder scope to search on text for complex Bootstrap Tables API.
     *
     * @param \Illuminate\Database\Query\Builder $query Query builder instance
     * @param text $search Search term
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeAssignedSearch($query, $search)
    {

        $search = explode(' OR ', $search);
        return $query->leftJoin('users as cl_users', function ($leftJoin) {
            $leftJoin->on("cl_users.id", "=", "consumables_locations.assigned_to")
                ->where("consumables_locations.assigned_type", "=", User::class);
        })->leftJoin('locations as cl_locations', function ($leftJoin) {
            $leftJoin->on("cl_locations.id", "=", "consumables_locations.assigned_to")
                ->where("consumables_locations.assigned_type", "=", Location::class);
        })->leftJoin('contracts as cl_contracts', function ($leftJoin) {
            $leftJoin->on('cl_contracts.id', '=', 'consumables_locations.assigned_to')
                ->where('consumables_locations.assigned_type', '=', Contract::class);
        })->leftJoin('purchases as cl_purchases', function ($leftJoin) {
            $leftJoin->on('cl_purchases.id', '=', 'consumables_locations.assigned_to')
                ->where('consumables_locations.assigned_type', '=', Purchase::class);
        })->leftJoin('assets as cl_assets', function ($leftJoin) {
            $leftJoin->on('cl_assets.id', '=', 'consumables_locations.assigned_to')
                ->where('consumables_locations.assigned_type', '=', Asset::class);
        })->leftJoin('contracts as list_contracts', function ($leftJoin) {
            $leftJoin->on('list_contracts.id', '=', 'consumables_locations.contract_id');
        }) ->where(function ($query) use ($search) {
            foreach ($search as $search) {
                $query->orWhere(function ($query) use ($search) {
                    $query->where('cl_users.first_name', 'LIKE', '%' . $search . '%')
                        ->orWhere('cl_users.last_name', 'LIKE', '%' . $search . '%')
                        ->orWhere('cl_users.username', 'LIKE', '%' . $search . '%')
                        ->orWhere('cl_purchases.invoice_number', 'LIKE', '%' . $search . '%')
                        ->orWhere('cl_contracts.name', 'LIKE', '%' . $search . '%')
                        ->orWhere('cl_assets.name', 'LIKE', '%' . $search . '%')
                        ->orWhere('cl_locations.name', 'LIKE', '%' . $search . '%')
                        ->orWhere('cl_contracts.name', 'LIKE', '%' . $search . '%')
                        ->orWhere('list_contracts.name', 'LIKE', '%' . $search . '%');
                });

                $query->orWhere('consumables_locations.quantity', 'LIKE', '%' . $search . '%')
                    ->orWhere('consumables_locations.comment', 'LIKE', '%' . $search . '%')
                    ->orWhere('consumables_locations.cost', 'LIKE', '%' . $search . '%');
            }

        }); //workaround for laravel bug
    }

    public function contract()
    {
        return $this->belongsTo(\App\Models\Contract::class, 'contract_id');
    }

    public function deal()
    {
        return $this->belongsTo(\App\Models\Deal::class, 'deal_id');
    }

    public function mass_operations()
    {
        return $this->belongsToMany(MassOperation::class,'cons_assignment_mass_operation');
    }
}
