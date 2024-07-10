<?php

use App\Http\Controllers\Api;
// use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


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

Route::group(['prefix' => 'v1', 'middleware' => ['api', 'throttle:api']], function () {


    Route::get('/', function () {
        return response()->json(
            [
                'status' => 'error',
                'message' => '404 endpoint not found. This is the base URL for the API and does not return anything itself. Please check the API reference at https://snipe-it.readme.io/reference to find a valid API endpoint.',
                'payload' => null,
            ], 404);
    });


    /**
     * Account routes
     */
    Route::group(['prefix' => 'account'], function () {

        Route::get('requests',
            [
                Api\ProfileController::class, 
                'requestedAssets'
            ]
        )->name('api.assets.requested');

        Route::get('requestable/hardware',
            [
                Api\AssetsController::class, 
                'requestable'
            ]
        )->name('api.assets.requestable');

        Route::post('personal-access-tokens',
            [
                Api\ProfileController::class,
                'createApiToken'
            ]
        )->name('api.personal-access-token.create');

        Route::get('personal-access-tokens',
            [
                Api\ProfileController::class,
                'showApiTokens'
            ]
        )->name('api.personal-access-token.index');

        Route::delete('personal-access-tokens/{tokenId}',
            [
                Api\ProfileController::class,
                'deleteApiToken'
            ]
        )->name('api.personal-access-token.delete');



     }); // end account group


     /**
     * Accessories routes
     */
    Route::group(['prefix' => 'accessories'], function () {

        Route::get('{accessory}/checkedout',
            [
                Api\AccessoriesController::class, 
                'checkedout'
            ]
        )->name('api.accessories.checkedout');

        Route::post('{accessory}/checkout',
            [
                Api\AccessoriesController::class, 
                'checkout'
            ]
        )->name('api.accessories.checkout');


        Route::post('{accessory}/checkin',
            [
                Api\AccessoriesController::class, 
                'checkin'
            ]
        )->name('api.accessories.checkin');

        Route::get('selectlist',
            [
                Api\AccessoriesController::class, 
                'selectlist'
            ]
        )->name('api.accessories.selectlist');



     }); // end accessories group

    Route::resource('accessories',
        Api\AccessoriesController::class,
        ['names' => [
                'index' => 'api.accessories.index',
                'show' => 'api.accessories.show',
                'update' => 'api.accessories.update',
                'store' => 'api.accessories.store',
                'destroy' => 'api.accessories.destroy',
            ],
            'except' => ['create', 'edit'],
            'parameters' => ['accessory' => 'accessory_id'],
        ]
    );

     
     /**
      * Categpries API routes
      */
      Route::group(['prefix' => 'categories'], function () {
        
        Route::get('{item_type}/selectlist',
            [
                Api\CategoriesController::class, 
                'selectlist'
            ]
        )->name('api.categories.selectlist');

      });

    Route::resource('categories', 
        Api\CategoriesController::class,
        ['names' => [
                'index' => 'api.categories.index',
                'show' => 'api.categories.show',
                'update' => 'api.categories.update',
                'store' => 'api.categories.store',
                'destroy' => 'api.categories.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['category' => 'category_id'],
        ]
    ); // end category API routes

     /**
      * Companies API routes
      */
      Route::group(['prefix' => 'companies'], function () {
        
        Route::get('selectlist',
            [
                Api\CompaniesController::class, 
                'selectlist'
            ]
        )->name('api.companies.selectlist');

      }); 

      Route::resource('companies', 
        Api\CompaniesController::class,
        ['names' => [
                'index' => 'api.companies.index',
                'show' => 'api.companies.show',
                'update' => 'api.companies.update',
                'store' => 'api.companies.store',
                'destroy' => 'api.companies.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['company' => 'company_id'],
        ]
    ); // end companies API routes


    /**
      * Departments API routes
      */
      Route::group(['prefix' => 'departments'], function () {
        
        Route::get('selectlist',
            [
                Api\DepartmentsController::class, 
                'selectlist'
            ]
        )->name('api.departments.selectlist');

      }); 

      Route::resource('departments', 
        Api\DepartmentsController::class,
        ['names' => [
                'index' => 'api.departments.index',
                'show' => 'api.departments.show',
                'update' => 'api.departments.update',
                'store' => 'api.departments.store',
                'destroy' => 'api.departments.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['department' => 'department_id'],
        ]
    ); // end departments API routes


      /**
      * Components API routes
      */
      Route::group(['prefix' => 'components'], function () {
        
        Route::get('selectlist',
            [
                Api\ComponentsController::class, 
                'selectlist'
            ]
        )->name('api.components.selectlist');

        Route::get('{component}/assets',
        [
            Api\ComponentsController::class, 
            'getAssets'
        ]
        )->name('api.components.assets');

      });
    Route::post('components/{id}/checkin',
        [
            Api\ComponentsController::class,
            'checkin'
        ]
    )->name('api.components.checkin');

    Route::post('components/{id}/checkout',
        [
            Api\ComponentsController::class,
            'checkout'
        ]
    )->name('api.components.checkout');


      Route::resource('components', 
        Api\ComponentsController::class,
        ['names' => [
                'index' => 'api.components.index',
                'show' => 'api.components.show',
                'update' => 'api.components.update',
                'store' => 'api.components.store',
                'destroy' => 'api.components.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['component' => 'component_id'],
        ]
    ); // end components API routes


      /**
      * Consumables API routes
      */
      Route::group(['prefix' => 'consumables'], function () {
        
        Route::get('selectlist',
            [
                Api\ConsumablesController::class, 
                'selectlist'
            ]
        )->name('api.consumables.selectlist');

        Route::get('{id}/users',
            [
                Api\ConsumablesController::class, 
                'getDataView'
            ]
        )->name('api.consumables.show.users');


        Route::post('{consumable}/checkout',
            [
                Api\ConsumablesController::class, 
                'checkout'
            ]
        )->name('api.consumables.checkout');

      }); 


      Route::resource('consumables', 
        Api\ConsumablesController::class,
        ['names' => [
                'index' => 'api.consumables.index',
                'show' => 'api.consumables.show',
                'update' => 'api.consumables.update',
                'store' => 'api.consumables.store',
                'destroy' => 'api.consumables.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['consumable' => 'consumable_id'],
        ]
        ); // end consumables API routes



        /**
         * Depreciations API routes
        */

        Route::group(['prefix' => 'depreciations'], function () {
            Route::get('selectlist',
                [
                    Api\DepreciationsController::class,
                    'selectlist'
                ]
            )->name('depreciations.selectlist');
        });
        Route::resource('depreciations', 
        Api\DepreciationsController::class,
        ['names' => [
                'index' => 'api.depreciations.index',
                'show' => 'api.depreciations.show',
                'update' => 'api.depreciations.update',
                'store' => 'api.depreciations.store',
                'destroy' => 'api.depreciations.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['depreciations' => 'depreciation_id'],
        ]
        ); // end depreciations API routes


        Route::get('reports/depreciation',
        [
            Api\AssetsController::class, 
            'index'
        ]
        )->name('api.depreciation-report.index');

       
        
        /**
         * Fields API routes
        */
        Route::group(['prefix' => 'fields'], function () {
        
            Route::post('fieldsets/{id}/order',
                [
                    Api\CustomFieldsController::class, 
                    'postReorder'
                ]
            )->name('api.customfields.order');
    
            Route::post('{field}/associate',
                [
                    Api\CustomFieldsController::class, 
                    'associate'
                ]
            )->name('api.customfields.associate');

            Route::post('{field}/disassociate',
                [
                    Api\CustomFieldsController::class, 
                    'disassociate'
                ]
            )->name('api.customfields.disassociate');
        });

        Route::resource('fields', 
        Api\CustomFieldsController::class,
            ['names' => 
                [
                    'index' => 'api.customfields.index',
                    'show' => 'api.customfields.show',
                    'update' => 'api.customfields.update',
                    'store' => 'api.customfields.store',
                    'destroy' => 'api.customfields.destroy',
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['field' => 'field_id'],
            ]
        ); // end custom fields API routes

        /**
         * Fieldsets API routes
        */
        Route::group(['prefix' => 'fieldsets'], function () {
        
            Route::post('{fieldset}/fields',
                [
                    Api\CustomFieldsetsController::class, 
                    'fields'
                ]
            )->name('api.fieldsets.fields');

            Route::post('{fieldset}/fields/{model}',
                [
                    Api\CustomFieldsetsController::class, 
                    'fieldsWithDefaultValues'
                ]
            )->name('api.fieldsets.fields-with-default-value');
    
        });

        Route::resource('fieldsets', 
        Api\CustomFieldsetsController::class,
            ['names' => [
                    'index' => 'api.fieldsets.index',
                    'show' => 'api.fieldsets.show',
                    'update' => 'api.fieldsets.update',
                    'store' => 'api.fieldsets.store',
                    'destroy' => 'api.fieldsets.destroy',
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['fieldset' => 'fieldset_id'],
            ]
        ); // end custom fieldsets API routes



        /**
         * Groups API routes
        */
        Route::resource('groups', 
        Api\GroupsController::class,
            ['names' => [
                    'index' => 'api.groups.index',
                    'show' => 'api.groups.show',
                    'update' => 'api.groups.update',
                    'store' => 'api.groups.store',
                    'destroy' => 'api.groups.destroy',
                ],
            'except' => ['create', 'edit'],
            'parameters' => ['group' => 'group_id'],
            ]
        ); // end groups API routes
        

     /**
      * Assets API routes
      */
      Route::group(['prefix' => 'hardware'], function () {
        
        Route::get('selectlist',
            [
                Api\AssetsController::class, 
                'selectlist'
            ]
        )->name('assets.selectlist');

        Route::get('{asset_id}/licenses',
            [
                Api\AssetsController::class, 
                'licenses'
            ]
        )->name('api.assets.licenselist');

        Route::get('bytag/{tag}',
            [
                Api\AssetsController::class, 
                'showByTag'
            ]
        )->name('assets.show.bytag');

        Route::get('bytag/{any}',
            [
                Api\AssetsController::class, 
                'showByTag'
            ]
        )->name('api.assets.show.bytag')
        ->where('any', '.*');

        Route::post('bytag/{any}/checkout',
            [
                Api\AssetsController::class, 
                'checkoutByTag'
            ]
        )->name('api.assets.checkout.bytag');

        Route::post('bytag/{any}/checkin',
            [
                Api\AssetsController::class,
                'checkinbytag'
            ]
        )->name('api.asset.checkinbytagPath');

        Route::post('checkinbytag',
            [
                Api\AssetsController::class,
                'checkinbytag'
            ]
        )->name('api.asset.checkinbytag');

        Route::get('byserial/{any}',
            [
                Api\AssetsController::class, 
                'showBySerial'
            ]
        )->name('api.assets.show.byserial')
        ->where('any', '.*');

        // LEGACY URL - Get assets that are due or overdue for audit
        Route::get('audit/{status}',
        [
            Api\AssetsController::class, 
            'index'
        ]
        )->name('api.asset.to-audit');



        // This gets the "due or overdue" API endpoints for audits and checkins
        Route::get('{action}/{upcoming_status}',
              [
                  Api\AssetsController::class,
                  'index'
              ]
        )->name('api.assets.list-upcoming')
        ->where(['action' => 'audits|checkins', 'upcoming_status' => 'due|overdue|due-or-overdue']);



        Route::post('audit',
        [
            Api\AssetsController::class, 
            'audit'
        ]
        )->name('api.asset.audit');

        Route::post('{id}/checkin',
        [
            Api\AssetsController::class, 
            'checkin'
        ]
        )->name('api.asset.checkin');

        Route::post('{id}/checkout',
        [
            Api\AssetsController::class, 
            'checkout'
        ]
        )->name('api.asset.checkout');

      Route::post('{asset_id}/restore',
          [
              Api\AssetsController::class,
              'restore'
          ]
      )->name('api.assets.restore');
        Route::post('{asset_id}/files',
          [
              Api\AssetFilesController::class,
              'store'
          ]
        )->name('api.assets.files');

        Route::get('{asset_id}/files',
          [
              Api\AssetFilesController::class,
              'list'
          ]
        )->name('api.assets.files');

        Route::get('{asset_id}/file/{file_id}',
          [
              Api\AssetFilesController::class,
              'show'
          ]
        )->name('api.assets.file');

        Route::delete('{asset_id}/file/{file_id}',
          [
              Api\AssetFilesController::class,
              'destroy'
          ]
        )->name('api.assets.file');

      });

        Route::resource('hardware', 
        Api\AssetsController::class,
        ['names' => [
                'index' => 'api.assets.index',
                'show' => 'api.assets.show',
                'update' => 'api.assets.update',
                'store' => 'api.assets.store',
                'destroy' => 'api.assets.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['asset' => 'asset_id'],
        ]
        ); // end assets API routes

        /**
         * Asset maintenances API routes
         */
        Route::resource('maintenances', 
        Api\AssetMaintenancesController::class,
        ['names' => [
                'index' => 'api.maintenances.index',
                'show' => 'api.maintenances.show',
                'update' => 'api.maintenances.update',
                'store' => 'api.maintenances.store',
                'destroy' => 'api.maintenances.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['maintenance' => 'maintenance_id'],
        ]
        ); // end assets API routes


      /**
      * Imports API routes
      */
      Route::group(['prefix' => 'imports'], function () {
        
        Route::post('process/{import}',
            [
                Api\ImportController::class, 
                'process'
            ]
        )->name('api.imports.importFile');

      }); 

      Route::resource('imports', 
        Api\ImportController::class,
        ['names' => [
                'index' => 'api.imports.index',
                'show' => 'api.imports.show',
                'update' => 'api.imports.update',
                'store' => 'api.imports.store',
                'destroy' => 'api.imports.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['import' => 'import_id'],
        ]
    ); // end imports API routes


        /**
         * Labels API routes
         */
        Route::group(['prefix' => 'labels'], function() {
            Route::get('{name}', [ Api\LabelsController::class, 'show'])
                ->where('name', '.*')
                ->name('api.labels.show');
            Route::get('', [ Api\LabelsController::class, 'index'])
                ->name('api.labels.index');
        });

        /**
         * Licenses API routes
        */
        Route::group(['prefix' => 'licenses'], function () {

        Route::get('selectlist',
            [
                Api\LicensesController::class, 
                'selectlist'
            ]
        )->name('api.licenses.selectlist');

        }); 

        Route::resource('licenses', 
        Api\LicensesController::class,
        ['names' => [
                'index' => 'api.licenses.index',
                'show' => 'api.licenses.show',
                'update' => 'api.licenses.update',
                'store' => 'api.licenses.store',
                'destroy' => 'api.licenses.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['licenses' => 'license_id'],
        ]
        ); 


        Route::resource('licenses.seats', 
        Api\LicenseSeatsController::class,
        ['names' => [
                'index' => 'api.licenses.seats.index',
                'show' => 'api.licenses.seats.show',
                'update' => 'api.licenses.seats.update',
            ],
        'except' => ['create', 'edit', 'destroy', 'store'],
        'parameters' => ['licenseseat' => 'licenseseat_id'],
        ]
        ); // end license API routes


        /**
         * Locations API routes
        */
        Route::group(['prefix' => 'locations'], function () {

            Route::get('selectlist',
                [
                    Api\LocationsController::class, 
                    'selectlist'
                ]
            )->name('api.locations.selectlist');

            Route::get('{location}/users',
                [
                    Api\LocationsController::class, 
                    'getDataViewUsers'
                ]
            )->name('api.locations.viewusers');

            Route::get('{location}/assets',
            [
                Api\LocationsController::class, 
                'getDataViewAssets'
            ]
            )->name('api.locations.viewassets');
    
        }); 
    
        Route::resource('locations', 
        Api\LocationsController::class,
        ['names' => [
                'index' => 'api.locations.index',
                'show' => 'api.locations.show',
                'update' => 'api.locations.update',
                'store' => 'api.locations.store',
                'destroy' => 'api.locations.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['location' => 'location_id'],
        ]
        ); // end locations API routes


        /**
        * Manufacturers API routes
        */
        Route::group(['prefix' => 'manufacturers'], function () {

            Route::get('selectlist',
                [
                    Api\ManufacturersController::class, 
                    'selectlist'
                ]
            )->name('api.manufacturers.selectlist');

            Route::post('{id}/restore',
                [
                    Api\ManufacturersController::class,
                    'restore'
                ]
            )->name('api.manufacturers.restore');

        });
    
        Route::resource('manufacturers', 
        Api\ManufacturersController::class,
        ['names' => [
                'index' => 'api.manufacturers.index',
                'show' => 'api.manufacturers.show',
                'update' => 'api.manufacturers.update',
                'store' => 'api.manufacturers.store',
                'destroy' => 'api.manufacturers.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['manufacturer' => 'manufacturer_id'],
        ]
        ); // end  manufacturers API routes


        /**
        * Asset models API routes
        */
        Route::group(['prefix' => 'models'], function () {

            Route::get('selectlist',
                [
                    Api\AssetModelsController::class, 
                    'selectlist'
                ]
            )->name('api.models.selectlist');

            Route::get('assets',
                [
                    Api\AssetModelsController::class, 
                    'assets'
                ]
            )->name('api.models.assets');

            Route::post('{id}/restore',
                [
                    Api\AssetModelsController::class,
                    'restore'
                ]
            )->name('api.models.restore');

        });
    
        Route::resource('models', 
        Api\AssetModelsController::class,
        ['names' => [
                'index' => 'api.models.index',
                'show' => 'api.models.show',
                'update' => 'api.models.update',
                'store' => 'api.models.store',
                'destroy' => 'api.models.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['model' => 'model_id'],
        ]
        ); // end asset models API routes



        /**
        * Settings API routes
        */
        Route::group(['middleware'=> ['auth', 'authorize:superuser'], 'prefix' => 'settings'], function () {

            Route::get('ldaptest',
                [
                    Api\SettingsController::class, 
                    'ldaptest'
                ]
            )->name('api.settings.ldaptest');

            Route::post('purge_barcodes',
                [
                    Api\SettingsController::class, 
                    'purgeBarcodes'
                ]
            )->name('api.settings.purgebarcodes');

            Route::get('login-attempts',
                [
                    Api\SettingsController::class, 
                    'showLoginAttempts'
                ]
            )->name('api.settings.login_attempts');

            Route::post('ldaptestlogin',
                [
                    Api\SettingsController::class, 
                    'ldaptestlogin'
                ]
            )->name('api.settings.ldaptestlogin');

            Route::post('slacktest',
            [
                Api\SettingsController::class, 
                'slacktest'
            ]
            )->name('api.settings.slacktest');

            Route::post('mailtest',
            [
                Api\SettingsController::class, 
                'ajaxTestEmail'
            ]
            )->name('api.settings.mailtest');

            Route::get('backups',
                [
                    Api\SettingsController::class,
                    'listBackups'
                ]
            )->name('api.settings.backups.index');

            Route::get('backups/download/latest',
                [
                    Api\SettingsController::class,
                    'downloadLatestBackup'
                ]
            )->name('api.settings.backups.latest');

            Route::get('backups/download/{file}',
                [
                    Api\SettingsController::class,
                    'downloadBackup'
                ]
            )->name('api.settings.backups.download');

        }); 
        
        Route::resource('settings', 
        Api\SettingsController::class,
        ['names' => [
                'index' => 'api.settings.index',
                'show' => 'api.settings.show',
                'update' => 'api.settings.update',
                'store' => 'api.settings.store',
                'destroy' => 'api.settings.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['setting' => 'setting_id'],
        ]
        ); // end settings API


        /**
        * Status labels API routes
        */
        Route::group(['prefix' => 'statuslabels'], function () {

            Route::get('selectlist',
                [
                    Api\StatuslabelsController::class, 
                    'selectlist'
                ]
            )->name('api.statuslabels.selectlist');

            Route::get('assets/name',
                [
                    Api\StatuslabelsController::class, 
                    'getAssetCountByStatuslabel'
                ]
            )->name('api.statuslabels.assets.byname');

            Route::get('assets/type',
                [
                    Api\StatuslabelsController::class,
                    'getAssetCountByMetaStatus'
                ]
            )->name('api.statuslabels.assets.bytype');

            Route::get('{id}/assetlist',
                [
                    Api\StatuslabelsController::class, 
                    'assets'
                ]
            )->name('api.statuslabels.assets');

            Route::get('{statuslabel}/deployable',
                [
                    Api\StatuslabelsController::class, 
                    'checkIfDeployable'
                ]
            )->name('api.statuslabels.deployable');

            Route::get('selectlist',
                [
                    Api\StatuslabelsController::class,
                    'selectlist'
                ]
            )->name('api.statuslabels.selectlist');

        });
    
        Route::resource('statuslabels', 
        Api\StatuslabelsController::class,
        ['names' => [
                'index' => 'api.statuslabels.index',
                'show' => 'api.statuslabels.show',
                'update' => 'api.statuslabels.update',
                'store' => 'api.statuslabels.store',
                'destroy' => 'api.statuslabels.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['statuslabel' => 'statuslabel_id'],
        ]
        ); // end status labels API routes


        /**
        * Suppliers API routes
        */
        Route::group(['prefix' => 'suppliers'], function () {

            Route::get('selectlist',
                [
                    Api\SuppliersController::class, 
                    'selectlist'
                ]
            )->name('api.suppliers.selectlist');

        }); 
    
        Route::resource('suppliers', 
        Api\SuppliersController::class,
        ['names' => [
                'index' => 'api.suppliers.index',
                'show' => 'api.suppliers.show',
                'update' => 'api.suppliers.update',
                'store' => 'api.suppliers.store',
                'destroy' => 'api.suppliers.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['supplier' => 'supplier_id'],
        ]
        ); // end suppliers API routes



        /**
        * Users API routes
        */
        Route::group(['prefix' => 'users'], function () {

            Route::get('selectlist',
                [
                    Api\UsersController::class, 
                    'selectlist'
                ]
            )->name('api.users.selectlist');

            Route::post('two_factor_reset',
                [
                    Api\UsersController::class, 
                    'postTwoFactorReset'
                ]
            )->name('api.users.two_factor_reset');

            Route::get('me',
                [
                    Api\UsersController::class, 
                    'getCurrentUserInfo'
                ]
            )->name('api.users.me');

            Route::get('list/{status?}',
            [
                Api\UsersController::class, 
                'getDatatable'
            ]
            )->name('api.users.list');

            Route::get('{user}/assets',
            [
                Api\UsersController::class, 
                'assets'
            ]
            )->name('api.users.assetlist');

            Route::post('{user}/email',
                [
                    Api\UsersController::class,
                    'emailAssetList'
                ]
            )->name('api.users.email_assets');

            Route::get('{user}/accessories',
            [
                Api\UsersController::class, 
                'accessories'
            ]
            )->name('api.users.accessorieslist');

            Route::get('{user}/licenses',
            [
                Api\UsersController::class, 
                'licenses'
            ]
            )->name('api.users.licenselist');

            Route::post('{user}/upload',
            [
                Api\UsersController::class, 
                'postUpload'
            ]
            )->name('api.users.uploads');

            Route::post('{user}/restore',
                [
                    Api\UsersController::class,
                    'restore'
                ]
            )->name('api.users.restore');

        }); 
    
        Route::resource('users', 
        Api\UsersController::class,
        ['names' => [
                'index' => 'api.users.index',
                'show' => 'api.users.show',
                'update' => 'api.users.update',
                'store' => 'api.users.store',
                'destroy' => 'api.users.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['user' => 'user_id'],
        ]
        ); // end users API routes


        /**
        * Kits API routes
        */
        Route::resource('kits', 
        Api\PredefinedKitsController::class,
        ['names' => [
                'index' => 'api.kits.index',
                'show' => 'api.kits.show',
                'update' => 'api.kits.update',
                'store' => 'api.kits.store',
                'destroy' => 'api.kits.destroy',
            ],
        'except' => ['create', 'edit'],
        'parameters' => ['kit' => 'kit_id'],
        ]
        ); // end kits API routes


        Route::group(['prefix' => 'kits/{kit_id}'], function () {

             // kit licenses
            Route::get('licenses',
                [
                    Api\PredefinedKitsController::class, 
                    'indexLicenses'
                ]
            )->name('api.kits.licenses.index');

            Route::post('licenses',
                [
                    Api\PredefinedKitsController::class, 
                    'storeLicense'
                ]
            )->name('api.kits.licenses.store');

            Route::put('licenses/{license_id}',
                [
                    Api\PredefinedKitsController::class, 
                    'updateLicense'
                ]
            )->name('api.kits.licenses.update');

            Route::delete('licenses/{license_id}',
                [
                    Api\PredefinedKitsController::class, 
                    'detachLicense'
                ]
            )->name('api.kits.licenses.destroy');


            // kit models
            Route::get('models',
                [
                    Api\PredefinedKitsController::class, 
                    'indexModels'
                ]
            )->name('api.kits.models.index');

            Route::post('models',
                [
                    Api\PredefinedKitsController::class, 
                    'storeModel'
                ]
            )->name('api.kits.models.store');

            Route::put('models/{model_id}',
                [
                    Api\PredefinedKitsController::class, 
                    'updateModels'
                ]
            )->name('api.kits.models.update');

            Route::delete('models/{model_id}',
                [
                    Api\PredefinedKitsController::class, 
                    'detachModels'
                ]
            )->name('api.kits.models.destroy');

             // kit accessories
             Route::get('accessories',
                [
                    Api\PredefinedKitsController::class, 
                    'indexAccessories'
                ]
            )->name('api.kits.accessories.index');

            Route::post('accessories',
                [
                    Api\PredefinedKitsController::class, 
                    'storeAccessory'
                ]
            )->name('api.kits.accessories.store');

            Route::put('accessories/{accessory_id}',
                [
                    Api\PredefinedKitsController::class, 
                    'updateAccessory'
                ]
            )->name('api.kits.accessories.update');

            Route::delete('accessories/{accessory_id}',
                [
                    Api\PredefinedKitsController::class, 
                    'detachAccessory'
                ]
            )->name('api.kits.accessories.destroy');

            // kit consumables
            Route::get('consumables',
            [
                Api\PredefinedKitsController::class, 
                'indexConsumables'
            ]
            )->name('api.kits.consumables.index');

            Route::post('consumables',
            [
                Api\PredefinedKitsController::class, 
                'storeConsumable'
            ]
            )->name('api.kits.consumables.store');

            Route::put('consumables/{consumable_id}',
            [
                Api\PredefinedKitsController::class, 
                'updateConsumable'
            ]
            )->name('api.kits.consumables.update');

            Route::delete('consumables/{consumable_id}',
            [
                Api\PredefinedKitsController::class, 
                'detachConsumable'
            ]
            )->name('api.kits.consumables.destroy');

        }); // end consumable routes
    
        
        /**
         * Reports API routes
         */
        
        Route::group(['prefix' => 'reports'], function () {

            Route::get('activity',
            [
                Api\ReportsController::class, 
                'index'
            ]
            )->name('api.activity.index');
        }); // end reports api routes

        /**
         * Version API routes
         */

        Route::get('/version', function () {
            return response()->json(
                [
                    'version' => config('version.app_version'),
                ], 200);
        }); // end version api routes


        Route::fallback(function () {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => '404 endpoint not found. Please check the API reference at https://snipe-it.readme.io/reference to find a valid API endpoint.',
                    'payload' => null,
                ], 404);
        }); // end fallback routes



        /**
         * CUSTOM API ROUTES START
         */


        /**
         * tabel_create API routes
         */


        Route::post('tabel_create',
                [
                    Api\AssetsController::class,
                    'tabel_create'
                ]
        )->name('api.assets.tabel_create');
        // end tabel_create API routes

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

        Route::resource('inventories',
            Api\InventoriesController::class,
            ['names' =>
                [
                    'index' => 'api.inventories.index',
                    'show' => 'api.inventories.show',
                    'update' => 'api.inventories.update',
                    'store' => 'api.inventories.store',
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
                    'show' => 'api.inventorystatuslabels.show',
                    'update' => 'api.inventorystatuslabels.update',
                ],
                'except' => ['create', 'edit'],
                'parameters' => ['inventorystatuslabel' => 'iinventorystatuslabel_id'],
            ]
        ); // end nventory status labels API routes

        /**
         * Inventories API routes
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
        ); // end Inventories API routes


        /**
         * Devices API routes
         */

        Route::resource('devices',
            Api\DevicesController::class,
            ['names' =>
                [
                    'index' => 'api.devices.index',
                    'show' => 'api.devices.show',
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
        Route::resource('invoicetypes',
            Api\InvoiceTypesController::class,
            ['names' =>
                [
                    'index' => 'api.invoicetypes.index',
                ],
                'except' => ['create', 'edit'],
                'parameters' => ['invoicetype' => 'invoicetype_id'],
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

            Route::post('{id}/closesell',
                [
                    Api\ContractsController::class,
                    'closesell'
                ]
            )->name('api.contracts.closesell');

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

        /**
         * Massoperations API routes
         */

        Route::resource('massoperations', Api\MassOperationsController::class,
            [
                'names' =>
                    [
                        'index' => 'api.massoperations.index',
                    ],
                'parameters' => ['massoperation' => 'massoperation_id']
            ]
        ); // Massoperations resource



    /**
         * CUSTOM API ROUTES END
         */

}); // end API routes
