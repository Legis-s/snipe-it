<?php
namespace App\Models;

use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Model;

class ConsumableAssignment extends Model
{
    use CompanyableTrait;

    protected $presenter = 'App\Presenters\ConsumableAssignmentPresenter';

    use  Presentable;
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $table = 'consumables_locations';


    const LOCATION = 'location';
    const ASSET = 'asset';
    const USER = 'user';
    const CONTRACT = 'contract';

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
        'user_id',
        'consumable_id',
        'assigned_to',
        'cost',
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
        return $this->morphMany('App\Models\Asset', 'assigned', 'assigned_type', 'assigned_to')->withTrashed();
    }


    public function consumable()
    {
        return $this->belongsTo('\App\Models\Consumable');
    }


    public function location()
    {
        return $this->belongsTo('\App\Models\Location', 'assigned_to');
    }

    public function user()
    {
        return $this->belongsTo('\App\Models\User', 'user_id');
    }

    public function purchase()
    {
        return $this->belongsTo('\App\Models\Purchase', 'purchase_id');
    }
}
