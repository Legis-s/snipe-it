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
                Api\ConsumablesController::class,
                'review'
            ]
        )->name('api.consumables.review');
        Route::post('{id}/compact',
            [
                Api\ConsumablesController::class,
                'compact'
            ]
        )->name('api.consumables.compact');
    });// end Consumables API routes


    /**
     * Assets API routes
     */
    Route::group(['prefix' => 'hardware'], function () {

        Route::post('{asset_id}/review',
            [
                Api\AssetsController::class,
                'review'
            ]
        )->name('api.assets.review');

        Route::post('{asset_id}/inventory',
            [
                Api\AssetsController::class,
                'inventory'
            ]
        )->name('api.assets.inventory');

        Route::post('{asset_id}/closesell',
            [
                Api\AssetsController::class,
                'closesell'
            ]
        )->name('api.assets.closesell');

    });

    /**
     * Inventories API routes
     */
    Route::group(['prefix' => 'inventories'], function () {

        Route::post('clearallemply',
            [
                Api\InventoriesController::class,
                'clearallemply'
            ]
        )->name('api.inventories.clearallemply');

    });

    Route::resource('inventories',
        Api\InventoriesController::class,
        ['names' =>
            [
                'index' => 'api.inventories.index',
                'create' => 'api.inventories.create',
                'store' => 'api.inventories.store',
                'show' => 'api.inventories.show',
                'edit' => 'api.inventories.edit',
                'update' => 'api.inventories.update',
                'destroy' => 'api.inventories.destroy'
            ],
            'except' => ['edit'],
            'parameters' => ['inventory' => 'inventory_id'],
        ]
    ); // end Inventories API routes

    /**
     * Inventory items API routes
     */

    Route::resource('inventory_items',
        Api\InventoryItemController::class,
        ['names' =>
            [
                'index' => 'api.inventory_items.index',
                'show' => 'api.inventory_items.show',
                'update' => 'api.inventory_items.update',
            ],
            'except' => ['create', 'edit'],
            'parameters' => ['inventory_item' => 'inventory_item_id'],
        ]
    ); // end Inventory items API routes

    /**
     * Inventory status labels API routes
     */

    Route::resource('inventorystatuslabels',
        Api\InventoryStatuslabelsController::class,
        ['names' =>
            [
                'index' => 'api.inventorystatuslabels.index',
                'create' => 'api.inventorystatuslabels.create',
                'store' => 'api.inventorystatuslabels.store',
                'show' => 'api.inventorystatuslabels.show',
                'edit' => 'api.inventorystatuslabels.edit',
                'update' => 'api.inventorystatuslabels.update',
                'destroy' => 'api.inventorystatuslabels.destroy'

            ],
            'except' => ['create', 'edit'],
            'parameters' => ['inventorystatuslabel' => 'iinventorystatuslabel_id'],
        ]
    ); // end inventory status labels API routes


    /**
     * Devices API routes
     */

    Route::resource('devices',
        Api\DevicesController::class,
        ['names' =>
            [
                'index' => 'api.devices.index',
                'create' => 'api.devices.create',
                'store' => 'api.devices.store',
                'show' => 'api.devices.show',
                'edit' => 'api.devices.edit',
                'update' => 'api.devices.update',
                'destroy' => 'api.devices.destroy'
            ],
            'except' => ['create', 'edit'],
            'parameters' => ['device' => 'device_id'],
        ]
    ); // end Inventories API routes

    /**
     * Map API routes
     */

    Route::resource('map',
        Api\MapController::class,
        ['names' =>
            [
                'index' => 'api.map.index',
            ],
        ]
    );// end Map API routes

    /**
     * Purchases API routes
     */
    Route::group(['prefix' => 'purchases'], function () {

        Route::post('{purchase}/paid',
            [
                Api\PurchasesController::class,
                'paid'
            ]
        )->name('api.purchases.paid');

        Route::post('{purchase}/consumables_check',
            [
                Api\PurchasesController::class,
                'consumables_check'
            ]
        )->name('api.purchases.consumables_check');


        Route::post('{purchase}/in_payment',
            [
                Api\PurchasesController::class,
                'in_payment'
            ]
        )->name('api.purchases.in_payment');

        Route::post('{purchase}/reject',
            [
                Api\PurchasesController::class,
                'reject'
            ]
        )->name('api.purchases.reject');

        Route::post('{purchase}/resend',
            [
                Api\PurchasesController::class,
                'resend'
            ]
        )->name('api.purchases.resend');

        Route::post('{purchase}/bitrix_task/{bitrix_task}',
            [
                Api\PurchasesController::class,
                'bitrix_task'
            ]
        )->name('api.purchases.bitrix_task');
    });

    Route::resource('purchases',
        Api\PurchasesController::class,
        ['names' =>
            [
                'index' => 'api.purchases.index',
                'show' => 'api.purchases.show',
                'store' => 'api.purchases.store',
                'update' => 'api.purchases.update',
                'destroy' => 'api.purchases.destroy'
            ],
            'except' => ['create', 'edit'],
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
                'selectlist'
            ]
        )->name('api.invoice_types.selectlist');


    });
    Route::resource('invoice_types',
        Api\InvoiceTypesController::class,
        ['names' =>
            [
                'index' => 'api.invoice_types.index',
            ],
            'except' => ['create', 'edit'],
            'parameters' => ['invoice_type' => 'invoice_type_id'],
        ]
    );// end InvoiceTypes API routes

    /**
     * LegalPersons API routes
     */
    Route::group(['prefix' => 'legal_persons'], function () {

        Route::get('selectlist',
            [
                Api\LegalPersonsController::class,
                'selectlist'
            ]
        )->name('api.legal_persons.selectlist');


    });// end LegalPersons API routes


    /**
     * Contracts API routes
     */
    Route::group(['prefix' => 'contracts'], function () {

        Route::get('selectlist',
            [
                Api\ContractsController::class,
                'selectlist'
            ]
        )->name('api.contracts.selectlist');

    });

    Route::resource('contracts',
        Api\ContractsController::class,
        ['names' =>
            [
                'index' => 'api.contracts.index',
                'show' => 'api.contracts.show',
                'store' => 'api.contracts.store',
                'update' => 'api.contracts.update',
                'destroy' => 'api.contracts.destroy'
            ],
            'except' => ['create', 'edit'],
            'parameters' => ['contract' => 'contract_id'],
        ]
    );// end Contracts API routes


    /**
     * Deals API routes
     */
    Route::group(['prefix' => 'deals'], function () {

        Route::get('selectlist',
            [
                Api\DealsController::class,
                'selectlist'
            ]
        )->name('api.deals.selectlist');

    });
    Route::resource('deals',
        Api\DealsController::class,
        ['names' =>
            [
                'index' => 'api.deals.index',
                'show' => 'api.deals.show',
                'store' => 'api.deals.store',
                'update' => 'api.deals.update',
                'destroy' => 'api.deals.destroy'
            ],
            'except' => ['create', 'edit'],
            'parameters' => ['deal' => 'deal_id'],
        ]
    );// end Deals API routes

    /**
     * BitrixSync API routes
     */
    Route::group(['prefix' => 'bitrix_sync'], function () {

        Route::post('users',
            [
                Api\BitrixSyncController::class,
                'syncUsers'
            ]
        )->name('api.bitrix_sync.users');

        Route::post('locations',
            [
                Api\BitrixSyncController::class,
                'syncLocations'
            ]
        )->name('api.bitrix_sync.locations');

        Route::post('suppliers',
            [
                Api\BitrixSyncController::class,
                'syncSuppliers'
            ]
        )->name('api.bitrix_sync.suppliers');

        Route::post('legal_persons',
            [
                Api\BitrixSyncController::class,
                'syncLegalPersons'
            ]
        )->name('api.bitrix_sync.legal_persons');

        Route::post('invoice_types',
            [
                Api\BitrixSyncController::class,
                'syncInvoiceTypes'
            ]
        )->name('api.bitrix_sync.invoice_types');

    });// end BitrixSync API routes

    /**
     * ConsumableAssignment API routes
     */

    Route::group(['prefix' => 'consumableassignments'], function () {

        Route::post('{id}/return',
            [
                Api\ConsumableAssignmentController::class,
                'return'
            ]
        )->name('api.consumableassignments.return');

        Route::post('{id}/close_documents',
            [
                Api\ConsumableAssignmentController::class,
                'close_documents'
            ]
        )->name('api.consumableassignments.close_documents');


    });

    Route::resource('consumableassignments',
        Api\ConsumableAssignmentController::class,
        ['names' =>
            [
                'index' => 'api.consumableassignments.index',
            ],
            'except' => ['create', 'edit'],
            'parameters' => ['consumableassignment' => 'consumableassignment_id'],
        ]
    );// end ConsumableAssignment API routes
});