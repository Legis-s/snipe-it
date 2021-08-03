<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::group(['prefix' => 'v1','namespace' => 'Api', 'middleware' => 'api'], function () {

    Route::group(['prefix' => 'account'], function () {
        Route::get('requestable/hardware',
            [
                'as' => 'api.assets.requestable',
                'uses' => 'AssetsController@requestable'
            ]
        );

        Route::get('requests',
            [
                'as' => 'api.assets.requested',
                'uses' => 'ProfileController@requestedAssets'
            ]
        );

    });

    /*--- Accessories API ---*/
    Route::resource('accessories', 'AccessoriesController',
        ['names' =>
            [
                'index' => 'api.accessories.index',
                'show' => 'api.accessories.show',
                'update' => 'api.accessories.update',
                'store' => 'api.accessories.store',
                'destroy' => 'api.accessories.destroy'
            ],
            'except' => ['create', 'edit'],
            'parameters' => ['accessory' => 'accessory_id']
        ]
    ); // Accessories resource

    Route::group(['prefix' => 'accessories'], function () {

        Route::get('{accessory}/checkedout',
            [
                'as' => 'api.accessories.checkedout',
                'uses' => 'AccessoriesController@checkedout'
            ]
        );

        Route::post('{accessory}/checkout',
            [
                'as' => 'api.accessories.checkout',
                'uses' => 'AccessoriesController@checkout'
            ]
        );

        Route::post('{accessory}/checkin',
            [
                'as' => 'api.accessories.checkin',
                'uses' => 'AccessoriesController@checkin'
            ]
        );

    }); // Accessories group


    /*--- Categories API ---*/

    Route::group(['prefix' => 'categories'], function () {

        Route::get('{item_type}/selectlist',
            [
                'as' => 'api.categories.selectlist',
                'uses' => 'CategoriesController@selectlist'
            ]
        );

    }); // Categories group


    Route::resource('categories', 'CategoriesController',
        [
            'names' =>
                [
                    'index' => 'api.categories.index',
                    'show' => 'api.categories.show',
                    'store' => 'api.categories.store',
                    'update' => 'api.categories.update',
                    'destroy' => 'api.categories.destroy'
                ],
            'except' => ['edit', 'create'],
            'parameters' => ['category' => 'category_id']
        ]
    ); // Categories resource


    /*--- Companies API ---*/

    Route::get( 'companies/selectlist',  [
        'as' => 'companies.selectlist',
        'uses' => 'CompaniesController@selectlist'
    ]);


    Route::resource('companies', 'CompaniesController',
        [
            'names' =>
                [
                    'index' => 'api.companies.index',
                    'show' => 'api.companies.show',
                    'store' => 'api.companies.store',
                    'update' => 'api.companies.update',
                    'destroy' => 'api.companies.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['component' => 'component_id']
        ]
    ); // Companies resource


    /*--- Departments API ---*/

    /*--- Suppliers API ---*/
    Route::group(['prefix' => 'departments'], function () {


        Route::get('selectlist',
            [
                'as' => 'api.departments.selectlist',
                'uses' => 'DepartmentsController@selectlist'
            ]
        );
    }); // Departments group



    Route::resource('departments', 'DepartmentsController',
        [
            'names' =>
                [
                    'index' => 'api.departments.index',
                    'show' => 'api.departments.show',
                    'store' => 'api.departments.store',
                    'update' => 'api.departments.update',
                    'destroy' => 'api.departments.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['department' => 'department_id']
        ]
    ); // Departments resource


    /*--- Components API ---*/

    Route::resource('components', 'ComponentsController',
        [
            'names' =>
                [
                    'index' => 'api.components.index',
                    'show' => 'api.components.show',
                    'store' => 'api.components.store',
                    'update' => 'api.components.update',
                    'destroy' => 'api.components.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['component' => 'component_id']
        ]
    ); // Components resource

    Route::group(['prefix' => 'components'], function () {

        Route::get('{component}/assets',
            [
                'as' =>'api.components.assets',
                'uses' => 'ComponentsController@getAssets',
            ]
        );
    }); // Components group


    /*--- Consumables API ---*/

    Route::resource('consumables', 'ConsumablesController',
        [
            'names' =>
                [
                    'index' => 'api.consumables.index',
                    'show' => 'api.consumables.show',
                    'store' => 'api.consumables.store',
                    'update' => 'api.consumables.update',
                    'destroy' => 'api.consumables.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['consumable' => 'consumable_id']
        ]
    ); // Consumables resource
//    Route::get('consumables/view/{id}/users',
//        [
//            'as' => 'api.consumables.showUsers',
//            'uses' => 'ConsumablesController@getDataView'
//        ]
//    );

    Route::get('consumables/view/{id}/locations',
        [
            'as' => 'api.consumables.showLocations',
            'uses' => 'ConsumablesController@getDataView'
        ]
    );

    Route::get('consumables/view/{id}/location',
        [
            'as' => 'api.consumables.showLocation',
            'uses' => 'ConsumablesController@getDataViewLocation'
        ]
    );

    /*--- Depreciations API ---*/

    Route::resource('depreciations', 'DepreciationsController',
        [
            'names' =>
                [
                    'index' => 'api.depreciations.index',
                    'show' => 'api.depreciations.show',
                    'store' => 'api.depreciations.store',
                    'update' => 'api.depreciations.update',
                    'destroy' => 'api.depreciations.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['depreciation' => 'depreciation_id']
        ]
    ); // Depreciations resource


    /*--- Fields API ---*/

    Route::resource('fields', 'CustomFieldsController', [
        'names' => [
            'index' => 'api.customfields.index',
            'show' => 'api.customfields.show',
            'store' => 'api.customfields.store',
            'update' => 'api.customfields.update',
            'destroy' => 'api.customfields.destroy'
        ],
        'except' => [ 'create', 'edit' ],
        'parameters' => [ 'field' => 'field_id' ]
    ]);

    Route::group(['prefix' => 'fields'], function () {
        Route::post('fieldsets/{id}/order',
            [
                'as' => 'api.customfields.order',
                'uses' => 'CustomFieldsController@postReorder'
            ]
        );
        Route::post('{field}/associate',
            [
                'as' => 'api.customfields.associate',
                'uses' => 'CustomFieldsController@associate'
            ]
        );
        Route::post('{field}/disassociate',
            [
                'as' => 'api.customfields.disassociate',
                'uses' => 'CustomFieldsController@disassociate'
            ]
        );
    }); // Fields group


    /*--- Fieldsets API ---*/

    Route::group(['prefix' => 'fieldsets'], function () {
        Route::get('{fieldset}/fields',
            [
                'as' => 'api.fieldsets.fields',
                'uses' => 'CustomFieldsetsController@fields'
            ]
        );
        Route::get('/{fieldset}/fields/{model}',
            [
                'as' => 'api.fieldsets.fields-with-default-value',
                'uses' => 'CustomFieldsetsController@fieldsWithDefaultValues'
            ]
        );
    });

    Route::resource('fieldsets', 'CustomFieldsetsController',
        [
            'names' =>
                [
                    'index' => 'api.fieldsets.index',
                    'show' => 'api.fieldsets.show',
                    'store' => 'api.fieldsets.store',
                    'update' => 'api.fieldsets.update',
                    'destroy' => 'api.fieldsets.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['fieldset' => 'fieldset_id']
        ]
    ); // Custom fieldset resource


    /*--- Groups API ---*/

    Route::resource('groups', 'GroupsController',
        [
            'names' =>
                [
                    'index' => 'api.groups.index',
                    'show' => 'api.groups.show',
                    'store' => 'api.groups.store',
                    'update' => 'api.groups.update',
                    'destroy' => 'api.groups.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['group' => 'group_id']
        ]
    ); // Groups resource


    /*--- Hardware API ---*/

    Route::group(['prefix' => 'hardware'], function () {

        Route::get('bytag/{any}',
            [
                'as' => 'api.assets.show.bytag',
                'uses' => 'AssetsController@showByTag'
            ]
        )->where('any', '.*');


        Route::get('byserial/{any}',
            [
                'as' => 'api.assets.show.byserial',
                'uses' => 'AssetsController@showBySerial'
            ]
         )->where('any', '.*');
        

        Route::get( 'selectlist',  [
            'as' => 'assets.selectlist',
            'uses' => 'AssetsController@selectlist'
        ]);

        Route::get('audit/{audit}', [
            'as' => 'api.asset.to-audit',
            'uses' => 'AssetsController@index'
        ]);


        Route::post('audit', [
            'as' => 'api.asset.audit',
            'uses' => 'AssetsController@audit'
        ]);

        Route::post('{asset_id}/checkout',
            [
                'as' => 'api.assets.checkout',
                'uses' => 'AssetsController@checkout'
            ]
        );

        Route::post('{asset_id}/checkin',
            [
                'as' => 'api.assets.checkin',
                'uses' => 'AssetsController@checkin'
            ]
        );

        Route::post('{asset_id}/review',
            [
                'as' => 'api.assets.review',
                'uses' => 'AssetsController@review'
            ]
        );

    });

    /*--- Asset Maintenances API ---*/
    Route::resource('maintenances', 'AssetMaintenancesController',
        [
            'names' =>
                [
                    'index' => 'api.maintenances.index',
                    'show' => 'api.maintenances.show',
                    'store' => 'api.maintenances.store',
                    'update' => 'api.maintenances.update',
                    'destroy' => 'api.maintenances.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['maintenance' => 'maintenance_id']
        ]
    ); // Consumables resource


    Route::resource('hardware', 'AssetsController',
        [
            'names' =>
                [
                    'index' => 'api.assets.index',
                    'show' => 'api.assets.show',
                    'store' => 'api.assets.store',
                    'update' => 'api.assets.update',
                    'destroy' => 'api.assets.destroy',
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['asset' => 'asset_id']
        ]
    ); // Hardware resource


    Route::resource('sale', 'SalesController',
        [
            'names' =>
                [
                    'index' => 'api.sales.index',
                    'show' => 'api.sales.show',
                    'store' => 'api.sales.store',
                    'update' => 'api.sales.update',
                    'destroy' => 'api.sales.destroy',
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['asset' => 'sale_id']
        ]
    ); // Sales resource



    /*--- Hardware API ---*/

    Route::group(['prefix' => 'sale'], function () {

        Route::post('{asset_id}/review',
            [
                'as' => 'api.sales.review',
                'uses' => 'SalesController@review'
            ]
        );

        Route::post('{asset_id}/inventory',
            [
                'as' => 'api.sales.inventory',
                'uses' => 'SalesController@inventory'
            ]
        );

    });
    /*--- Imports API ---*/

    Route::resource('imports', 'ImportController',
        [
            'names' =>
                [
                    'index' => 'api.imports.index',
                    'show' => 'api.imports.show',
                    'store' => 'api.imports.store',
                    'update' => 'api.imports.update',
                    'destroy' => 'api.imports.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['import' => 'import_id']
        ]
    ); // Imports resource

    Route::group(['prefix' => 'imports'], function () {

        Route::post('process/{import}',
            [
                'as' => 'api.imports.importFile',
                'uses'=> 'ImportController@process'
            ]
        );
    }); // Imports group




    /*--- Licenses API ---*/

    Route::group(['prefix' => 'licenses'], function () {
        Route::get('{licenseId}/seats', [
            'as' => 'api.license.seats',
            'uses' => 'LicensesController@seats'
        ]);
    }); // Licenses group

    Route::resource('licenses', 'LicensesController',
        [
            'names' =>
                [
                    'index' => 'api.licenses.index',
                    'show' => 'api.licenses.show',
                    'store' => 'api.licenses.store',
                    'update' => 'api.licenses.update',
                    'destroy' => 'api.licenses.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['license' => 'license_id']
        ]
    ); // Licenses resource



    /*--- Locations API ---*/

    Route::group(['prefix' => 'locations'], function () {

        Route::get('{location}/users',
            [
                'as'=>'api.locations.viewusers',
                'uses'=>'LocationsController@getDataViewUsers'
            ]
        );

        Route::get('{location}/assets',
            [
                'as'=>'api.locations.viewassets',
                'uses'=>'LocationsController@getDataViewAssets'
            ]
        );

        // Do we actually still need this, now that we have an API?
        Route::get('{location}/check',
            [
                'as' => 'api.locations.check',
                'uses' => 'LocationsController@show'
            ]
        );

        Route::get( 'selectlist',  [
            'as' => 'locations.selectlist',
            'uses' => 'LocationsController@selectlist'
        ]);
    }); // Locations group



    Route::resource('locations', 'LocationsController',
        [
            'names' =>
                [
                    'index' => 'api.locations.index',
                    'show' => 'api.locations.show',
                    'store' => 'api.locations.store',
                    'update' => 'api.locations.update',
                    'destroy' => 'api.locations.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['location' => 'location_id']
        ]
    ); // Locations resource


    Route::group(['prefix' => 'contracts'], function () {

        Route::get('selectlist',
            [
                'as' => 'api.contracts.selectlist',
                'uses'=> 'ContractsController@selectlist'
            ]
        );
    }); // Contracts group


    Route::resource('contracts', 'ContractsController',
        [
            'names' =>
                [
                    'index' => 'api.contracts.index',
                    'show' => 'api.contracts.show',
                    'store' => 'api.contracts.store',
                    'update' => 'api.contracts.update',
                    'destroy' => 'api.contracts.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['contract' => 'contract_id']
        ]
    ); // Contracts resource


    /*--- Manufacturers API ---*/

    Route::group(['prefix' => 'manufacturers'], function () {

        Route::get( 'selectlist',  [
            'as' => 'manufacturers.selectlist',
            'uses' => 'ManufacturersController@selectlist'
        ]);
    }); // Locations group


    Route::resource('manufacturers', 'ManufacturersController',
        [
            'names' =>
                [
                    'index' => 'api.manufacturers.index',
                    'show' => 'api.manufacturers.show',
                    'store' => 'api.manufacturers.store',
                    'update' => 'api.manufacturers.update',
                    'destroy' => 'api.manufacturers.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['manufacturer' => 'manufacturer_id']
        ]
    ); // Manufacturers resource


    /*--- Models API ---*/

    Route::group(['prefix' => 'models'], function () {

        Route::get('assets',
            [
                'as' => 'api.models.assets',
                'uses'=> 'AssetModelsController@assets'
            ]
        );
        Route::get('selectlist',
            [
                'as' => 'api.models.selectlist',
                'uses'=> 'AssetModelsController@selectlist'
            ]
        );
    }); // Models group


    Route::resource('models', 'AssetModelsController',
        [
            'names' =>
                [
                    'index' => 'api.models.index',
                    'show' => 'api.models.show',
                    'store' => 'api.models.store',
                    'update' => 'api.models.update',
                    'destroy' => 'api.models.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['model' => 'model_id']
        ]
    ); // Models resource




    /*--- Settings API ---*/
    Route::get('settings/ldaptest', [
        'as' => 'api.settings.ldaptest',
        'uses' => 'SettingsController@ldaptest'
    ]);

    Route::post('settings/ldaptestlogin', [
        'as' => 'api.settings.ldaptestlogin',
        'uses' => 'SettingsController@ldaptestlogin'
    ]);

    Route::post('settings/slacktest', [
        'as' => 'api.settings.slacktest',
        'uses' => 'SettingsController@slacktest'
    ]);

    Route::post(
        'settings/mailtest',
        [
            'as'  => 'api.settings.mailtest',
            'uses' => 'SettingsController@ajaxTestEmail' ]
    );


    Route::resource('settings', 'SettingsController',
        [
            'names' =>
                [
                    'index' => 'api.settings.index',
                    'store' => 'api.settings.store',
                    'show' => 'api.settings.show',
                    'update' => 'api.settings.update'
                ],
            'except' => ['create', 'edit', 'destroy'],
            'parameters' => ['setting' => 'setting_id']
        ]
    ); // Settings resource




    /*--- Status Labels API ---*/


    Route::group(['prefix' => 'statuslabels'], function () {

        // Pie chart for dashboard
        Route::get('assets',
            [
                'as' => 'api.statuslabels.assets.bytype',
                'uses' => 'StatuslabelsController@getAssetCountByStatuslabel'
            ]
        );

        Route::get('{statuslabel}/assetlist',
            [
                'as' => 'api.statuslabels.assets',
                'uses' => 'StatuslabelsController@assets'
            ]
        );

        Route::get('{statuslabel}/deployable',
            [
                'as' => 'api.statuslabels.deployable',
                'uses' => 'StatuslabelsController@checkIfDeployable'
            ]
        );


    });

    Route::resource('statuslabels', 'StatuslabelsController',
        [
            'names' =>
                [
                    'index' => 'api.statuslabels.index',
                    'store' => 'api.statuslabels.store',
                    'show' => 'api.statuslabels.show',
                    'update' => 'api.statuslabels.update',
                    'destroy' => 'api.statuslabels.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['statuslabel' => 'statuslabel_id']
        ]
    );

    Route::resource('inventorystatuslabels', 'InventoryStatuslabelsController',
        [
            'names' =>
                [
                    'index' => 'api.inventorystatuslabels.index',
                    'store' => 'api.inventorystatuslabels.store',
                    'show' => 'api.inventorystatuslabels.show',
                    'update' => 'api.inventorystatuslabels.update',
                    'destroy' => 'api.inventorystatuslabels.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['inventorystatuslabel' => 'inventorystatuslabel_id']
        ]
    );

    // Status labels group


    /*--- Suppliers API ---*/
    Route::group(['prefix' => 'suppliers'], function () {

        Route::get('list',
            [
                'as'=>'api.suppliers.list',
                'uses'=>'SuppliersController@getDatatable'
            ]
        );

        Route::get('selectlist',
            [
                'as' => 'api.suppliers.selectlist',
                'uses' => 'SuppliersController@selectlist'
            ]
        );
    }); // Suppliers group


    Route::resource('suppliers', 'SuppliersController',
        [
            'names' =>
                [
                    'index' => 'api.suppliers.index',
                    'show' => 'api.suppliers.show',
                    'store' => 'api.suppliers.store',
                    'update' => 'api.suppliers.update',
                    'destroy' => 'api.suppliers.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['supplier' => 'supplier_id']
        ]
    ); // Suppliers resource




    /*--- Users API ---*/

    
    Route::group([ 'prefix' => 'users' ], function () {

        Route::post('two_factor_reset',
            [
                'as' => 'api.users.two_factor_reset',
                'uses' => 'UsersController@postTwoFactorReset'
            ]
        );

        Route::get('me',
            [
                'as' => 'api.users.me',
                'uses' => 'UsersController@getCurrentUserInfo'
            ]
        );

        Route::get('list/{status?}',
            [
                'as' => 'api.users.list',
                'uses' => 'UsersController@getDatatable'
            ]
        );

        Route::get('selectlist',
            [
                'as' => 'api.users.selectlist',
                'uses' => 'UsersController@selectList'
            ]
        );

        Route::get('{user}/assets',
            [
                'as' => 'api.users.assetlist',
                'uses' => 'UsersController@assets'
            ]
        );

        Route::get('{user}/accessories',
            [
                'as' => 'api.users.accessorieslist',
                'uses' => 'UsersController@accessories'
            ]
        );

        Route::get('{user}/licenses',
            [
                'as' => 'api.users.licenselist',
                'uses' => 'UsersController@licenses'
            ]
        );

        Route::post('{user}/upload',
            [
                'as' => 'api.users.uploads',
                'uses' => 'UsersController@postUpload'
            ]
        );
    }); // Users group

    Route::resource('users', 'UsersController',
        [
            'names' =>
                [
                    'index' => 'api.users.index',
                    'show' => 'api.users.show',
                    'store' => 'api.users.store',
                    'update' => 'api.users.update',
                    'destroy' => 'api.users.destroy'
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['user' => 'user_id']
        ]
    ); // Users resource


    Route::get(
        'reports/activity',
        [ 'as' => 'api.activity.index', 'uses' => 'ReportsController@index' ]
    );

    /*--- Inventory API ---*/


    Route::resource('inventories', 'InventoriesController',
        [
            'names' =>
                [
                    'index' => 'api.inventories.index',
                    'show' => 'api.inventories.show',
                    'store' => 'api.inventories.store',
                    'update' => 'api.inventories.update',
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['inventory' => 'inventory_id']
        ]
    ); // Inventory resource

    /*--- InventoryItem API ---*/


    Route::resource('inventory_items', 'InventoryItemController',
        [
            'names' =>
                [
                    'index' => 'api.inventory_items.index',
                    'show' => 'api.inventory_items.show',
                    'update' => 'api.inventory_items.update',
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['inventory_item' => 'inventory_item_id']
        ]
    ); // Inventory resource




    /*--- Map API ---*/

    Route::resource('map', 'MapController',
        [
            'names' =>
                [
                    'index' => 'api.map.index',
                ],
//            'except' => ['create', 'edit'],
//            'parameters' => ['purchase' => 'purchase_id']
        ]
    ); // Purchases resource

    /*--- Purchases API ---*/

    Route::resource('purchases', 'PurchasesController',
        [
            'names' =>
                [
                    'index' => 'api.purchases.index',
                    'show' => 'api.purchases.show',
                    'store' => 'api.purchases.store',
                    'update' => 'api.purchases.update',
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['purchase' => 'purchase_id']
        ]
    ); // Purchases resource

    Route::group([ 'prefix' => 'purchases' ], function () {

        Route::post('{purchase}/paid',
            [
                'as' => 'api.purchases.paid',
                'uses' => 'PurchasesController@paid'
            ]
        );

        Route::post('{purchase}/consumables_check',
            [
                'as' => 'api.purchases.consumables_check',
                'uses' => 'PurchasesController@consumables_check'
            ]
        );

        Route::post('{purchase}/in_payment',
            [
                'as' => 'api.purchases.in_payment',
                'uses' => 'PurchasesController@in_payment'
            ]
        );

        Route::post('{purchase}/reject',
            [
                'as' => 'api.purchases.reject',
                'uses' => 'PurchasesController@reject'
            ]
        );
        Route::post('{purchase}/resend',
            [
                'as' => 'api.purchases.resend',
                'uses' => 'PurchasesController@resend'
            ]
        );

        Route::post('{purchase}/bitrix_task/{bitrix_task}',
            [
                'as' => 'api.purchases.bitrix_task',
                'uses' => 'PurchasesController@bitrix_task'
            ]
        );
    }); // Users group


    /*--- InvoiceTypes API ---*/

    Route::group(['prefix' => 'invoice_types'], function () {

        Route::get( 'selectlist',  [
            'as' => 'invoice_types.selectlist',
            'uses' => 'InvoiceTypesController@selectlist'
        ]);
    });

    /*--- LegalPersons API ---*/

    Route::group(['prefix' => 'legal_persons'], function () {

        Route::get( 'selectlist',  [
            'as' => 'legal_persons.selectlist',
            'uses' => 'LegalPersonsController@selectlist'
        ]);
    });

    /*--- BitrixSync API ---*/

    Route::group(['prefix' => 'bitrix_sync'], function () {

        Route::post( 'users',  [
            'as' => 'bitrix_sync.users',
            'uses' => 'BitrixSyncController@syncUsers'
        ]);

        Route::post( 'locations',  [
            'as' => 'bitrix_sync.locations',
            'uses' => 'BitrixSyncController@syncLocations'
        ]);

        Route::post( 'suppliers',  [
            'as' => 'bitrix_sync.suppliers',
            'uses' => 'BitrixSyncController@syncSuppliers'
        ]);

        Route::post( 'legal_persons',  [
            'as' => 'bitrix_sync.legal_persons',
            'uses' => 'BitrixSyncController@syncLegalPersons'
        ]);

        Route::post( 'invoice_types',  [
            'as' => 'bitrix_sync.invoice_types',
            'uses' => 'BitrixSyncController@syncInvoiceTypes'
        ]);
    });
//    Route::post('auth',
//        [
//            'as' => 'api.auth',
//            'uses' => 'AuthController@getToken'
//        ]
//    );

});
