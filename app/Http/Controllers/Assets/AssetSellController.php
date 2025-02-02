<?php

namespace App\Http\Controllers\Assets;

use App\Exceptions\CheckoutNotAllowed;
use App\Helpers\Helper;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCheckoutRequest;
use App\Http\Requests\AssetSellRequest;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\Statuslabel;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetSellController extends Controller
{
    use CheckInOutRequest;

    /**
     * Returns a view that presents a form to check an asset out to a
     * user.
     *
     * @author [A. Gianotto] [<snipe@snipe.net>]
     * @param int $assetId
     * @since [v1.0]
     * @return View
     */
    public function create($assetId)
    {
        // Check if the asset exists
        if (is_null($asset = Asset::find(e($assetId)))) {
            return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
        }

        $this->authorize('checkout', $asset);

        if ($asset->availableForCheckout()) {
            return view('hardware/sell', compact('asset'))
                ->with('statusLabel_list', Helper::deployableStatusLabelList());
        }

        return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkout.not_available'));
    }

    /**
     * Validate and process the form data to check out an asset to a user.
     *
     * @author [S. Markin] [<markin@legis-s.ru>]
     * @param AssetSellRequest $request
     * @param int $assetId
     * @return Redirect
     * @since [v1.0]
     */
    public function store(AssetSellRequest $request, $assetId)
    {


        try {
            // Check if the asset exists
            if (! $asset = Asset::find($assetId)) {
                return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
            } elseif (! $asset->availableForCheckout()) {
                return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.checkout.not_available'));
            }
            $this->authorize('checkout', $asset);
            $admin = Auth::user();

            $target = $this->determineSellTarget($asset);


            $checkout_at = date('Y-m-d H:i:s');
            if (($request->filled('checkout_at')) && ($request->get('checkout_at') != date('Y-m-d'))) {
                $checkout_at = $request->get('checkout_at');
            }

            if ($asset->sell($target, $admin ,$checkout_at,e($request->get('contract_id')) ,e($request->get('note')), $request->get('name'))) {
                return redirect()->route('hardware.index')->with('success', trans('admin/hardware/message.checkout.success'));
            }

            return redirect()->to("hardware/$assetId/sell")->with('error', trans('admin/hardware/message.checkout.error').$asset->getErrors());

        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', trans('admin/hardware/message.checkout.error'))->withErrors($asset->getErrors());
        } catch (CheckoutNotAllowed $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


}
