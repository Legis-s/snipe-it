<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceTypesController;
use App\Http\Controllers\PurchasesController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\InventoriesController;
use App\Http\Controllers\InventoryStatuslabelsController;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\ContractsController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\DealsController;
use App\Http\Controllers\Consumables;

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
});



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
            'bulkcheckout',
            [Consumables\BulkConsumablesController::class, 'create']
        )->name('consumables.bulkcheckout.show');

        Route::post(
            'bulkcheckout',
            [Consumables\BulkConsumablesController::class, 'store']
        )->name('consumables.bulkcheckout.store');
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