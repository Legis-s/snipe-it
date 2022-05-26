<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\AssetRequest;
use App\Http\Requests\AssetCheckoutRequest;
use App\Http\Transformers\AssetsTransformer;
use App\Http\Transformers\SalesTransformer;
use App\Models\Actionlog;
use App\Models\Asset;
use App\Models\AssetModel;
use App\Models\Company;
use App\Models\Contract;
use App\Models\CustomField;
use App\Models\Location;
use App\Models\Sale;
use App\Models\Setting;
use App\Models\Statuslabel;
use App\Models\User;
use Artisan;
use Auth;
use Carbon\Carbon;
use Config;
use DB;
use Gate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Input;
use Lang;
use Log;
use Mail;
use Paginator;
use Response;
use Slack;
use Str;
use TCPDF;
use Validator;
use View;
use App\Http\Transformers\SelectlistTransformer;


class BulkAssetsController extends Controller
{
    /**
     * Accepts a POST request to create a new asset
     *
     * @param Request $request
     * @return JsonResponse
     * @since [v4.0]
     * @author [A. Gianotto] [<snipe@snipe.net>]
     */
    public function bulkCheckout(Request $request)
    {

        $this->authorize('checkout', Asset::class);


        switch ($request->get('checkout_to_type')) {
            case 'location':
                $target = Location::findOrFail($request->get('assigned_location'));
                break;
            case 'asset':
                $target = Asset::findOrFail($request->get('assigned_asset'));
                break;
            case 'user':
                $target = User::findOrFail($request->get('assigned_user'));
                break;
            case 'contract':
                $target = Contract::findOrFail($request->get('assigned_contract'));
                break;
        }

        if (!is_array($request->get('selected_assets'))) {
            return response()->json(Helper::formatStandardApiResponse('error',null,"selected_assets not array"));
        }


        $asset_ids = array_filter($request->get('selected_assets'));
        foreach ($asset_ids as $asset_id) {
            if ($target->id == $asset_id && request('checkout_to_type') =='asset') {
                return response()->json(Helper::formatStandardApiResponse('error',null,"You cannot check an asset out to itself."));
            }
        }

        $checkout_at = date("Y-m-d H:i:s");
        if (($request->filled('checkout_at')) && ($request->get('checkout_at')!= date("Y-m-d"))) {
            $checkout_at = e($request->get('checkout_at'));
        }

        $errors = [];

        $admin=null;
        $expected_checkin = '';

        \Illuminate\Support\Facades\DB::transaction(function () use ($target, $admin, $checkout_at, $expected_checkin, $errors, $asset_ids, $request) {

            foreach ($asset_ids as $asset_id) {
                $asset = Asset::findOrFail($asset_id);
                $this->authorize('checkout', $asset);
                $error = $asset->checkOut($target, $admin, $checkout_at, $expected_checkin, e($request->get('note')), null);

                if ($target->location_id!='') {
                    $asset->location_id = $target->location_id;
                    $asset->unsetEventDispatcher();
                    $asset->save();
                }

                if ($error) {
                    array_merge_recursive($errors, $asset->getErrors()->toArray());
                }
            }
        });
        if (!$errors) {
            return response()->json(Helper::formatStandardApiResponse('success',null,"Success"));
        }
        return response()->json(Helper::formatStandardApiResponse('error',null,"error"));

    }
}
