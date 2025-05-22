<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * This controller handles all actions related to Deals for
 * the Snipe-IT Asset Management application.
 */
class DealsController extends Controller
{
    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the deals listing, which is generated in getDatatable.
     */
    public function index(): View
    {
        $this->authorize('view', Location::class);
        // Show the page
        return view('deals/index');
    }

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the deals detail page.
     */
    public function show(Deal $deal): View|RedirectResponse
    {
        $this->authorize('view', Location::class);

        $deal = Deal::find($deal->id);

        if (isset($deal->id)) {
            return view('deals/view', compact('deal'));
        }

        return redirect()->route('deals.index')->with('error', trans('admin/locations/message.does_not_exist'));
    }
}