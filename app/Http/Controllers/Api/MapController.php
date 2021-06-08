<?php


namespace App\Http\Controllers\Api;

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
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
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
            'locations.active',
        ])->withCount('assignedAssets as assigned_assets_count')
            ->withCount('assets as assets_count');

        $locations = $locations->get();
        return (new LocationsTransformer)->transformCollectionForMap($locations);
    }


}