<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\Location;

class DevicesController extends Controller
{

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the locations listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @see LocationsController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        // Grab all the locations
        $this->authorize('view', Location::class);
        // Show the page
        return view('devices/index');
    }



    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the locations detail page.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $id
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     */
    public function show($id = null)
    {
        $device = Device::find($id);

        if (isset($device->id)) {
            return view('devices/view', compact('device'));
        }

        return redirect()->route('devices.index')->with('error', trans('admin/locations/message.does_not_exist'));
    }

}