<?php
namespace App\Http\Controllers;


use App\Helpers\Helper;
use App\Http\Transformers\ImportsTransformer;
use App\Models\Import;
use App\Models\Inventory;
use App\Models\Location;
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
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $inventoryId
     * @since [v1.0]
     * @return \Illuminate\Contracts\View\View
     */
    public function show($inventoryId = null)
    {
        // Grab all the locations
        $this->authorize('view', Location::class);

        $inventory = Inventory::find($inventoryId);

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