<?php

namespace App\Models;

use App\Exceptions\CheckoutNotAllowed;
use App\Http\Traits\UniqueSerialTrait;
use App\Http\Traits\UniqueUndeletedTrait;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use AssetPresenter;
use Auth;
use Carbon\Carbon;
use Config;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;
use Watson\Validating\ValidatingTrait;
use DB;
use App\Notifications\CheckinAssetNotification;
use App\Notifications\CheckoutAssetNotification;

/**
 * Model for Assets.
 *
 * @version    v1.0
 */
class Sale extends Depreciable
{
    protected $presenter = 'App\Presenters\SalesPresenter';
    use Loggable, Presentable, SoftDeletes, ValidatingTrait, UniqueUndeletedTrait, UniqueSerialTrait;

    const LOCATION = 'location';
    const CONTRACT = 'contract';
    const USER = 'user';

    const ACCEPTANCE_PENDING = 'pending';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sales';

    /**
     * Whether the model should inject it's identifier to the unique
     * validation rules before attempting validation. If this property
     * is not set in the model it will default to true.
     *
     * @var boolean
     */
    protected $injectUniqueIdentifier = true;

    // We set these as protected dates so that they will be easily accessible via Carbon
    protected $dates = [
        'sold_at',
        'created_at',
        'updated_at',
        'deleted_at',
        'purchase_date',
        'last_checkout',
    ];


    protected $rules = [
        'name' => 'max:255|nullable',
        'model_id' => 'required|integer|exists:models,id',
        'status_id' => 'required|integer|exists:status_labels,id',
        'company_id' => 'integer|nullable',
        'checkout_date' => 'date|max:10|min:10|nullable',
        'checkin_date' => 'date|max:10|min:10|nullable',
        'supplier_id' => 'numeric|nullable',
        'asset_tag' => 'required|min:1|max:255|unique_undeleted',
        'status' => 'integer',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'asset_tag',
        'assigned_to',
        'assigned_type',
        'company_id',
        'image',
        'location_id',
        'model_id',
        'name',
        'notes',
        'order_number',
        'purchase_cost',
        'depreciable_cost',
        'quality',
        'purchase_date',
        'rtd_location_id',
        'serial',
        'status_id',
        'supplier_id',
        'warranty_months',
        'requestable',
        'purchase_id',
        'nds',
        'user_verified_id',
        'contract_id',
        'user_responsible_id',
        'closing_documents'
    ];

    use Searchable;

    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = [
        'name',
        'asset_tag',
        'serial',
        'order_number',
        'purchase_cost',
        'depreciable_cost',
        'notes',
        'created_at',
        'updated_at',
        'purchase_date',
        'quality',
        'expected_checkin',
        'next_audit_date',
        'last_audit_date'
    ];

    /**
     * The relations and their attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableRelations = [
        'assetstatus' => ['name'],
        'supplier' => ['name'],
        'company' => ['name'],
        'defaultLoc' => ['name'],
        'model' => ['name', 'model_number'],
        'model.category' => ['name'],
        'model.manufacturer' => ['name'],
    ];

    public function getDisplayNameAttribute()
    {
        return $this->present()->name();
    }


    public function company()
    {
        return $this->belongsTo('\App\Models\Company', 'company_id');
    }

    public function assignedTo()
    {
        return $this->morphTo('assigned', 'assigned_type', 'assigned_to');
    }

    public function assignedAssets()
    {
        return $this->morphMany('App\Models\Asset', 'assigned', 'assigned_type', 'assigned_to')->withTrashed();
    }

    public function assignedType()
    {
        return strtolower(class_basename($this->assigned_type));
    }

    public function getImageUrl()
    {
        if ($this->image && !empty($this->image)) {
            return url('/') . '/uploads/assets/' . $this->image;
        } elseif ($this->model && !empty($this->model->image)) {
            return url('/') . '/uploads/models/' . $this->model->image;
        }
        return false;
    }


    /**
     * Get action logs for this asset
     */
    public function adminuser()
    {
        return $this->belongsTo('\App\Models\User', 'user_id');
    }

    /**
     * Get total assets
     */
    public static function assetcount()
    {
        return Company::scopeCompanyables(Asset::where('physical', '=', '1'))
            ->whereNull('deleted_at', 'and')
            ->count();
    }


    /**
     * Get total assets not checked out
     */
    public static function availassetcount()
    {
        return Asset::RTD()
            ->whereNull('deleted_at')
            ->count();
    }

    /**
     * Get asset status
     */
    public function assetstatus()
    {
        return $this->belongsTo('\App\Models\Statuslabel', 'status_id');
    }

    public function model()
    {
        return $this->belongsTo('\App\Models\AssetModel', 'model_id')->withTrashed();
    }

    public function supplier()
    {
        return $this->belongsTo('\App\Models\Supplier', 'supplier_id');
    }


    public function location()
    {
        return $this->belongsTo('\App\Models\Location', 'location_id');
    }

    /**
     * Get auto-increment
     */
    public static function autoincrement_asset()
    {
        $settings = \App\Models\Setting::getSettings();


        if ($settings->auto_increment_assets == '1') {
            $temp_asset_tag = \DB::table('assets')
                ->where('physical', '=', '1')
                ->max('asset_tag');

            $asset_tag_digits = preg_replace('/\D/', '', $temp_asset_tag);
            $asset_tag = preg_replace('/^0*/', '', $asset_tag_digits);

            if ($settings->zerofill_count > 0) {
                return $settings->auto_increment_prefix . Asset::zerofill($settings->next_auto_tag_base, $settings->zerofill_count);
            }
            return $settings->auto_increment_prefix . $settings->next_auto_tag_base;
        } else {
            return false;
        }
    }


    /*
     * Get the next base number for the auto-incrementer. We'll add the zerofill and
     * prefixes on the fly as we generate the number
     *
     */
    public static function nextAutoIncrement($assets)
    {

        $max = 1;

        foreach ($assets as $asset) {
            $results = preg_match("/\d+$/", $asset['asset_tag'], $matches);

            if ($results) {
                $number = $matches[0];

                if ($number > $max) {
                    $max = $number;
                }
            }
        }
        return $max + 1;

    }
    public static function zerofill($num, $zerofill = 3)
    {
        return str_pad($num, $zerofill, '0', STR_PAD_LEFT);
    }


    public function checkin_email()
    {
        return $this->model->category->checkin_email;
    }


    public function getEula()
    {
        $Parsedown = new \Parsedown();

        if (($this->model) && ($this->model->category)) {
            if ($this->model->category->eula_text) {
                return $Parsedown->text(e($this->model->category->eula_text));
            } elseif ($this->model->category->use_default_eula == '1') {
                return $Parsedown->text(e(Setting::getSettings()->default_eula_text));
            } else {
                return false;
            }
        }
        return false;
    }

    public function purchase()
    {
        return $this->belongsTo('\App\Models\Purchase');
    }

    public function user_responsible()
    {
        return $this->belongsTo('\App\Models\User', 'user_responsible_id');
    }

    public function contract()
    {
        return $this->belongsTo('\App\Models\Contract', 'contract_id');
    }

    public function user_verified()
    {
        return $this->belongsTo('\App\Models\User', 'user_verified_id');
    }


    public function availableForSale()
    {
//        if (
//            (empty($this->assigned_to)) &&
//            (empty($this->deleted_at)) &&
//            (($this->assetstatus) && ($this->assetstatus->deployable == 1))) {
//            return true;
//        }
        return true;
    }


}
