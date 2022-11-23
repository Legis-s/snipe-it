<?php

namespace App\Http\Controllers;

use App\Models\Location;

/**
 * This controller for map
 *
 * @version    v1.0
 */
class MapController extends Controller
{
    /**
     * Returns a view that shows map
     *
     * @author [S. Markin] [<markin@legis-s.ru>]
     */
    public function index()
    {
        $this->authorize('view', Location::class);
        return view('map/index');
    }

}