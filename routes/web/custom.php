<?php

use App\Http\Controllers\Assets\BulkSellAssetsController;
use App\Models\Asset;
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
use Tabuna\Breadcrumbs\Trail;

Route::impersonate();

Route::group(['middleware' => 'auth'], function () {

    /**
     * InvoiceTypes
     */
    Route::resource('invoicetypes', InvoiceTypesController::class);

    /**
     * Contracts
     */
    Route::resource('contracts', ContractsController::class);

    /**
     * Deals
     */
    Route::resource('deals', DealsController::class);

    /**
     * Inventories
     */
    Route::resource('inventories', InventoriesController::class);

    /**
     * Inventory Status Labels
     */
    Route::resource('inventorystatuslabels', InventoryStatuslabelsController::class);

    /**
     * Purchases
     */
    Route::get(
        'purchases/delete_all_rejected',
        [PurchasesController::class, 'deleteAllRejected']
    )->name('purchases.delete_all_rejected');


    Route::get(
        'purchases/{assetId}/clone',
        [PurchasesController::class, 'getClone']
    )->name('clone/purchases');

    Route::resource('purchases', PurchasesController::class);

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
    Route::resource('devices', DevicesController::class);


//    /**
//     * MassOperations
//     */
//    Route::group(
//        [
//            'prefix' => 'bulk',
//        ],
//
//        function () {
//            // Bulk sell
//            Route::get('sell',
//                [MassOperationsController::class, 'showSell']
//            )->name('bulk.sell.show');
//
//            Route::post('sell',
//                [MassOperationsController::class, 'storeSell']
//            )->name('bulk.sell.store');
//
//            // Bulk checkin
//            Route::get('checkin',
//                [MassOperationsController::class, 'showCheckin']
//            )->name('bulk.checkin.show');
//
//            Route::post('checkin',
//                [MassOperationsController::class, 'storeCheckin']
//            )->name('bulk.checkin.store');
//
//            // Bulk checkout
//            Route::get('checkout',
//                [MassOperationsController::class, 'showCheckout']
//            )->name('bulk.checkout.show');
//
//            Route::post('checkout',
//                [MassOperationsController::class, 'storeCheckout']
//            )->name('bulk.checkout.store');
//        }
//    );
//    /**
//     * bulk
//     */
//    Route::resource('bulk', MassOperationsController::class);

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
        // Bulk sell
        Route::get('bulksell', [BulkSellAssetsController::class, 'showCheckout'])
            ->name('hardware.bulksell.show')
            ->breadcrumbs(fn(Trail $trail) => $trail->parent('hardware.index')
                ->push(trans('general.bulk_sell'), route('hardware.index'))
            );

        Route::post('bulksell',
            [BulkSellAssetsController::class, 'storeCheckout']
        )->name('hardware.bulksell.store');


        //Sell
        Route::get('{asset}/sell', [AssetSellController::class, 'create'])
            ->name('hardware.sell.create')
            ->breadcrumbs(fn(Trail $trail, Asset $asset) => $trail->parent('hardware.show', $asset)
                ->push(trans('admin/hardware/general.sell'), route('hardware.index'))
            );
        Route::post('{assetId}/sell',
            [AssetSellController::class, 'store']
        )->name('hardware.sell.store');

        //Rent
        Route::get('{asset}/rent', [AssetRentController::class, 'create'])
            ->name('hardware.rent.create')
            ->breadcrumbs(fn(Trail $trail, Asset $asset) => $trail->parent('hardware.show', $asset)
                ->push(trans('admin/hardware/general.rent'), route('hardware.index'))
            );
        Route::post('{assetId}/rent',
            [AssetRentController::class, 'store']
        )->name('hardware.rent.store');

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


        Route::get(
            'bulkcheckout',
            [Consumables\BulkConsumablesController::class, 'create']
        )->name('consumables.bulkcheckout.show');

        Route::post(
            'bulkcheckout',
            [Consumables\BulkConsumablesController::class, 'store']
        )->name('consumables.bulkcheckout.store');


        Route::get(
            'bulksell',
            [Consumables\BulkSellConsumablesController::class, 'create']
        )->name('consumables.bulksell.show');

        Route::post(
            'bulksell',
            [Consumables\BulkSellConsumablesController::class, 'store']
        )->name('consumables.bulksell.store');


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