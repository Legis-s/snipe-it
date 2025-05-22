<?php
namespace App\Http\Controllers;


use App\Helpers\Helper;
use App\Http\Transformers\ImportsTransformer;
use App\Models\Import;
use App\Models\Inventory;
use App\Models\Location;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoriesController extends Controller
{
    public function index()
    {
        // Grab all the locations
        $this->authorize('view', Location::class);
        return view('inventories/index');
    }

    /**
     * Returns a view that invokes the ajax tables which actually contains
     * the content for the locations detail page.
     *
     * @param int $id
     */
    public function show(Inventory $inventory): View | RedirectResponse
    {
        // Grab all the locations
        $this->authorize('view', Location::class);

        $inventory = Inventory::find($inventory->id);

        if (isset($inventory->id)) {
            return view('inventories/view', compact('inventory'));
        }

        return redirect()->route('inventories.index')->with('error', trans('admin/locations/message.does_not_exist'));
    }


    /**
     * Remove the specified resource from storage.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @since [v4.0]
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (Auth::user()->isSuperUser()) {
            $inventory = Inventory::findOrFail($id);

            $inventory->inventory_items()->forceDelete();
            $inventory->forceDelete();

//            $inventory->forceDelete();
        }

        return redirect()->to(route('inventories.index'))->with('success', trans('admin/locations/message.delete.success'));
    }
}