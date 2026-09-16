<?php

namespace App\Http\Traits;

use App\Models\Asset;
use App\Models\Deal;
use App\Models\Location;
use App\Models\SnipeModel;
use App\Models\User;

trait CheckInOutTrait
{
    /**
     * Find target for checkout
     */
    protected function determineCheckoutTarget(?\Illuminate\Http\Request $request = null): ?SnipeModel
    {
        $request ??= request();
        // This item is checked out to a location
        switch ($request->input('checkout_to_type')) {
            case 'location':
                return Location::findOrFail($request->input('assigned_location'));
            case 'asset':
                return Asset::findOrFail($request->input('assigned_asset'));
            case 'deal':
                return Deal::findOrFail($request->input('assigned_deal'));
            default:
                return User::findOrFail($request->input('assigned_user'));
        }
    }

    /**
     * Update the location of the asset passed in.
     *
     * @param  Asset  $asset  Asset being updated
     * @param  SnipeModel  $target  Target with location
     * @return Asset Asset being updated
     */
    protected function updateAssetLocation($asset, $target): Asset
    {
        switch (request('checkout_to_type')) {
            case 'location':
                $asset->location_id = $target->id;
                Asset::where('assigned_type', 'App\Models\Asset')->where('assigned_to', $asset->id)
                    ->update(['location_id' => $asset->location_id]);
                break;
            case 'asset':
                $asset->location_id = $target->rtd_location_id;
                // Override with the asset's location_id if it has one
                if ($target->location_id != '') {
                    $asset->location_id = $target->location_id;
                }
                break;
            case 'user':
                $asset->location_id = $target->location_id;
                break;
        }

        return $asset;
    }
}
