<?php


namespace App\Http\Controllers\Api;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Location;
use App\Http\Transformers\LocationsTransformer;
use App\Http\Transformers\SelectlistTransformer;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class MapController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $licenseId
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $this->authorize('view', Location::class);

        $locations = Location::with('assets')->select([
            'locations.id',
            'locations.name',
            'locations.address',
            'locations.coordinates',
            'locations.bitrix_id',
            'locations.object_code',
            'locations.active',
        ]) ->where('object_code', '=', 843)
            ->orWhere('object_code', '=', 847)
            ->orWhere('object_code', '=', 848)
            ->where('active',"=", true)
            ->withCount(['assets as assets_count',
                'assets as checked_assets_count' => function (Builder $query) {
                $query->whereNotNull('assets.last_audit_date');
            }]);


        $locations = $locations->get();
        return (new LocationsTransformer)->transformCollectionForMap($locations);
    }


}