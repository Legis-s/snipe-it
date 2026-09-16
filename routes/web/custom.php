<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Consumables;
use App\Http\Controllers\ContractsController;
use App\Http\Controllers\DealsController;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\InventoriesController;
use App\Http\Controllers\InventoryStatuslabelsController;
use App\Http\Controllers\InvoiceTypesController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\PurchasesController;
use Illuminate\Support\Facades\Route;

Route::impersonate();

Route::group(['middleware' => 'auth'], function () {

    /**
     * InvoiceTypes
     */
    Route::resource('invoicetypes', InvoiceTypesController::class)->only(['index', 'edit', 'update']);

    /**
     * Contracts
     */
    Route::resource('contracts', ContractsController::class)->only(['index', 'show']);

    /**
     * Deals
     */
    Route::resource('deals', DealsController::class)->only(['index', 'show']);

    /**
     * Inventories
     */
    Route::resource('inventories', InventoriesController::class)->only(['index', 'show', 'destroy']);

    /**
     * Inventory Status Labels
     */
    Route::resource('inventorystatuslabels', InventoryStatuslabelsController::class);

    /**
     * Purchases
     */
    Route::post(
        'purchases/delete_all_rejected',
        [PurchasesController::class, 'deleteAllRejected']
    )->name('purchases.delete_all_rejected');

    Route::get(
        'purchases/{assetId}/clone',
        [PurchasesController::class, 'getClone']
    )->name('clone/purchases');

    Route::get(
        'purchases/{purchase}/invoice-file',
        [PurchasesController::class, 'downloadInvoice']
    )->name('purchases.invoice.download');

    Route::post(
        'purchases/recognize-invoice',
        [PurchasesController::class, 'recognizeInvoice']
    )->name('purchases.recognize-invoice');

    Route::resource('purchases', PurchasesController::class)->only(['index', 'create', 'store', 'show', 'edit', 'destroy']);

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
    Route::resource('devices', DevicesController::class)->only(['index', 'show']);
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
