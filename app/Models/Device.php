<?php

namespace App\Models;
use App\Models\SnipeModel;
use App\Models\Traits\Searchable;
use App\Presenters\Presentable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends SnipeModel
{

    use HasFactory;

    protected $presenter = \App\Presenters\DevicePresenter::class;

    use Presentable;
    use SoftDeletes;
    protected $dates = ['lastUpdate'];

    protected $table = 'devices';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'number',
        'mdm_id',
        'statusCode',
        'description',
        'deviceId',
        'info_imei',
        'batteryLevel',
        'model',
        'imei',
        'lastUpdate',
        'launcherVersion',
        'androidVersion',
        'biometrikaVersion',
        'serial',
        'asset_id',
        'asset_sim_id',
    ];

    use Searchable;


    /**
     * The attributes that should be included when searching the model.
     *
     * @var array
     */
    protected $searchableAttributes = ['number', 'model', 'description'];


    /**
     * Get the user that owns the phone.
     */
    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user that owns the phone.
     */
    public function asset_sim(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }
}