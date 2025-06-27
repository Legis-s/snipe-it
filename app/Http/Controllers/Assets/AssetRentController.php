<?php

namespace App\Http\Controllers\Assets;

use App\Exceptions\CheckoutNotAllowed;
use App\Helpers\Helper;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetRentRequest;
use App\Models\Asset;
use App\Models\Deal;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;

class AssetRentController extends Controller
{
    use CheckInOutRequest;

    /**
     * Returns a view that presents a form to check an asset out to deal
     * user.
     * @param int $assetId
     */
    public function create(Asset $asset): View|RedirectResponse
    {
        $this->authorize('checkout', $asset);

        if (!$asset->model) {
            return redirect()->route('hardware.show', $asset)
                ->with('error', trans('admin/hardware/general.model_invalid_fix'));
        }

        if ($asset->isInvalid()) {
            return redirect()->route('hardware.edit', $asset)->withErrors($asset->getErrors());
        }

        if ($asset->availableForCheckout()) {
            return view('hardware/rent', compact('asset'))
                ->with('statusLabel_list', Helper::deployableStatusLabelList())
                ->with('table_name', 'Assets')
                ->with('item', $asset);
        }
        return redirect()->route('hardware.index')
            ->with('error', trans('admin/hardware/message.checkout.not_available'));
    }

    /**
     * Validate and process the form data to check out an asset to a user.
     * @param AssetRentRequest $request
     * @param int $assetId
     */
    public function store(AssetRentRequest $request, $assetId): RedirectResponse
    {
        try {
            // Check if the asset exists
            if (!$asset = Asset::find($assetId)) {
                return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.does_not_exist'));
            } elseif (!$asset->availableForCheckout()) {
                return redirect()->route('hardware.index')->with('error', trans('admin/hardware/message.rent.not_available'));
            }
            $this->authorize('checkout', $asset);
            $admin = auth()->user();

            $target = Deal::findOrFail(request('assigned_deal'));
            $asset->location_id = null;
            $asset->rtd_location_id = null;

            $checkout_at = date('Y-m-d H:i:s');
            if (($request->filled('checkout_at')) && ($request->get('checkout_at') != date('Y-m-d'))) {
                $checkout_at = $request->get('checkout_at');
            }

            if ($asset->rent($target, $admin, $checkout_at, $request->get('note'), $request->get('name'))) {
                return redirect()->to(Helper::getRedirectOption($request, $asset->id, 'Assets'))
                    ->with('success', trans('admin/hardware/message.rent.success'));
            }
            // Redirect to the asset management page with error
            return redirect()->route("hardware.rent.create", $asset)->with('error', trans('admin/hardware/message.rent.error') . $asset->getErrors());

        } catch (ModelNotFoundException $e) {
            return redirect()->back()->with('error', trans('admin/hardware/message.checkout.error'))->withErrors($asset->getErrors());
        } catch (CheckoutNotAllowed $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
