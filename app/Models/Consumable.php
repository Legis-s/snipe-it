<?php

namespace App\Models;

use App\MassOperation;
use App\Helpers\Helper;
use App\Models\Traits\Acceptable;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Watson\Validating\ValidatingTrait;

class Consumable extends SnipeModel
{
    use HasFactory;

    protected $presenter = \App\Presenters\ConsumablePresenter::class;
    use CompanyableTrait;
    use Loggable, Presentable;
    use SoftDeletes;
    use Acceptable;

    protected $table = 'consumables';
    protected $casts = [
        'purchase_date' => 'datetime',
        'requestable'    => 'boolean',
        'category_id'    => 'integer',
        'company_id'     => 'integer',
        'supplier_id',
        'qty'            => 'integer',
        'min_amt'        => 'integer',
    ];

    /**
     * Category validation rules
     */
    public $rules = [
        'name'        => 'required|min:3|max:255',
        'qty'         => 'required|integer|min:0',
        'category_id' => 'required|integer',
        'company_id'  => 'integer|nullable',
        'min_amt'     => 'integer|min:0|nullable',
        'purchase_cost'   => 'numeric|nullable|gte:0',
        'purchase_date'   => 'date_format:Y-m-d|nullable',
    ];

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var bool
     */
    protected $injectUniqueIdentifier = true;
    use ValidatingTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'company_id',
        'item_no',
        'location_id',
        'manufacturer_id',
        'name',
        'order_number',
        'model_number',
        'purchase_cost',
        'purchase_date',
        'qty',
        'min_amt',
        'requestable',
        'notes',
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['name', 'order_number', 'purchase_cost', 'purchase_date', 'item_no', 'model_number', 'notes'];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'category'     => ['name'],
        'company'      => ['name'],
        'location'     => ['name'],
        'manufacturer' => ['name'],
        'supplier'     => ['name'],
        'model' => ['name', 'model_number'],
        'model.category' => ['name'],
        'model.manufacturer' => ['name'],
    ];


    /**
     * Establishes the components -> action logs -> uploads relationship
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since [v6.1.13]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function uploads()
    {
        return $this->hasMany(\App\Models\Actionlog::class, 'item_id')
            ->where('item_type', '=', self::class)
            ->where('action_type', '=', 'uploaded')
            ->whereNotNull('filename')
            ->orderBy('created_at', 'desc');
    }


    /**
     * Sets the attribute of whether or not the consumable is requestable
     *
     * This isn't really implemented yet, as you can't currently request a consumable
     * however it will be implemented in the future, and we needed to include
     * this method here so all of our polymorphic methods don't break.
     *
     * @todo Update this comment once it's been implemented
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function setRequestableAttribute($value)
    {
        if ($value == '') {
            $value = null;
        }
        $this->attributes['requestable'] = filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Establishes the consumable -> admin user relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function admin()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Establishes the component -> assignments relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function consumableAssignments()
    {
        return $this->hasMany(\App\Models\ConsumableAssignment::class);
    }

    /**
     * Establishes the component -> company relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function company()
    {
        return $this->belongsTo(\App\Models\Company::class, 'company_id');
    }

    /**
     * Establishes the component -> manufacturer relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function manufacturer()
    {
        return $this->belongsTo(\App\Models\Manufacturer::class, 'manufacturer_id');
    }

    /**
     * Establishes the component -> location relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class, 'location_id');
    }

    /**
     * Establishes the component -> category relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function category()
    {
        return $this->belongsTo(\App\Models\Category::class, 'category_id');
    }

    public function mass_operations()
    {
        return $this->belongsToMany(MassOperation::class);
    }

    /**
     * Establishes the component -> action logs relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function assetlog()
    {
        return $this->hasMany(\App\Models\Actionlog::class, 'item_id')->where('item_type', self::class)->orderBy('created_at', 'desc')->withTrashed();
    }

    /**
     * Gets the full image url for the consumable
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return string | false
     */
    public function getImageUrl()
    {
        if ($this->image) {
            return Storage::disk('public')->url(app('consumables_upload_path').$this->image);
        }
        return false;

    }

    /**
     * Establishes the component -> users relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v3.0]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function users()
    {
        return $this->belongsToMany(\App\Models\User::class, 'consumables_users', 'consumable_id', 'assigned_to')->withPivot('user_id')->withTrashed()->withTimestamps();
    }

    /**
     * Establishes the item -> supplier relationship
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v6.1.1]
     * @return \Illuminate\Database\Eloquent\Relations\Relation
     */
    public function supplier()
    {
        return $this->belongsTo(\App\Models\Supplier::class, 'supplier_id');
    }


    /**
     * Determine whether to send a checkin/checkout email based on
     * asset model category
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return bool
     */
    public function checkin_email()
    {
        return $this->category->checkin_email;
    }

    /**
     * Determine whether this asset requires acceptance by the assigned user
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return bool
     */
    public function requireAcceptance()
    {
        return $this->category->require_acceptance;
    }

    /**
     * Checks for a category-specific EULA, and if that doesn't exist,
     * checks for a settings level EULA
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return string | false
     */
    public function getEula()
    {
        if ($this->category->eula_text) {
            return  Helper::parseEscapedMarkedown($this->category->eula_text);
        } elseif ((Setting::getSettings()->default_eula_text) && ($this->category->use_default_eula == '1')) {
            return  Helper::parseEscapedMarkedown(Setting::getSettings()->default_eula_text);
        } else {
            return null;
        }
    }

    /**
     * Check how many items within a consumable are checked out
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v5.0]
     * @return int
     */
    public function numCheckedOut()
    {
        $checkedout = 0;
        $consumable = ConsumableAssignment::where('consumable_id', $this->id)
            ->whereIn("type",["sold", "issued"])
            ->get();
        $checkedout = 0 ;
        foreach ($consumable as &$consumabl) {
            $checkedout += $consumabl->quantity;
        }


        return $checkedout;
    }

    /**
     * Checks the number of available consumables
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @return int
     */
    public function numRemaining()
    {

        $consumable = ConsumableAssignment::where('consumable_id', $this->id)
            ->whereIn("type",["sold", "issued"])
            ->get();
        $checkedout = 0 ;
        foreach ($consumable as &$consumabl) {
            $checkedout += $consumabl->quantity;
        }
        $total = $this->qty;
        $remaining = $total - $checkedout;

        return $remaining;
    }

    /**
     * -----------------------------------------------
     * BEGIN MUTATORS
     * -----------------------------------------------
     **/

    /**
     * This sets a value for qty if no value is given. The database does not allow this
     * field to be null, and in the other areas of the code, we set a default, but the importer
     * does not.
     *
     * This simply checks that there is a value for quantity, and if there isn't, set it to 0.
     *
     * @author A. Gianotto <snipe@snipe.net>
     * @since v6.3.4
     * @param $value
     * @return void
     */
    public function setQtyAttribute($value)
    {
        $this->attributes['qty'] = (!$value) ? 0 : intval($value);
    }

    /**
     * -----------------------------------------------
     * BEGIN QUERY SCOPES
     * -----------------------------------------------
     **/

    /**
     * Query builder scope to order on company
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  string                              $order       Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderCategory($query, $order)
    {
        return $query->join('categories', 'consumables.category_id', '=', 'categories.id')->orderBy('categories.name', $order);
    }

    /**
     * Query builder scope to order on location
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text                              $order       Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderLocation($query, $order)
    {
        return $query->leftJoin('locations', 'consumables.location_id', '=', 'locations.id')->orderBy('locations.name', $order);
    }

    /**
     * Query builder scope to order on manufacturer
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  string   $order       Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderManufacturer($query, $order)
    {
        return $query->leftJoin('manufacturers', 'consumables.manufacturer_id', '=', 'manufacturers.id')->orderBy('manufacturers.name', $order);
    }

    /**
     * Query builder scope to order on company
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  string                              $order       Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderCompany($query, $order)
    {
        return $query->leftJoin('companies', 'consumables.company_id', '=', 'companies.id')->orderBy('companies.name', $order);
    }

    /**
     * Query builder scope to order on supplier
     *
     * @param  \Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text                              $order       Order
     *
     * @return \Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeOrderSupplier($query, $order)
    {
        return $query->leftJoin('suppliers', 'consumables.supplier_id', '=', 'suppliers.id')->orderBy('suppliers.name', $order);
    }


//    public function contract()
//    {
//        return $this->belongsTo(\App\Models\Contract::class);
//    }


    public function purchase()
    {
        return $this->belongsTo(\App\Models\Purchase::class);
    }

    public function model()
    {
        return $this->belongsTo(\App\Models\AssetModel::class, 'model_id')->withTrashed();
    }


    public function locations()
    {
        return $this->belongsToMany(\App\Models\Location::class, 'consumables_locations', 'consumable_id', 'assigned_to')->withPivot('user_id')->withTrashed()->withTimestamps();
    }

    public function hasLocations()
    {
        return $this->belongsToMany(\App\Models\Location::class, 'consumables_locations', 'consumable_id', 'assigned_to')->count();
    }




    /**
     * Query builder scope to search on text, including catgeory and manufacturer name
     *
     * @param  Illuminate\Database\Query\Builder  $query  Query builder instance
     * @param  text                              $search      Search term
     *
     * @return Illuminate\Database\Query\Builder          Modified query builder
     */
    public function scopeSearchByManufacturerOrCatOrLocation($query, $search)
    {

        return $query->where('name', 'LIKE', "%$search%")
            ->orWhere('model_number', 'LIKE', "%$search%")
            ->orWhere(function ($query) use ($search) {
                $query->whereHas('category', function ($query) use ($search) {
                    $query->where('categories.name', 'LIKE', '%'.$search.'%');
                });
            })
            ->orWhere(function ($query) use ($search) {
                $query->whereHas('manufacturer', function ($query) use ($search) {
                    $query->where('manufacturers.name', 'LIKE', '%'.$search.'%');
                });
            })
            ->orWhere(function ($query) use ($search) {
                $query->whereHas('location', function ($query) use ($search) {
                    $query->where('locations.name', 'LIKE', '%'.$search.'%');
                });
            });

    }

}