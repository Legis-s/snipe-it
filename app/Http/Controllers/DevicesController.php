<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class DevicesController extends Controller
{

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the devices listing, which is generated in getDatatable.
     */
    public function index(): View
    {
        $this->authorize('view', Location::class);
        return view('devices/index');
    }

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the devices detail page.
     */
    public function show(Device $device): View|RedirectResponse
    {
        $device = Device::find($device->id);

        if (isset($device->id)) {
            $asset = null;
            if ($device->asset) {
                $asset = $device->asset;
            }
            $asset_sim = null;
            if ($device->asset_sim) {
                $asset_sim = $device->asset_sim;
            }
            return view('devices/view', compact('device', 'asset', 'asset_sim'));
        }

        return redirect()->route('devices.index')->with('error', trans('admin/locations/message.does_not_exist'));
    }

}