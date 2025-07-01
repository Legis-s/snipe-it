<?php

namespace App\Http\Controllers\Assets;

use App\Helpers\Helper;
use App\Http\Controllers\CheckInOutRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetCheckoutRequest;
use App\Http\Requests\AssetSellRequest;
use App\Models\Asset;
use App\Models\Deal;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class BulkSellAssetsController extends Controller
{
    use CheckInOutRequest;

    /**
     * Show Bulk Sell Page
     */
    public function showCheckout() : View
    {
        $this->authorize('checkout', Asset::class);
        return view('hardware/bulk-sell');
    }

    /**
     * Process Multiple Sell Request
     */
    public function storeCheckout(AssetSellRequest $request) : RedirectResponse | ModelNotFoundException
    {
        $this->authorize('checkout', Asset::class);

        try {
            $admin = auth()->user();

            $target = Deal::findOrFail(request('assigned_deal'));

            if (! is_array($request->get('selected_assets'))) {
                return redirect()->route('hardware.bulksell.show')->withInput()->with('error', trans('admin/hardware/message.checkout.no_assets_selected'));
            }

            $asset_ids = array_filter($request->get('selected_assets'));

            $assets = Asset::findOrFail($asset_ids);

            if (request('checkout_to_type') == 'asset') {
                foreach ($asset_ids as $asset_id) {
                    if ($target->id == $asset_id) {
                        return redirect()->back()->with('error', 'You cannot check an asset out to itself.');
                    }
                }
            }
            $checkout_at = date('Y-m-d H:i:s');
            if (($request->filled('checkout_at')) && ($request->get('checkout_at') != date('Y-m-d'))) {
                $checkout_at = $request->get('checkout_at');
            }

            $errors = [];
            DB::transaction(function () use ($target, $admin, $checkout_at, $errors, $assets, $request) {
                foreach ($assets as $asset) {
                    $this->authorize('checkout', $asset);


                    $checkout_success = $asset->sell($target, $admin, $checkout_at, '', e($request->get('note')), $asset->name, null);

//                    //TODO - I think this logic is duplicated in the checkOut method?
//                    if ($target->location_id != '') {
//                        $asset->location_id = $target->location_id;
//                        // TODO - I don't know why this is being saved without events
//                        $asset::withoutEvents(function () use ($asset) {
//                            $asset->save();
//                        });
//                    }

                    if (!$checkout_success) {
                        $errors = array_merge_recursive($errors, $asset->getErrors()->toArray());
                    }
                }
            });

            if (! $errors) {
                // Redirect to the new asset page
                return redirect()->to('hardware')->with('success', trans_choice('admin/hardware/message.multi-checkout.success', $asset_ids));
            }
            // Redirect to the asset management page with error
            return redirect()->route('hardware.bulksell.show')->withInput()->with('error', trans_choice('admin/hardware/message.multi-checkout.error', $asset_ids))->withErrors($errors);
        } catch (ModelNotFoundException $e) {
            return redirect()->route('hardware.bulksell.show')->withInput()->with('error', trans_choice('admin/hardware/message.multi-checkout.error', $request->input('selected_assets')));
        }
        
    }
}
