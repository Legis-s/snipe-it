<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Location;

/**
 * This controller handles all actions related to Contract for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class ContractsController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the locations listing, which is generated in getDatatable.
     *
     * @author [S. MArkin] [<markin@legis-s.ru>]
     * @see ContractsController::getDatatable() method that generates the JSON response
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        // Grab all the locations
        $this->authorize('view', Location::class);
        // Show the page
        return view('contracts/index');
    }


    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the locations detail page.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $contractId
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     */
    public function show($contractId = null)
    {
        $contract = Contract::find($contractId);

        if (isset($contract->id)) {
            return view('contracts/view', compact('contract'));
        }

        return redirect()->route('contracts.index')->with('error', trans('admin/locations/message.does_not_exist'));
    }


}