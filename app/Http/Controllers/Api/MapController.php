<?php


namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Transformers\LocationsTransformer;
use App\Models\Location;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MapController extends Controller
{
    /**
     * Display a list feature for a map.
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request): JsonResponse|array
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
        ])   ->whereIn('object_code', [843, 847,848])
//            ->where('object_code', '=', 843)
//            ->orWhere('object_code', '=', 847)
//            ->orWhere('object_code', '=', 848)
//            ->where('active', "=", true)
//            ->withCount(['assets as assets_count',
//                'assets as checked_assets_count' => function (Builder $query) {
//                    $query->whereNotNull('assets.last_audit_date');
//                }])
            ->get();
        return (new LocationsTransformer)->transformCollectionForMap($locations);
    }
}