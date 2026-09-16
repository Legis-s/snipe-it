<?php

use App\Http\Controllers\Api;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes CUSTOM
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['prefix' => 'v1', 'middleware' => ['api', 'api-throttle:api']], function () {

    /**
     * Consumable API routes
     */
    Route::group(['prefix' => 'consumables'], function () {
        Route::post('{id}/review',
            [
                Api\ConsumableReceiptController::class,
                'store',
            ]
        )->name('api.consumables.review');
        Route::post('{id}/compact',
            [
                Api\ConsumableCompactController::class,
                'store',
            ]
        )->name('api.consumables.compact');
    }); // end Consumables API routes

    /**
     * Assets API routes
     */
    Route::group(['prefix' => 'hardware'], function () {

        Route::post('{asset_id}/review',
            [
                Api\AssetPurchaseWorkflowController::class,
                'review',
            ]
        )->name('api.assets.review');

        Route::post('{asset_id}/inventory',
            [
                Api\AssetPurchaseWorkflowController::class,
                'inventory',
            ]
        )->name('api.assets.inventory');

    });

    /**
     * Inventories API routes
     */
    Route::group(['prefix' => 'inventories'], function () {

        Route::post('clearallemply',
            [
                Api\InventoriesController::class,
                'clearallemply',
            ]
        )->name('api.inventories.clearallemply');

    });

    Route::resource('inventories',
        Api\InventoriesController::class,
        ['names' => [
            'index' => 'api.inventories.index',
            'store' => 'api.inventories.store',
            'show' => 'api.inventories.show',
            'update' => 'api.inventories.update',
        ],
            'only' => ['index', 'store', 'show', 'update'],
            'parameters' => ['inventory' => 'inventory_id'],
        ]
    ); // end Inventories API routes

    /**
     * Inventory items API routes
     */
    Route::resource('inventory_items',
        Api\InventoryItemController::class,
        ['names' => [
            'index' => 'api.inventory_items.index',
            'show' => 'api.inventory_items.show',
            'update' => 'api.inventory_items.update',
        ],
            'only' => ['index', 'show', 'update'],
            'parameters' => ['inventory_item' => 'inventory_item_id'],
        ]
    ); // end Inventory items API routes

    /**
     * Inventory status labels API routes
     */
    Route::resource('inventorystatuslabels',
        Api\InventoryStatuslabelsController::class,
        ['names' => [
            'index' => 'api.inventorystatuslabels.index',

        ],
            'only' => ['index'],
            'parameters' => ['inventorystatuslabel' => 'iinventorystatuslabel_id'],
        ]
    ); // end inventory status labels API routes

    /**
     * Devices API routes
     */
    Route::resource('devices',
        Api\DevicesController::class,
        ['names' => [
            'index' => 'api.devices.index',
            'show' => 'api.devices.show',
        ],
            'only' => ['index', 'show'],
            'parameters' => ['device' => 'device_id'],
        ]
    ); // end Inventories API routes

    /**
     * Map API routes
     */
    Route::resource('map',
        Api\MapController::class,
        ['names' => [
            'index' => 'api.map.index',
        ],
            'only' => ['index'],
        ]
    ); // end Map API routes

    /**
     * Purchases API routes
     */
    Route::group(['prefix' => 'purchases'], function () {

        Route::post('{purchase}/paid',
            [
                Api\PurchasesController::class,
                'paid',
            ]
        )->name('api.purchases.paid');

        Route::post('{purchase}/consumables_check',
            [
                Api\PurchasesController::class,
                'consumables_check',
            ]
        )->name('api.purchases.consumables_check');

        Route::post('{purchase}/consumables_line',
            [
                Api\PurchasesController::class,
                'update_consumables_line',
            ]
        )->name('api.purchases.consumables_line');

        Route::post('{purchase}/in_payment',
            [
                Api\PurchasesController::class,
                'in_payment',
            ]
        )->name('api.purchases.in_payment');

        Route::post('{purchase}/reject',
            [
                Api\PurchasesController::class,
                'reject',
            ]
        )->name('api.purchases.reject');

        Route::post('{purchase}/resend',
            [
                Api\PurchasesController::class,
                'resend',
            ]
        )->name('api.purchases.resend');

        Route::post('{purchase}/bitrix_task/{bitrix_task}',
            [
                Api\PurchasesController::class,
                'bitrix_task',
            ]
        )->name('api.purchases.bitrix_task');
    });

    Route::resource('purchases',
        Api\PurchasesController::class,
        ['names' => [
            'index' => 'api.purchases.index',
            'show' => 'api.purchases.show',
        ],
            'only' => ['index', 'show'],
            'parameters' => ['purchase' => 'purchase_id'],
        ]
    ); // end Purchases API routes

    /**
     * InvoiceTypes API routes
     */
    Route::group(['prefix' => 'invoice_types'], function () {

        Route::get('selectlist',
            [
                Api\InvoiceTypesController::class,
                'selectlist',
            ]
        )->name('api.invoice_types.selectlist');

    });
    Route::resource('invoice_types',
        Api\InvoiceTypesController::class,
        ['names' => [
            'index' => 'api.invoice_types.index',
        ],
            'only' => ['index'],
            'parameters' => ['invoice_type' => 'invoice_type_id'],
        ]
    ); // end InvoiceTypes API routes

    /**
     * LegalPersons API routes
     */
    Route::group(['prefix' => 'legal_persons'], function () {

        Route::get('selectlist',
            [
                Api\LegalPersonsController::class,
                'selectlist',
            ]
        )->name('api.legal_persons.selectlist');

    }); // end LegalPersons API routes

    /**
     * Contracts API routes
     */
    Route::group(['prefix' => 'contracts'], function () {

        Route::get('selectlist',
            [
                Api\ContractsController::class,
                'selectlist',
            ]
        )->name('api.contracts.selectlist');

    });

    Route::resource('contracts',
        Api\ContractsController::class,
        ['names' => [
            'index' => 'api.contracts.index',
            'show' => 'api.contracts.show',
        ],
            'only' => ['index', 'show'],
            'parameters' => ['contract' => 'contract_id'],
        ]
    ); // end Contracts API routes

    /**
     * Deals API routes
     */
    Route::group(['prefix' => 'deals'], function () {

        Route::get('selectlist',
            [
                Api\DealsController::class,
                'selectlist',
            ]
        )->name('api.deals.selectlist');

    });
    Route::resource('deals',
        Api\DealsController::class,
        ['names' => [
            'index' => 'api.deals.index',
            'show' => 'api.deals.show',
        ],
            'only' => ['index', 'show'],
            'parameters' => ['deal' => 'deal_id'],
        ]
    ); // end Deals API routes

    /**
     * BitrixSync API routes
     */
    Route::group(['prefix' => 'bitrix_sync'], function () {

        Route::post('users',
            [
                Api\BitrixSyncController::class,
                'syncUsers',
            ]
        )->name('api.bitrix_sync.users');

        Route::post('locations',
            [
                Api\BitrixSyncController::class,
                'syncLocations',
            ]
        )->name('api.bitrix_sync.locations');

        Route::post('suppliers',
            [
                Api\BitrixSyncController::class,
                'syncSuppliers',
            ]
        )->name('api.bitrix_sync.suppliers');

        Route::post('legal_persons',
            [
                Api\BitrixSyncController::class,
                'syncLegalPersons',
            ]
        )->name('api.bitrix_sync.legal_persons');

        Route::post('invoice_types',
            [
                Api\BitrixSyncController::class,
                'syncInvoiceTypes',
            ]
        )->name('api.bitrix_sync.invoice_types');

    }); // end BitrixSync API routes

    /**
     * ConsumableAssignment API routes
     */
    Route::group(['prefix' => 'consumableassignments'], function () {

        Route::post('{id}/return',
            [
                Api\ConsumableAssignmentController::class,
                'return',
            ]
        )->name('api.consumableassignments.return');

    });

    Route::resource('consumableassignments',
        Api\ConsumableAssignmentController::class,
        ['names' => [
            'index' => 'api.consumableassignments.index',
        ],
            'only' => ['index'],
            'parameters' => ['consumableassignment' => 'consumableassignment_id'],
        ]
    ); // end ConsumableAssignment API routes
});
