<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * This controller handles all actions related to Contract for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class DealsController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the locations listing, which is generated in getDatatable.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v1.0]
     */
    public function index(): View
    {
        // Grab all the locations
        $this->authorize('view', Location::class);
        // Show the page
        return view('deals/index');
    }



    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the locations detail page.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $id
     * @since [v1.0]
     */
    public function show($id = null): View | RedirectResponse
    {
        $deal = Deal::find($id);

        if (isset($deal->id)) {
            return view('deals/view', compact('deal'));
        }

        return redirect()->route('deals.index')->with('error', trans('admin/locations/message.does_not_exist'));
    }


}