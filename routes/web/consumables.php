<?php


    # Consumables
    Route::group([ 'prefix' => 'consumables', 'middleware' => ['auth']], function () {
        Route::get(
            '{consumableID}/checkout',
            [ 'as' => 'checkout/consumable','uses' => 'ConsumablesController@getCheckout' ]
        );
        Route::post(
            '{consumableID}/checkout',
            [ 'as' => 'checkout/consumable', 'uses' => 'ConsumablesController@postCheckout' ]
        );
        Route::get(
            '{consumableID}/sell',
            [ 'as' => 'checkout/consumable','uses' => 'ConsumablesController@getSell' ]
        );
        Route::post(
            '{consumableID}/sell',
            [ 'as' => 'checkout/consumable', 'uses' => 'ConsumablesController@postSell' ]
        );
    });

    Route::resource('consumables', 'ConsumablesController', [
        'middleware' => ['auth'],
        'parameters' => ['consumable' => 'consumable_id']
    ]);
