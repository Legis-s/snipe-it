<?php

namespace App\Http\Controllers\Api;

use App\Actions\Purchases\PayPurchaseAction;
use App\Actions\Purchases\ReceiveLegacyConsumablesAction;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Transformers\PurchasesTransformer;
use App\Models\Asset;
use App\Models\Consumable;
use App\Models\Purchase;
use App\Models\Statuslabel;
use DateTime;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class PurchasesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse|array
    {
        $this->authorize('view', Purchase::class);
        $status = Statuslabel::where('name', 'Доступные')->first();
        $purchases = Purchase::with('supplier', 'assets', 'invoice_type', 'legal_person', 'adminuser', 'consumables')
            ->select([
                'purchases.id',
                'purchases.invoice_number',
                'purchases.invoice_file',
                'purchases.bitrix_id',
                'purchases.final_price',
                'purchases.status',
                'purchases.supplier_id',
                'purchases.legal_person_id',
                'purchases.invoice_type_id',
                'purchases.comment',
                'purchases.currency',
                'purchases.created_by',
                'purchases.user_verified_id',
                'purchases.created_at',
                'purchases.updated_at',
                'purchases.deleted_at',
                'purchases.bitrix_task_id',
                'purchases.consumables_json',
                'purchases.delivery_cost',
            ])->withCount([
                'assets as assets_count',
                'assets as assets_count_ok' => function (Builder $query) use ($status) {
                    $query->where('status_id', $status->id);
                },
            ])->addSelect(['consumables_count' => Consumable::forLegacyPurchase(DB::raw('purchases.id'))->selectRaw('count(*)')]);

        if ($request->filled('search')) {
            $purchases = $purchases->TextSearch($request->input('search'));

        }
        if ($request->filled('created_by')) {
            $purchases->where('created_by', '=', $request->input('created_by'));
        }
        if ($request->filled('status')) {
            $purchases->where('status', '=', $request->input('status'));
        }
        if ($request->filled('supplier')) {
            $purchases->where('supplier_id', '=', $request->input('supplier'));
        }

        $allowed_columns =
            [
                'id', 'invoice_number', 'bitrix_id', 'final_price', 'status', 'created_at',
                'deleted_at',
            ];

        $order = $request->input('order') === 'asc' ? 'asc' : 'desc';
        $sort = in_array($request->input('sort'), $allowed_columns) ? $request->input('sort') : 'created_at';

        if ($request->input('not_finished_status')) {
            $purchases->where('status', '<>', 'finished');
        }

        $purchases->orderBy($sort, $order);
        // Set the offset to the API call's offset, unless the offset is higher than the actual count of items in which
        // case we override with the actual count, so we should return 0 items.
        $offset = (($purchases) && ($request->get('offset') > $purchases->count())) ? $purchases->count() : $request->get('offset', 0);

        // Check to make sure the limit is not higher than the max allowed
        ((config('app.max_results') >= $request->input('limit')) && ($request->filled('limit'))) ? $limit = $request->input('limit') : $limit = config('app.max_results');

        $total = $purchases->count();
        $purchases = $purchases->skip($offset)->take($limit)->get();

        return (new PurchasesTransformer)->transformPurchases($purchases, $total);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show($id): JsonResponse|array
    {
        $this->authorize('view', Purchase::class);
        $status = Statuslabel::where('name', 'Доступные')->first();
        $purchise = Purchase::with('supplier', 'assets', 'invoice_type', 'legal_person', 'consumables')
            ->select([
                'purchases.id',
                'purchases.invoice_number',
                'purchases.invoice_file',
                'purchases.bitrix_id',
                'purchases.final_price',
                'purchases.status',
                'purchases.supplier_id',
                'purchases.legal_person_id',
                'purchases.invoice_type_id',
                'purchases.comment',
                'purchases.currency',
                'purchases.created_by',
                'purchases.user_verified_id',
                'purchases.created_at',
                'purchases.deleted_at',
                'purchases.bitrix_task_id',
                'purchases.consumables_json',
            ])->withCount([
                'assets as assets_count',
                'assets as assets_count_ok' => function (Builder $query) use ($status) {
                    $query->where('status_id', $status->id);
                },
            ])->addSelect(['consumables_count' => Consumable::forLegacyPurchase(DB::raw('purchases.id'))->selectRaw('count(*)')])
            ->findOrFail($id);

        return (new PurchasesTransformer)->transformPurchase($purchise, true);
    }

    /**
     * Display a listing of the resource.
     */
    public function paid(Request $request, $purchaseId = null): JsonResponse
    {
        $this->authorize('update', Purchase::class);
        $purchase = PayPurchaseAction::run((int) $purchaseId);

        return response()->json(
            Helper::formatStandardApiResponse(
                'success',
                (new PurchasesTransformer)->transformPurchase($purchase),
                trans('admin/locations/message.update.success')
            )
        );
    }

    /**
     * Display a listing of the resource.
     */
    public function consumables_check(Request $request, $purchaseId = null): JsonResponse
    {
        $purchase = ReceiveLegacyConsumablesAction::run((int) $purchaseId);

        return response()->json(Helper::formatStandardApiResponse(
            'success', (new PurchasesTransformer)->transformPurchase($purchase),
            trans('admin/locations/message.update.success')
        ));
    }

    /**
     * Update or delete one not-yet-fully-reviewed consumable line in purchase JSON.
     */
    public function update_consumables_line(Request $request, $purchaseId = null): JsonResponse
    {
        $this->authorize('review');
        $data = $request->validate([
            'row_id' => 'required|integer|min:1',
            'action' => 'sometimes|required|in:update,delete',
            'quantity' => 'required_unless:action,delete|integer|min:1|max:2147483647',
            'purchase_cost' => 'required_unless:action,delete|numeric|min:0|max:99999999999999999.99',
            'nds' => 'sometimes|required|numeric|min:0',
        ]);
        $purchase = DB::transaction(function () use ($purchaseId, $data): Purchase {
            $purchase = Purchase::whereKey($purchaseId)->lockForUpdate()->firstOrFail();
            $lines = json_decode($purchase->consumables_json ?: '[]', true);
            if (! is_array($lines) || collect($lines)->contains(fn ($line): bool => ! is_array($line))) {
                throw ValidationException::withMessages(['row_id' => trans('general.purchase_line_invalid')]);
            }
            $matches = collect($lines)->filter(fn (array $line): bool => (string) ($line['id'] ?? '') === (string) $data['row_id']);
            if ($matches->count() !== 1) {
                throw ValidationException::withMessages(['row_id' => trans('general.purchase_line_invalid')]);
            }
            $index = $matches->keys()->first();
            $reviewed = (int) ($lines[$index]['reviewed'] ?? 0);
            if (($data['action'] ?? 'update') === 'delete') {
                if ($reviewed > 0) {
                    throw ValidationException::withMessages(['row_id' => trans('general.purchase_line_received')]);
                }
                unset($lines[$index]);
            } else {
                if ((int) $data['quantity'] < $reviewed) {
                    throw ValidationException::withMessages(['quantity' => trans('validation.min.numeric', ['attribute' => trans('general.quantity'), 'min' => $reviewed])]);
                }
                $lines[$index]['quantity'] = (int) $data['quantity'];
                $lines[$index]['purchase_cost'] = $data['purchase_cost'];
                $lines[$index]['nds'] = $data['nds'] ?? 0;
            }
            $purchase->consumables_json = json_encode(array_values($lines), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
            $purchase->checkStatus();
            if (! $purchase->save()) {
                throw ValidationException::withMessages($purchase->getErrors()->toArray());
            }

            return $purchase;
        });

        return response()->json(Helper::formatStandardApiResponse('success', [
            'purchase' => (new PurchasesTransformer)->transformPurchase($purchase),
            'consumables' => json_decode($purchase->consumables_json, true),
        ], trans('admin/consumables/message.update.success')));
    }

    /**
     * Display a listing of the resource.
     */
    public function in_payment(Request $request, $purchaseId = null): JsonResponse
    {
        $this->authorize('update', Purchase::class);
        $purchase = Purchase::findOrFail($purchaseId);
        $purchase->status = 'in_payment';
        if ($purchase->save()) {

            return response()->json(
                Helper::formatStandardApiResponse(
                    'success',
                    (new PurchasesTransformer)->transformPurchase($purchase),
                    trans('admin/locations/message.update.success')
                )
            );
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $purchase->getErrors()));
    }

    /**
     * Display a listing of the resource.
     */
    public function bitrix_task(Request $request, $purchaseId = null, $bitrix_task = null): JsonResponse|array
    {
        $this->authorize('update', Purchase::class);
        $purchase = Purchase::findOrFail($purchaseId);
        $purchase->bitrix_task_id = $bitrix_task;
        if ($purchase->save()) {
            return response()->json(
                Helper::formatStandardApiResponse(
                    'success',
                    (new PurchasesTransformer)->transformPurchase($purchase),
                    trans('admin/locations/message.update.success')
                )
            );
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $purchase->getErrors()));
    }

    /**
     * Display a listing of the resource.
     */
    public function reject(Request $request, $purchaseId = null): JsonResponse|array
    {
        $this->authorize('update', Purchase::class);
        $purchase = Purchase::findOrFail($purchaseId);
        $purchase->status = 'rejected';
        $purchase->bitrix_result_at = new DateTime;
        if ($purchase->save()) {
            $status = Statuslabel::where('name', 'Отклонено')->first();
            $assets = Asset::where('purchase_id', $purchase->id)->get();
            foreach ($assets as &$value) {
                $value->status_id = $status->id;
                $value->unsetEventDispatcher();
                $value->save();
            }

            return response()->json(
                Helper::formatStandardApiResponse(
                    'success',
                    (new PurchasesTransformer)->transformPurchase($purchase),
                    trans('admin/locations/message.update.success')
                )
            );
        }

        return response()->json(Helper::formatStandardApiResponse('error', null, $purchase->getErrors()));
    }

    /**
     * Display a listing of the resource.
     */
    public function resend(Request $request, $purchaseId = null): JsonResponse|array
    {
        $this->authorize('update', Purchase::class);
        $purchase = Purchase::findOrFail($purchaseId);

        if ($purchase->bitrix_id) {
            return response()->json(
                Helper::formatStandardApiResponse('error', null, trans('general.purchase_already_sent_to_bitrix')),
                422
            );
        }

        $user = $request->user();
        $raw_bitrix_token = $user?->decryptedBitrixToken();
        if (! $raw_bitrix_token || ! $user->bitrix_id) {
            $purchase->setStatusError();
            $purchase->save();

            return response()->json(
                Helper::formatStandardApiResponse('error', null, trans('admin/users/table.bitrix_token_invalid')),
                422
            );
        }

        try {
            $payloadPath = public_path('/uploads/purchases/'.$purchase->bitrix_send_json);
            if (! $purchase->bitrix_send_json || ! is_file($payloadPath)) {
                throw new RuntimeException('Saved Bitrix payload is missing.');
            }

            $params = json_decode(file_get_contents($payloadPath), true);
            if (! is_array($params)) {
                throw new RuntimeException('Saved Bitrix payload is invalid.');
            }

            $client = new \GuzzleHttp\Client;
            $response = $client->request('POST', env('BITRIX_URL').'rest/'.$user->bitrix_id.'/'.$raw_bitrix_token.'/lists.element.add.json/', $params);
            $responseBody = $response->getBody()->getContents();
            $bitrixResult = json_decode($responseBody, true);
            if (! is_array($bitrixResult) || empty($bitrixResult['result'])) {
                throw new RuntimeException('Bitrix did not return a purchase ID.');
            }

            $purchase->bitrix_result = $responseBody;
            $purchase->bitrix_id = $bitrixResult['result'];
            $purchase->setStatusInprogress();

            if (! $purchase->save()) {
                return response()->json(Helper::formatStandardApiResponse('error', null, $purchase->getErrors()), 422);
            }
        } catch (Throwable $exception) {
            $purchase->setStatusError();
            $purchase->save();
            report($exception);

            return response()->json(
                Helper::formatStandardApiResponse('error', null, trans('general.bitrix_send_failed')),
                502
            );
        }

        return response()->json(
            Helper::formatStandardApiResponse(
                'success',
                (new PurchasesTransformer)->transformPurchase($purchase),
                trans('general.bitrix_send_success')
            )
        );
    }
}
