<?php

namespace App\Http\Controllers;

use App\Models\Statuslabel;
use App\Models\InventoryStatuslabel;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use \Illuminate\Contracts\View\View;

/**
 * This controller handles all actions related to Status Labels for
 * the Snipe-IT Asset Management application.
 *
 * @version    v1.0
 */
class InventoryStatuslabelsController extends Controller
{
    /**
     * Show a list of all the statuslabels.
     */

    public function index(): View
    {
        $this->authorize('view', Statuslabel::class);
        return view('inventorystatuslabels.index');
    }

    public function show(InventoryStatuslabel $inventorystatuslabel): View|RedirectResponse
    {
        $this->authorize('view', Statuslabel::class);
        return view('inventorystatuslabels.view')->with('statuslabel', $inventorystatuslabel);
    }


    /**
     * InventoryStatuslabel create.
     *
     */
    public function create(): View
    {
        // Show the page
        $this->authorize('create', Statuslabel::class);

        return view('inventorystatuslabels/edit')
            ->with('item', new InventoryStatuslabel);
    }


    /**
     * InventoryStatuslabel create form processing.
     *
     * @param Request $request
     */
    public function store(Request $request): RedirectResponse
    {

        $this->authorize('create', Statuslabel::class);
        // create a new model instance
        $statusLabel = new InventoryStatuslabel();

        // Save the Statuslabel data
        $statusLabel->name =  $request->input('name');
        $statusLabel->created_by = auth()->id();
        $statusLabel->notes =  $request->input('notes');
        $statusLabel->color =  $request->input('color');
        $statusLabel->success = $request->input('success', 0);


        if ($statusLabel->save()) {
            // Redirect to the new Statuslabel  page
            return redirect()->route('inventorystatuslabels.index')->with('success', trans('admin/statuslabels/message.create.success'));
        }
        return redirect()->back()->withInput()->withErrors($statusLabel->getErrors());
    }

    /**
     * InventoryStatuslabel update.
     *
     * @param int $statuslabelId
     */
    public function edit(InventoryStatuslabel $inventorystatuslabel): View|RedirectResponse
    {
        $this->authorize('update', Statuslabel::class);
        // Check if the Statuslabel exists
//        if (is_null($item = InventoryStatuslabel::find($statuslabel->id))) {
//            // Redirect to the blogs management page
//            return redirect()->route('inventorystatuslabels.index')->with('error', trans('admin/statuslabels/message.does_not_exist'));
//        }

//        return view('inventorystatuslabels/edit', compact('item'));

        return view('inventorystatuslabels/edit')
            ->with('item', $inventorystatuslabel);
    }


    /**
     * Statuslabel update form processing page.
     *
     * @param int $inventorystatuslabelId
     */
    public function update(Request $request, InventoryStatuslabel $inventorystatuslabel): RedirectResponse
    {
        $this->authorize('update', Statuslabel::class);
        // Check if the Statuslabel exists
        if (is_null($inventorystatuslabel = InventoryStatuslabel::find($inventorystatuslabel->id))) {
            // Redirect to the blogs management page
            return redirect()->route('inventorystatuslabels.index')->with('error', trans('admin/statuslabels/message.does_not_exist'));
        }

        // Save the Statuslabel data
        $inventorystatuslabel->name =  $request->input('name');
        $inventorystatuslabel->created_by = auth()->id();
        $inventorystatuslabel->notes =  $request->input('notes');
        $inventorystatuslabel->color =  $request->input('color');
        $inventorystatuslabel->success = $request->input('success', 0);

        // Was the asset created?
        if ($inventorystatuslabel->save()) {
            // Redirect to the saved Statuslabel page
            return redirect()->route("inventorystatuslabels.index")->with('success', trans('admin/statuslabels/message.update.success'));
        }
        return redirect()->back()->withInput()->withErrors($inventorystatuslabel->getErrors());
    }

    /**
     * Delete the given Statuslabel.
     *
     * @param int $statuslabelId
     */
    public function destroy($statuslabelId): RedirectResponse
    {
        $this->authorize('delete', Statuslabel::class);
        // Check if the Statuslabel exists
        if (is_null($statuslabel = InventoryStatuslabel::find($statuslabelId))) {
            return redirect()->route('inventorystatuslabels.index')->with('error', trans('admin/statuslabels/message.not_found'));
        }

        $statuslabel->delete();
        return redirect()->route('inventorystatuslabels.index')->with('success', trans('admin/statuslabels/message.delete.success'));
    }

}
