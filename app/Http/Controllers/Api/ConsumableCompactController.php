<?php

namespace App\Http\Controllers\Api;

use App\Actions\Consumables\CompactConsumablesAction;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\ConsumableCompactRequest;
use App\Http\Transformers\ConsumablesTransformer;
use Illuminate\Http\JsonResponse;

class ConsumableCompactController extends Controller
{
    public function store(ConsumableCompactRequest $request, int $id): JsonResponse
    {
        $consumable = CompactConsumablesAction::run($id, array_map('intval', $request->validated('id_array')));

        return response()->json(Helper::formatStandardApiResponse(
            'success', (new ConsumablesTransformer)->transformConsumable($consumable),
            trans('admin/consumables/message.update.success')
        ));
    }
}
