<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImageUploadRequest;
use App\Models\Asset;
use App\Models\InvoiceType;
use App\Models\Location;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * This controller handles all actions related to InvoiceTypes for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class InvoiceTypesController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the locations listing, which is generated in getDatatable.
     *
     * @see InvoiceTypesController::getDatatable() method that generates the JSON response
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index()
    {
        // Grab all the locations
        $this->authorize('view', Location::class);
        // Show the page
        return view('invoicetypes/index');
    }

    /**
     * Makes a form view to edit location information.
     * @see InvoiceTypesController::postCreate() method that validates and stores
     * @param int $invoicetypeId
     * @return \Illuminate\Contracts\View\View
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function edit($invoicetypeId = null)
    {
        $this->authorize('update', Location::class);
        // Check if the location exists
        if (is_null($item = InvoiceType::find($invoicetypeId))) {
            return redirect()->route('invoicetypes.index')->with('error', trans('admin/locations/message.does_not_exist'));
        }

        return view('invoicetypes/edit', compact('item'));
    }

    /**
     * Validates and stores updated location data from edit form.
     * @see LocationsController::getEdit() method that makes the form view
     * @param InvoiceTypesController $request
     * @param int $invoicetypeId
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @since [v1.0]
     */
    public function update(ImageUploadRequest $request, $invoicetypeId = null)
    {
        $this->authorize('update', Location::class);
        // Check if the location exists
        if (is_null($invoiceType = InvoiceType::find($invoicetypeId))) {
            return redirect()->route('invoicetypes.index')->with('error', trans('admin/locations/message.does_not_exist'));
        }

        // Update the location data
        $invoiceType->active = $request->input('active');

        if ($invoiceType->save()) {
            return redirect()->route('invoicetypes.index')->with('success', trans('admin/locations/message.update.success'));
        }

        return redirect()->back()->withInput()->withInput()->withErrors($invoiceType->getErrors());
    }
}
