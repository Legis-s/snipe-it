<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceTypesController;
use App\Http\Controllers\PurchasesController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MassOperationsController;
use App\Http\Controllers\InventoriesController;
use App\Http\Controllers\InventoryStatuslabelsController;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\ContractsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DealsController;
use App\Http\Controllers\Consumables;
use App\Http\Controllers\Assets\AssetRentController;
use App\Http\Controllers\Assets\AssetSellController;

Route::impersonate();

Route::group(['middleware' => 'auth'], function () {

    /**
     * InvoiceTypes
     */
    Route::resource('invoicetypes', InvoiceTypesController::class, [
        'parameters' => ['invoicetype' => 'invoicetype_id']
    ]);

    /**
     * Contracts
     */
    Route::resource('contracts', ContractsController::class, [
        'parameters' => ['contract' => 'contract_id']
    ]);

    /**
     * Deals
     */
    Route::resource('deals', DealsController::class, [
        'parameters' => ['deal' => 'deal_id']
    ]);

    /**
     * Inventories
     */
    Route::resource('inventories', InventoriesController::class, [
        'parameters' => ['inventory' => 'inventory_id']
    ]);

    /**
     * Inventory Status Labels
     */
    Route::resource('inventorystatuslabels', InventoryStatuslabelsController::class, [
        'parameters' => ['inventorystatuslabel' => 'inventorystatuslabel_id']
    ]);

    /**
     * Purchases
     */
    Route::get(
        'purchases/delete_all_rejected',
        [PurchasesController::class, 'deleteAllRejected']
    )->name('purchases.delete_all_rejected');

    Route::resource('purchases', PurchasesController::class, [
        'parameters' => ['purchase' => 'purchase_id']
    ]);

    Route::get(
        'purchases/{assetId}/clone',
        [PurchasesController::class, 'getClone']
    )->name('clone/purchases');

    /**
     * Map
     */
    Route::get(
        'map',
        [MapController::class, 'index']
    )->name('map');

    /**
     * Devices
     */
    Route::resource('devices', DevicesController::class, [
        'parameters' => ['device' => 'device_id']
    ]);

    /**
     * MassOperations
     */
    Route::group(
        [
            'prefix' => 'bulk',
        ],

        function () {
            // Bulk sell
            Route::get('sell',
                [MassOperationsController::class, 'showSell']
            )->name('bulk.sell.show');

            Route::post('sell',
                [MassOperationsController::class, 'storeSell']
            )->name('bulk.sell.store');

            // Bulk checkin
            Route::get('checkin',
                [MassOperationsController::class, 'showCheckin']
            )->name('bulk.checkin.show');

            Route::post('checkin',
                [MassOperationsController::class, 'storeCheckin']
            )->name('bulk.checkin.store');

            // Bulk checkout
            Route::get('checkout',
                [MassOperationsController::class, 'showCheckout']
            )->name('bulk.checkout.show');

            Route::post('checkout',
                [MassOperationsController::class, 'storeCheckout']
            )->name('bulk.checkout.store');
        }
    );
    Route::resource('bulk', MassOperationsController::class, [
        'parameters' => ['bulk' => 'bulk_id']
    ]);

});


/*
|--------------------------------------------------------------------------
| Asset Routes
|--------------------------------------------------------------------------
|
| Register all the asset routes.
|
*/
Route::group(
    [
        'prefix' => 'hardware',
        'middleware' => ['auth'],
    ],

    function () {
        // Sell
        Route::get('{assetId}/sell/',
            [AssetSellController::class, 'create']
        )->name('hardware.sell.create');

        Route::post('{assetId}/sell/',
            [AssetSellController::class, 'store']
        )->name('hardware.sell.store');

        // Rent
        Route::get('{assetId}/rent/',
            [AssetRentController::class, 'create']
        )->name('hardware.rent.create');

        Route::post('{assetId}/rent/',
            [AssetRentController::class, 'store']
        )->name('hardware.rent.store');

//        // Bulk sell
//        Route::get('bulksell',
//            [BulkAssetsController::class, 'showSell']
//        )->name('hardware.bulksell.show');
//
//        Route::post('bulksell',
//            [BulkAssetsController::class, 'storeSell']
//        )->name('hardware.bulksell.store');
//
//
//        // Bulk checkin
//        Route::get('bulkcheckin',
//            [BulkAssetsController::class, 'showCheckin']
//        )->name('hardware.bulkcheckin.show');
//
//        Route::post('bulkcheckin',
//            [BulkAssetsController::class, 'storeCheckin']
//        )->name('hardware.bulkcheckin.store');

    }
    );
/*
|--------------------------------------------------------------------------
| END Asset Routes
|--------------------------------------------------------------------------
*/


/*
|--------------------------------------------------------------------------
| Consumables Routes
|--------------------------------------------------------------------------
|
| Register all the consumables routes.
|
*/
Route::group(['prefix' => 'consumables', 'middleware' => ['auth']],

    function () {
        Route::get(
            'ncd',
            [Consumables\ConsumablesController::class, 'noclosingdocuments']
        )->name('consumables.ncd.index');

        Route::get(
            '{consumableId}/sell',
            [Consumables\ConsumableSellController::class, 'create']
        )->name('consumables.sell.show');

        Route::post(
            '{consumableId}/sell',
            [Consumables\ConsumableSellController::class, 'store']
        )->name('consumables.sell.store');

    }
);

/*
|--------------------------------------------------------------------------
| END Consumables Routes
|--------------------------------------------------------------------------
*/

Route::group(['middleware' => 'web'], function () {

    Route::get(
        'auth',
        [AuthController::class, 'getToken']
    )->name('token_get');

});

Route::get(
    '/bitrixAuth/',
    [AuthController::class, 'bitrixAuth']
)->name('bitrixAuth');