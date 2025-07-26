<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * This controller handles all actions related to Contract for
 * the Snipe-IT Asset Management application.
 */
class ContractsController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the contract listing, which is generated in getDatatable.
     *
     * @see ContractsController::getDatatable() method that generates the JSON response
     */
    public function index(): View
    {
        // Grab all the locations
        $this->authorize('view', Location::class);
        // Show the page
        return view('contracts/index');
    }


    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the contract detail page.
     *
     * @param int $id
     */
    public function show(Contract $contract): View|RedirectResponse
    {
        $contract = Contract::find($contract->id);

        if (isset($contract->id)) {
            return view('contracts/view', compact('contract'));
        }

        return redirect()->route('contracts.index')->with('error', trans('admin/locations/message.does_not_exist'));
    }
}