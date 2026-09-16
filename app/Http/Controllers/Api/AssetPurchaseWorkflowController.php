<?php

namespace App\Http\Controllers\Api;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\AssetsTransformer;
use App\Models\Asset;
use App\Models\Setting;
use App\Models\Statuslabel;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class AssetPurchaseWorkflowController extends Controller
{
    /**
     * Returns JSON with information about an asset for detail view.
     *
     * @throws \Exception
     */
    public function inventory(int $id): JsonResponse
    {

        $this->authorize('update', Asset::class);
        $asset = Asset::with('status')->withTrashed()->findOrFail($id);
        $asset_tag = request('asset_tag');
        if ($asset) {

            $originalValues = $asset->getRawOriginal();
            $note = 'Инвентризация после покупки';
            $status = Statuslabel::where('name', 'Ожидает проверки')->firstOrFail();
            if (isset($asset_tag)) {
                $asset->asset_tag = $asset_tag;
            }
            $asset->status_id = $status->id;

            if ($asset->isValid() && Asset::withoutEvents(fn (): bool => $asset->save())) {
                $asset->logTag($note, $originalValues);

                return response()->json((new AssetsTransformer)->transformAsset($asset));

            }
        }

        return response()->json(Helper::formatStandardApiResponse('error', ['asset_tag' => e($asset->asset_tag)], 'Asset with tag '.e($asset->asset_tag).' not found'));
    }

    /**
     * Mark an asset as audited
     */
    public function review(int $id): array|JsonResponse
    {
        $this->authorize('review', Asset::class);
        $asset = Asset::with('status')->withTrashed()->findOrFail($id);

        $settings = Setting::getSettings();
        $dt = Carbon::now()->addMonths($settings->audit_interval)->toDateString();

        if ($asset) {
            // We don't want to log this as a normal update, so let's bypass that

            $note = 'Проверка после покупки';
            $asset->purchase_date = date('Y-m-d');
            $asset->next_audit_date = $dt;
            $asset->last_audit_date = date('Y-m-d H:i:s');
            $user = auth()->user();
            $asset->user_verified_id = $user->id;
            $status = Statuslabel::where('name', 'Доступные')->firstOrFail();
            $asset->status_id = $status->id;

            if ($asset->isValid() && Asset::withoutEvents(fn (): bool => $asset->save())) {
                $asset->logAudit($note, request('location_id'));

                return (new AssetsTransformer)->transformAsset($asset);
            }
        }

        return response()->json(Helper::formatStandardApiResponse('error', ['asset_tag' => e($asset->asset_tag)], 'Asset with tag '.e($asset->asset_tag).' not found'));
    }
}
