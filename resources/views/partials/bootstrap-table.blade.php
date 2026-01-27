@push('css')
    <link rel="stylesheet" href="{{ url(mix('css/dist/bootstrap-table.css')) }}">
@endpush

@push('js')

<script src="{{ url(mix('js/dist/bootstrap-table.js')) }}"></script>
<script src="{{ url(mix('js/dist/bootstrap-table-locale-all.min.js')) }}"></script>

<!-- load english again here, even though it's in the all.js file, because if BS table doesn't have the translation, it otherwise defaults to chinese. See https://bootstrap-table.com/docs/api/table-options/#locale -->
<script src="{{ url(mix('js/dist/bootstrap-table-en-US.min.js')) }}"></script>

<script nonce="{{ csrf_token() }}">
    $(function () {


        var blockedFields = "searchable,sortable,switchable,title,visible,formatter,class".split(",");

        var keyBlocked = function(key) {
            for(var j in blockedFields) {
                if (key === blockedFields[j]) {
                    return true;
                }
            }
            return false;
        }

        $('.snipe-table').bootstrapTable('destroy').each(function () {

            data_export_options = $(this).attr('data-export-options');
            export_options = data_export_options ? JSON.parse(data_export_options) : {};
            export_options['htmlContent'] = false; // this is already the default; but let's be explicit about it
            export_options['jspdf'] = {
                "orientation": "l",
                "autotable": {
                        "styles": {
                            overflow: 'linebreak'
                        },
                        tableWidth: 'wrap'
                }
            };
            // tableWidth: 'wrap',
            // the following callback method is necessary to prevent XSS vulnerabilities
            // (this is taken from Bootstrap Tables's default wrapper around jQuery Table Export)
            export_options['onCellHtmlData'] = function (cell, rowIndex, colIndex, htmlData) {
                if (cell.is('th')) {
                    return cell.find('.th-inner').text()
                }
                return htmlData
            }

            // This allows us to override the table defaults set below using the data-dash attributes
            var table = this;
            var data_with_default = function (key,default_value) {
                attrib_val = $(table).data(key);
                if(attrib_val !== undefined) {
                    return attrib_val;
                }
                return default_value;
            }



            $(this).bootstrapTable({

                ajaxOptions: {
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                },
                // reorderableColumns: true,
                // buttonsPrefix: "btn",
                addrbar: {{ (config('session.bs_table_addrbar') == 'true') ? 'true' : 'false'}}, // deeplink search phrases, sorting, etc
                advancedSearch: data_with_default('advanced-search', true),
                buttonsClass: "tableButton tableButton btn-theme hidden-print",
                buttonsOrder: [
                    'columns',
                    'btnAdd',
                    'btnShowDeleted',
                    'btnShowAdmins',
                    'btnShowExpiring',
                    'btnShowInactive',
                    'refresh',
                    'btnExport',
                    'export',
                    'print',
                    'fullscreen',
                    'advancedSearch',
                ],
                classes: 'table table-responsive table-striped snipe-table table-no-bordered',
                clickToSelect: data_with_default('click-to-select', true),
                cookie: true,
                cookieExpire: '2y',
                cookieStorage: '{{ config('session.bs_table_storage') }}',
                iconsPrefix: 'fa',
                maintainSelected: data_with_default('maintain-selected', true),
                minimumCountColumns: data_with_default('minimum-count-columns', 2),
                mobileResponsive: data_with_default('mobile-responsive', true),
                pagination: data_with_default('pagination', true),
                paginationFirstText: "{{ trans('general.first') }}",
                paginationLastText: "{{ trans('general.last') }}",
                paginationNextText: "{{ trans('general.next') }}",
                paginationPreText: "{{ trans('general.previous') }}",
                search: data_with_default('search', true),
                searchHighlight: data_with_default('search-highlight', true),
                showColumns: data_with_default('show-columns', true),
                showColumnsToggleAll: data_with_default('show-columns-toggle-all', true),
                showExport: data_with_default('show-export', true),
                showFullscreen: data_with_default('show-fullscreen', true),
                showPrint: data_with_default('show-print', true),
                showRefresh: data_with_default('show-refresh', true),
                showSearchClearButton: data_with_default('show-search-clear-button', true),
                sortName: data_with_default('sort-name', 'created_at'),
                sortOrder: data_with_default('sort-order', 'desc'),
                stickyHeader: true,
                stickyHeaderOffsetLeft: parseInt($('body').css('padding-left'), 10),
                stickyHeaderOffsetRight: parseInt($('body').css('padding-right'), 10),
                trimOnSearch: false,
                undefinedText: '',
                pageList: ['10', '20', '30', '50', '100', '150', '200'{!! ((config('app.max_results') > 200) ? ",'500'" : '') !!}{!! ((config('app.max_results') > 500) ? ",'".config('app.max_results')."'" : '') !!}],
                pageSize: {{  (($snipeSettings->per_page!='') && ($snipeSettings->per_page > 0)) ? $snipeSettings->per_page : 20 }},
                paginationVAlign: 'both',
                queryParams: function (params) {
                    var newParams = {};
                    for (var i in params) {
                        if (!keyBlocked(i)) { // only send the field if it's not in blockedFields
                            newParams[i] = params[i];
                        }
                    }
                    return newParams;
                },
                formatLoadingMessage: function () {
                    return '<h2><x-icon type="spinner" /> {{ trans('general.loading') }} </h2>';
                },
                icons: {
                    advancedSearchIcon: 'fas fa-search-plus',
                    paginationSwitchDown: 'fa-caret-square-o-down',
                    paginationSwitchUp: 'fa-caret-square-o-up',
                    fullscreen: 'fa-expand',
                    columns: 'fa-columns',
                    print: 'fa-print',
                    refresh: 'fas fa-sync-alt',
                    export: 'fa-download',
                    clearSearch: 'fa-times',
                },
                locale: '{{ app()->getLocale() }}',
                exportOptions: export_options,
                exportTypes: ['xlsx', 'excel', 'csv', 'pdf', 'json', 'xml', 'txt', 'sql', 'doc'],
                onLoadSuccess: function () { // possible 'fixme'? this might be for contents, not for headers?
                    $('[data-tooltip="true"]').tooltip(); // Needed to attach tooltips after ajax call
                },
                onPostHeader: function () {
                    var lookup = {};
                    var lookup_initialized = false;
                    var ths = $('th');
                    var toolbar_buttons = $('.tableButton');

                    ths.each(function (index, element) {
                        th = $(element);
                        //only populate the lookup table once; don't need to keep doing it.
                        if (!lookup_initialized) {
                            // th -> tr -> thead -> table
                            var table = th.parent().parent().parent()
                            var column_data = table.data('columns')

                            for (var column in column_data) {
                                lookup[column_data[column].field] = column_data[column].titleTooltip;
                            }

                            lookup_initialized = true
                        }

                        field = th.data('field'); // find fieldname this column refers to
                        title = lookup[field];

                        if (title) {
                            th.attr('data-toggle', 'tooltip');
                            th.attr('data-tooltip', 'true');
                            th.attr('data-placement', 'top');
                            th.tooltip({container: 'body', title: title});

                        }
                    });

                    // Add tooltips to the toolbar buttons too
                    toolbar_buttons.each(function (index, element) {
                        tableButton = $(element);
                        title = tableButton.attr('title');
                        override_class = tableButton.attr('class');

                        if (title) {
                            // Keep this commented out so that we don't interfere with the dropdown toggle for columns, etc
                            // tableButton.attr('data-toggle', 'tooltip');
                            tableButton.attr('data-tooltip', 'true');
                            tableButton.attr('data-placement', 'auto');

                            // This prevents the slight button jitter on the mouseovees on the dashboard
                            tableButton.tooltip({container: 'body', title: title});

                            // This handles the case where we want a different color button than the default
                            if ((override_class) && ((override_class.indexOf('btn-info') >= 0)) || (override_class.indexOf('btn-danger') >= 0)) {
                                tableButton.removeClass('btn-primary');
                            }
                        }
                    });

                },
                formatNoMatches: function () {
                    return '{{ trans('table.no_matching_records') }}';
                }

            });

        });
    });


    // User table buttons
    window.userButtons = () => ({
        @can('create', \App\Models\User::class)
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('users.create') }}';
            },
            attributes: {
                title: '{{ trans('general.create') }}',
                class: 'btn-warning',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
        @endcan

        btnExport: {
            text: '{{ trans('general.export_all_to_csv') }}',
            icon: 'fa-solid fa-file-csv',
            event () {
                window.location.href = '{{ route('users.export') }}';
            },
            attributes: {
                title: '{{ trans('general.export_all_to_csv') }}',
            }
        },

        btnShowAdmins: {
            text: '{{ trans('general.show_admins') }}',
            icon: 'fa-solid fa-crown',
            event () {
                window.location.href = '{{ (request()->input('admins') == "true") ? route('users.index') : route('users.index', ['admins' => 'true']) }}';
            },
            attributes: {
                title: '{{ trans('general.show_admins') }}',
                class: '{{ (request()->input('admins') == "true") ? ' btn-selected text-danger' : '' }}'
            }
        },

        btnShowDeleted: {
            text: '{{ (request()->input('status') == "deleted") ? trans('admin/users/table.show_current') : trans('admin/users/table.show_deleted') }}',
            icon: 'fa-solid fa-trash',
            event () {
                window.location.href = '{{ (request()->input('status') == "deleted") ? route('users.index') : route('users.index', ['status' => 'deleted']) }}';
            },
            attributes: {
                class: '{{ (request()->input('status') == "deleted") ? ' btn-selected' : '' }}',
                title: '{{ (request()->input('status') == "deleted") ? trans('admin/users/table.show_current') : trans('admin/users/table.show_deleted') }}',

            }
        },

    }); // end user table buttons


    @can('create', \App\Models\Company::class)
    // Company table buttons
    window.companyButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('companies.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },

    }); // End company table buttons
    @endcan


    @can('create', \App\Models\Groups::class)
    // Groups table buttons
    window.groupButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('groups.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },

    }); // End Groups table buttons
    @endcan


    // Asset table buttons
    window.assetButtons = () => ({
        @can('create', \App\Models\Asset::class)
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('hardware.create') }}';
            },
            attributes: {
                title: '{{ trans('general.create') }}',
                class: 'btn-warning',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
        @endcan

        @can('update', \App\Models\Asset::class)
        btnAddMaintenance: {
            text: '{{ trans('button.add_maintenance') }}',
            icon: 'fa-solid fa-screwdriver-wrench',
            event () {
                window.location.href = '{{ route('maintenances.create', ['asset_id' => (isset($asset)) ? $asset->id :'' ]) }}';
            },
            attributes: {
                title: '{{ trans('button.add_maintenance') }}',
            }
        },
        @endcan


        btnExport: {
            text: '{{ trans('admin/hardware/general.custom_export') }}',
            icon: 'fa-solid fa-file-csv',
            event () {
                window.location.href = '{{ route('reports/custom') }}';
            },
            attributes: {
                title: '{{ trans('admin/hardware/general.custom_export') }}',
            }
        },

        btnShowDeleted: {
            text: '{{ (request()->input('status') == "Deleted") ? trans('general.list_all') : trans('general.deleted') }}',
            icon: 'fa-solid fa-trash',
            event () {
                window.location.href = '{{ (request()->input('status') == "Deleted") ? route('hardware.index') : route('hardware.index', ['status' => 'Deleted']) }}';
            },
            attributes: {
                class: '{{ (request()->input('status') == "Deleted") ? 'btn-selected' : '' }}',
                title: '{{ (request()->input('status') == "Deleted") ? trans('general.list_all') : trans('general.deleted') }}',

            }
        },
    });

    window.purchaseButtons = () => ({
        @can('create', \App\Models\Purchase::class)
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('purchases.create') }}';
            },
            attributes: {
                title: '{{ trans('general.create') }}',
                class: 'btn-warning',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
        @endcan

        btnExport: {
            text: '{{ trans('admin/hardware/general.custom_export') }}',
            icon: 'fa-solid fa-file-csv',
            event () {
                window.location.href = '{{ route('reports/custom') }}';
            },
            attributes: {
                title: '{{ trans('admin/hardware/general.custom_export') }}',
            }
        },
        deleteAllRejected: {
            text: 'Удалить все отклоненные',
            icon: 'fa-solid fa-trash',
            event () {
                window.location.href = '{{ route('purchases.delete_all_rejected') }}';
            },
            attributes: {
                class: 'btn-danger',
                title: 'Удалить все отклоненные',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'a'
                @endif
            }
        },
    });

    window.inventoriesButtons = () => ({
        @can('create', \App\Models\Purchase::class)
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('purchases.create') }}';
            },
            attributes: {
                title: '{{ trans('general.create') }}',
                class: 'btn-warning',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
        @endcan

        btnExport: {
            text: '{{ trans('admin/hardware/general.custom_export') }}',
            icon: 'fa-solid fa-file-csv',
            event () {
                window.location.href = '{{ route('reports/custom') }}';
            },
            attributes: {
                title: '{{ trans('admin/hardware/general.custom_export') }}',
            }
        },
        deleteAllRejected: {
            text: 'Удалить все отклоненные',
            icon: 'fa-solid fa-trash',
            event () {
                window.location.href = '{{ route('purchases.delete_all_rejected') }}';
            },
            attributes: {
                class: 'btn-danger',
                title: 'Удалить все отклоненные',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'a'
                @endif
            }
        },
    });

    @can('create', \App\Models\Location::class)
    // Location table buttons
    window.locationButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('locations.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },

        btnShowDeleted: {
            text: '{{ (request()->input('status') == "deleted") ? trans('admin/users/table.show_current') : trans('admin/users/table.show_deleted') }}',
            icon: 'fa-solid fa-trash',
            event () {
                window.location.href = '{{ (request()->input('status') == "deleted") ? route('locations.index') : route('locations.index', ['status' => 'deleted']) }}';
            },
            attributes: {
                class: '{{ (request()->input('status') == "deleted") ? 'btn-selected' : '' }}',
                title: '{{ (request()->input('status') == "deleted") ? trans('admin/users/table.show_current') : trans('admin/users/table.show_deleted') }}',

            }
        },
    });
    @endcan

    @can('create', \App\Models\Accessory::class)
    // Accessory table buttons
    window.accessoryButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('accessories.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\Depreciation::class)
    // Accessory table buttons
    window.depreciationButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('depreciations.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\CustomField::class)
    // Accessory table buttons
    window.customFieldButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('fields.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan


    @can('create', \App\Models\CustomFieldset::class)
    // Accessory table buttons
    window.customFieldsetButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('fieldsets.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\Component::class)
    // Compoment table buttons
    window.componentButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('components.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\Consumable::class)
    // Consumable table buttons
    window.consumableButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('consumables.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },

        btnBulkcheckout: {
            text:  '{{ trans('general.bulk_checkout') }}',
            icon: 'fa fa-dolly',
            event () {
                window.location.href = '{{ route('consumables.bulkcheckout.show') }}';
            },
            attributes: {
                class: 'btn-info',
                title: '{{ trans('general.bulk_checkout') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\Manufacturer::class)
    // Manufacturer table buttons
    window.manufacturerButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('manufacturers.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            },
        },

        btnShowDeleted: {
            text: '{{ (request()->input('status') == "Deleted") ? trans('general.list_all') : trans('general.deleted') }}',
            icon: 'fa-solid fa-trash',
            event () {
                window.location.href = '{{ (request()->input('status') == "deleted") ? route('manufacturers.index') : route('manufacturers.index', ['status' => 'deleted']) }}';
            },
            attributes: {
                class: '{{ (request()->input('status') == "deleted") ? 'btn-selected' : '' }}',
                title: '{{ (request()->input('status') == "deleted") ? trans('general.list_all') : trans('general.deleted') }}',

            }
        },
    });
    @endcan

    @can('create', \App\Models\Supplier::class)
    // Consumable table buttons
    window.supplierButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('suppliers.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\Department::class)
    // Department table buttons
    window.departmentButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('departments.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\Department::class)
    // Custom Field table buttons
    window.departmentButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('departments.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('update', \App\Models\Asset::class)
    // Custom Field table buttons
    window.maintenanceButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('maintenances.create', ['asset_id' => (isset($asset)) ? $asset->id :'' ]) }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('button.add_maintenance') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\Category::class)
    // Custom Field table buttons
    window.categoryButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('categories.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\PredefinedKit::class)
    // Custom Field table buttons
    window.kitButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('kits.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan

    @can('create', \App\Models\AssetModel::class)
    // Custom Field table buttons
    window.modelButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('models.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
        btnShowDeleted: {
            text: '{{ (request()->input('status') == "deleted") ? trans('general.list_all') : trans('general.deleted') }}',
            icon: 'fa-solid fa-trash',
            event () {
                window.location.href = '{{ (request()->input('status') == "deleted") ? route('models.index') : route('models.index', ['status' => 'deleted']) }}';
            },
            attributes: {
                class: '{{ (request()->input('status') == "deleted") ? ' btn-selected' : '' }}',
                title: '{{ (request()->input('status') == "deleted") ? trans('general.list_all') : trans('general.deleted') }}',

            }
        },
    });
    @endcan

    @can('create', \App\Models\Statuslabel::class)
    // Status label table buttons
    window.statuslabelButtons = () => ({
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('statuslabels.create') }}';
            },
            attributes: {
                class: 'btn-info',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            }
        },
    });
    @endcan


    // License table buttons
    window.licenseButtons = () => ({
        @can('create', \App\Models\License::class)
        btnAdd: {
            text: '{{ trans('general.create') }}',
            icon: 'fa fa-plus',
            event () {
                window.location.href = '{{ route('licenses.create') }}';
            },
            attributes: {
                class: 'btn-warning',
                title: '{{ trans('general.create') }}',
                @if ($snipeSettings->shortcuts_enabled == 1)
                accesskey: 'n'
                @endif
            },
        },
        @endcan

        btnExport: {
            text: '{{ trans('general.export_all_to_csv') }}',
            icon: 'fa-solid fa-file-csv',
            event () {
                window.location.href = '{{ route('licenses.export', ['category_id' => (isset($category)) ? $category->id :'' ]) }}';
            },
            attributes: {
                title: '{{ trans('general.export_all_to_csv') }}',
            }
        },

        btnShowExpiring: {
            text: '{{ (request()->input('status') == "expiring") ? trans('general.list_all') : trans('general.show_expiring') }}',
            icon: 'fas fa-clock',
            event () {
                window.location.href = '{{ (request()->input('status') == "expiring") ? route('licenses.index') : route('licenses.index', ['status' => 'expiring']) }}';
            },
            attributes: {
                class: "{{ (request()->input('status') == "expiring") ? ' btn-warning' : '' }}",
                title: '{{ (request()->input('status') == "expiring") ? trans('general.list_all') : trans('general.show_expiring') }}',

            }
        },

        btnShowInactive: {
            text: '{{ (request()->input('status') == "inactive") ? trans('general.list_all') : trans('general.show_inactive') }}',
            icon: 'fas fa-history',
            event () {
                window.location.href = '{{ (request()->input('status') == "inactive") ? route('licenses.index') : route('licenses.index', ['status' => 'inactive']) }}';
            },
            attributes: {
                class: "{{ (request()->input('status') == "inactive") ? ' btn-warning' : '' }}",
                title: '{{ (request()->input('status') == "inactive") ? trans('general.list_all') : trans('general.show_inactive') }}',

            }
        },
    });





    function dateRowCheckStyle(value) {
        if ((value.days_to_next_audit) && (value.days_to_next_audit < {{ $snipeSettings->audit_warning_days ?: 0 }})) {
            return { classes : "danger" }
        }
        return {};
    }


    // These methods dynamically add/remove hidden input values in the bulk actions form
    $('.snipe-table').on('check.bs.table .btSelectItem', function (row, $element) {
        var buttonName =  $(this).data('bulk-button-id');
        var tableId =  $(this).data('id-table');

        $(buttonName).removeAttr('disabled');
        $(buttonName).after('<input id="' + tableId + '_checkbox_' + $element.id + '" type="hidden" name="ids[]" value="' + $element.id + '">');
    });

    $('.snipe-table').on('check-all.bs.table', function (event, rowsAfter) {

        var buttonName =  $(this).data('bulk-button-id');
        $(buttonName).removeAttr('disabled');
        var tableId =  $(this).data('id-table');

        for (var i in rowsAfter) {
            // Do not select things that were already selected
            if($('#'+ tableId + '_checkbox_' + rowsAfter[i].id).length == 0) {
                $(buttonName).after('<input id="' + tableId + '_checkbox_' + rowsAfter[i].id + '" type="hidden" name="ids[]" value="' + rowsAfter[i].id + '">');
            }
        }
    });


    $('.snipe-table').on('uncheck.bs.table .btSelectItem', function (row, $element) {
        var tableId =  $(this).data('id-table');
        $( "#" + tableId + "_checkbox_" + $element.id).remove();
    });


    // Handle whether the edit button should be disabled
    $('.snipe-table').on('uncheck.bs.table', function () {
        var buttonName =  $(this).data('bulk-button-id');

        if ($(this).bootstrapTable('getSelections').length == 0) {

            $(buttonName).attr('disabled', 'disabled');
        }
    });

    $('.snipe-table').on('uncheck-all.bs.table', function (event, rowsAfter, rowsBefore) {

        var buttonName =  $(this).data('bulk-button-id');
        $(buttonName).attr('disabled', 'disabled');
        var tableId =  $(this).data('id-table');

        for (var i in rowsBefore) {
            $('#' + tableId + "_checkbox_" + rowsBefore[i].id).remove();
        }

    });

    // Initialize sort-order for bulk actions (label-generation) for snipe-tables
    $('.snipe-table').each(function (i, table) {
        table_cookie_segment = $(table).data('cookie-id-table');
        sort = '';
        order = '';
        cookies = document.cookie.split(";");
        for(i in cookies) {
            cookiedef = cookies[i].split("=", 2);
            cookiedef[0] = cookiedef[0].trim();
            if (cookiedef[0] == table_cookie_segment + ".bs.table.sortOrder") {
                order = cookiedef[1];
            }
            if (cookiedef[0] == table_cookie_segment + ".bs.table.sortName") {
                sort = cookiedef[1];
            }
        }
        if (sort && order) {
            domnode = $($(this).data('bulk-form-id')).get(0);
            if ( domnode && domnode.elements && domnode.elements.sort ) {
                domnode.elements.sort.value = sort;
                domnode.elements.order.value = order;
            }
        }
    });

    // If sort order changes, update the sort-order for bulk-actions (for label-generation)
    $('.snipe-table').on('sort.bs.table', function (event, name, order) {
       domnode = $($(this).data('bulk-form-id')).get(0);
       // make safe in case there isn't a bulk-form-id, or it's not found, or has no 'sort' element
       if ( domnode && domnode.elements && domnode.elements.sort ) {
           domnode.elements.sort.value = name;
           domnode.elements.order.value = order;
       }
    });



    // This specifies the footer columns that should have special styles associated
    // (usually numbers)
    window.footerStyle = column => ({
        remaining: {
            classes: 'text-padding-number-footer-cell'
        },
        qty: {
            classes: 'text-padding-number-footer-cell',
        },
        purchase_cost: {
            classes: 'text-padding-number-footer-cell'
        },
        checkouts_count: {
            classes: 'text-padding-number-footer-cell'
        },
        assets_count: {
            classes: 'text-padding-number-footer-cell'
        },
        seats: {
            classes: 'text-padding-number-footer-cell'
        },
        free_seats_count: {
            classes: 'text-padding-number-footer-cell'
        },
    }[column.field]);




    // This only works for model index pages because it uses the row's model ID
    function genericRowLinkFormatter(destination) {
        return function (value,row) {

            if ((row) && (row.tag_color) && (row.tag_color!='') && (row.tag_color!=undefined)) {
                var tag_icon = '<i class="fa-solid fa-square" style="color: ' + row.tag_color + ';" aria-hidden="true"></i> ';
            } else {
                var tag_icon = '';
            }

            if (value) {
                return tag_icon + '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '">' + value + '</a>';
            }
        };
    }



    // This is a special formatter that will indicate whether a user is an admin or superadmin
    function usernameRoleLinkFormatter(value, row) {

            if ((value) && (row)) {

                if (row.role === 'superadmin') {
                    return '<span style="white-space: nowrap" data-tooltip="true" title="{{ trans('general.superuser_tooltip') }}"><x-icon type="superadmin" title="{{ trans('general.superuser') }}"  class="text-danger" /> <a href="{{ config('app.url') }}/users/' + row.id + '">' + value + '</a></span>';
                } else if (row.role === 'admin') {
                    return '<span style="white-space: nowrap" data-tooltip="true" title="{{ trans('general.admin_tooltip') }}"><x-icon type="superadmin" title="{{ trans('general.admin_user') }}" class="text-warning" /> <a href="{{ config('app.url') }}/users/' + row.id + '">' + value + '</a></span>';
                }

                // Regular user
                return '<a href="{{ config('app.url') }}/users/' + row.id + '">' + value + '</a>';
            }

    }

    // Use this when we're introspecting into a column object and need to link
    function genericColumnObjLinkFormatter(destination) {
        return function (value,row) {
            if ((value) && (value.status_meta)) {

                var text_color;
                var icon_style;
                var text_help;
                var status_meta = {
                  'deployed': '{{ strtolower(trans('general.deployed')) }}',
                  'deployable': '{{ strtolower(trans('admin/hardware/general.deployable')) }}',
                  'archived': '{{ strtolower(trans('general.archived')) }}',
                  'undeployable': '{{ strtolower(trans('general.undeployable')) }}',
                  'pending': '{{ strtolower(trans('general.pending')) }}'
                }

                switch (value.status_meta) {
                    case 'deployed':
                        text_color = 'blue';
                        icon_style = 'fa-circle';
                        text_help = '<label class="label label-default">{{ trans('general.deployed') }}</label>';
                    break;
                    case 'deployable':
                        text_color = 'green';
                        icon_style = 'fa-circle';
                        text_help = '';
                    break;
                    case 'pending':
                        text_color = 'orange';
                        icon_style = 'fa-circle';
                        text_help = '';
                        break;
                    default:
                        text_color = 'red';
                        icon_style = 'fa-times';
                        text_help = '';
                }

                return '<nobr><a href="{{ config('app.url') }}/' + destination + '/' + value.id + '" data-tooltip="true" title="'+ status_meta[value.status_meta] + '"> <i class="fa ' + icon_style + ' text-' + text_color + '"></i> ' + value.name + ' ' + text_help + ' </a> </nobr>';
            } else if ((value) && (value.name)) {

                // Add some overrides for any funny urls we have
                var dest = destination;
                var tag_color;
                var polymorphicItemFormatterDest = '';



                if (destination == 'fieldsets') {
                    var polymorphicItemFormatterDest = 'fields/';
                }

                // Handle the preceding icon if a tag_color is given in the API response
                if ((value.tag_color) && (value.tag_color!='')) {
                    var tag_icon = '<i class="fa-solid fa-square" style="color: ' + value.tag_color + ';" aria-hidden="true"></i>';
                } else {
                    var tag_icon = '';
                }

                return '<nobr>'+ tag_icon + ' <a href="{{ config('app.url') }}/' + polymorphicItemFormatterDest + dest + '/' + value.id + '">' + value.name + '</a></span>';
            }
        };
    }


    function colorTagFormatter(value, row) {
        if (value) {
            return '<i class="fa-solid fa-square" style="color: ' + value + ';" aria-hidden="true"></i> ' + value;
        }
    }




    function licenseKeyFormatter(value, row) {
        if (value) {
            return '<code class="single-line"><span class="js-copy-link" data-clipboard-target=".js-copy-key-' + row.id + '" aria-hidden="true" data-tooltip="true" data-placement="top" title="{{ trans('general.copy_to_clipboard') }}"><span class="js-copy-key-' + row.id + '">' + value + '</span></span></code>';
        }
    }



    function hardwareAuditFormatter(value, row) {
        return '<a href="{{ config('app.url') }}/hardware/' + row.id + '/audit" class="actions btn btn-sm btn-primary" data-tooltip="true" title="{{ trans('general.audit') }}"><x-icon type="audit" /><span class="sr-only">{{ trans('general.audit') }}</span></a>&nbsp;';
    }




    // Make the edit/delete buttons
    function genericActionsFormatter(owner_name, element_name) {
        if (!element_name) {
            element_name = '';
        }


        return function (value,row) {
            var actions = '<nobr>';

            // Add some overrides for any funny urls we have
            var dest = owner_name;

            if (dest =='groups') {
                var dest = 'admin/groups';
            }


            if(element_name != '') {
                dest = dest + '/' + row.owner_id + '/' + element_name;
            }


            /**
             *  START CUSTOM
             */

            if ((row.available_actions) && (row.available_actions.print_label === true)) {
                actions += '<span class="btn btn-sm btn-warning print_label" data-tooltip="true" title="Print label"><i class="fas fa-barcode" style="color: white" aria-hidden="true"></i><span class="sr-only">Print label</span></span>&nbsp;';
            }

            if ((row.available_actions) && (row.available_actions.inventory === true)) {
                actions += '<span class="btn btn-sm btn-warning inventory" data-tooltip="true" title="Inventory Item"><i class="fas fa-key" style="color: white" aria-hidden="true"></i><span class="sr-only">Inventory</span></span>&nbsp;';
            }

            if ((row.available_actions) && (row.available_actions.impersonate === true)) {
                actions += '<a href="{{ url('/') }}/impersonate/take/' + row.id + '/" class="btn btn-sm btn-danger" data-tooltip="true" title="Impersonate"><i class="fas fa-unlock" aria-hidden="true"></i><span class="sr-only">impersonate</span></a>&nbsp;';
            }

            /**
             *  END CUSTOM
             */

            if ((row.available_actions) && (row.available_actions.clone === true)) {
                actions += '<a href="{{ config('app.url') }}/' + dest + '/' + row.id + '/clone" class="actions btn btn-sm btn-info" data-tooltip="true" title="{{ trans('general.clone_item') }}"><x-icon type="clone" class="fa-fw" /><span class="sr-only">{{ trans('general.clone_item') }}</span></a>&nbsp;';
            }

            if ((row.available_actions) && (row.available_actions.audit === true)) {
                actions += '<a href="{{ config('app.url') }}/' + dest + '/' + row.id + '/audit" class="actions btn btn-sm btn-primary" data-tooltip="true" title="{{ trans('general.audit') }}"><x-icon type="audit" class="fa-fw" /><span class="sr-only">{{ trans('general.audit') }}</span></a>&nbsp;';
            }

            if ((row.available_actions) && (row.available_actions.update === true)) {
                actions += '<a href="{{ config('app.url') }}/' + dest + '/' + row.id + '/edit" class="actions btn btn-sm btn-warning" data-tooltip="true" title="{{ trans('general.update') }}"><x-icon type="edit" class="fa-fw" /><span class="sr-only">{{ trans('general.update') }}</span></a>&nbsp;';
            } else {
                if ((row.available_actions) && (row.available_actions.update != true)) {
                    actions += '<span data-tooltip="true" title="{{ trans('general.cannot_be_edited') }}"><a class="btn btn-warning btn-sm disabled" onClick="return false;"><x-icon type="edit" class="fa-fw" /></a></span>&nbsp;';
                }
            }

            if ((row.available_actions) && (row.available_actions.delete === true)) {

                // use the asset tag if no name is provided

                if (row.name) {
                    var name_for_box = row.name
                } else if (row.asset_tag) {
                    var name_for_box = row.asset_tag
                }


                
                actions += '<a href="{{ config('app.url') }}/' + dest + '/' + row.id + '" '
                    + ' class="actions btn btn-danger btn-sm delete-asset" data-tooltip="true"  '
                    + ' data-toggle="modal" data-icon="fa-trash"'
                    + ' data-content="{{ trans('general.sure_to_delete') }}: ' + name_for_box + '?" '
                    + ' data-title="{{  trans('general.delete') }}" onClick="return false;">'
                    + '<x-icon type="delete" class="fa-fw" /><span class="sr-only">{{ trans('general.delete') }}</span></a>&nbsp;';
            } else {
                // Do not show the delete button on things that are already deleted
                if ((row.available_actions) && (row.available_actions.restore != true)) {
                    actions += '<span data-tooltip="true" title="{{ trans('general.cannot_be_deleted') }}"><a class="btn btn-danger btn-sm delete-asset disabled" onClick="return false;"><x-icon type="delete" class="fa-fw" /><span class="sr-only">{{ trans('general.cannot_be_deleted') }}</span></a></span>&nbsp;';
                }

            }


            if ((row.available_actions) && (row.available_actions.restore === true)) {
                actions += '<form style="display: inline;" method="POST" action="{{ config('app.url') }}/' + dest + '/' + row.id + '/restore"> ';
                actions += '@csrf';
                actions += '<button class="btn btn-sm btn-warning" data-tooltip="true" title="{{ trans('general.restore') }}"><x-icon type="restore" class="fa-fw" /><span class="sr-only">{{ trans('general.restore') }}</span></button>&nbsp;';
            }

            actions +='</nobr>';
            return actions;

        };
    }


    // This handles the icons and display of polymorphic entries
    function polymorphicItemFormatter(value) {

        var item_destination = '';
        var item_icon;

        if ((value) && (value.type)) {

            if (value.type == 'asset') {
                item_destination = 'hardware';
                item_icon = 'fas fa-barcode';
            } else if (value.type == 'accessory') {
                item_destination = 'accessories';
                item_icon = 'far fa-keyboard';
            } else if (value.type == 'component') {
                item_destination = 'components';
                item_icon = 'far fa-hdd';
            } else if (value.type == 'consumable') {
                item_destination = 'consumables';
                item_icon = 'fas fa-tint';
            } else if (value.type == 'license') {
                item_destination = 'licenses';
                item_icon = 'far fa-save';
            } else if (value.type == 'user') {
                item_destination = 'users';
                item_icon = 'fas fa-user';
            } else if (value.type == 'location') {
                item_destination = 'locations'
                item_icon = 'fas fa-map-marker-alt';
            } else if (value.type == 'maintenance') {
                item_destination = 'maintenances'
                item_icon = 'fa-solid fa-screwdriver-wrench';
            } else if (value.type == 'model') {
                item_destination = 'models'
                item_icon = '';
            } else if (value.type == 'purchase') {
                item_destination = 'purchases'
                item_icon = 'fas fa-shopping-basket';
            } else if (value.type == 'contract') {
                item_destination = 'contracts'
                item_icon = 'fas fa-file';
            } else if (value.type == 'sale') {
                item_destination = 'sales'
                item_icon = 'fas fa-usd';
            } else if (value.type == 'deal') {
                item_destination = 'deals'
                item_icon = 'fas fa-usd';
            }

            // display the username if it's checked out to a user, but don't do it if the username's there already
            if (value.username && !value.name.match('\\(') && !value.name.match('\\)')) {
                value.name = value.name + ' (' + value.username + ')';
            }

            return '<nobr><a href="{{ config('app.url') }}/' + item_destination +'/' + value.id + '" data-tooltip="true" title="' + value.type + '"><i class="' + item_icon + ' fa-fw"></i> ' + value.name + '</a></nobr>';

        } else {
            return '';
        }


    }

    // This just prints out the item type in the activity report
    function itemTypeFormatter(value, row) {

        if ((row) && (row.item) && (row.item.type)) {
            return row.item.type;
        }
    }


    // Convert line breaks to <br>
    function notesFormatter(value) {
        if (value) {
            return value.replace(/(?:\r\n|\r|\n)/g, '<br />');
        }
    }

    // Check if checkbox should be selectable
    // Selectability is determined by the API field "selectable" which is set at the Presenter/API Transformer
    // However since different bulk actions have different requirements, we have to walk through the available_actions object
    // to determine whether to disable it
    function checkboxEnabledFormatter (value, row) {

        // add some stuff to get the value of the select2 option here?

        if ((row.available_actions) && (row.available_actions.bulk_selectable) && (row.available_actions.bulk_selectable.delete !== true)) {
            return {
                disabled:true,
                //checked: false, <-- not sure this will work the way we want?
            }
        }
    }

    function licenseInOutFormatter(value, row) {

        // check that checkin is not disabled
        if (row.user_can_checkout === false) {
            return '<span class="btn btn-sm bg-maroon btn-checkout disabled" data-tooltip="true" title="{{ trans('admin/licenses/message.checkout.unavailable') }}">{{ trans('general.checkout') }}</span>';
        } else if (row.disabled === true) {
            return '<span class="btn btn-sm bg-maroon btn-checkout disabled" data-tooltip="true" title="{{ trans('admin/licenses/message.checkout.license_is_inactive') }}">{{ trans('general.checkout') }}</span>';

        } else
            // The user is allowed to check the license seat out and it's available
        if ((row.available_actions.checkout === true) && (row.user_can_checkout === true) && (row.disabled === false)) {
            return '<a href="{{ config('app.url') }}/licenses/' + row.id + '/checkout/" class="btn btn-sm bg-maroon btn-checkout" data-tooltip="true" title="{{ trans('general.checkout_tooltip') }}">{{ trans('general.checkout') }}</a>';
        }
    }
    // We need a special formatter for license seats, since they don't work exactly the same
    // Checkouts need the license ID, checkins need the specific seat ID

    function licenseSeatInOutFormatter(value, row) {
        if (row.disabled && (row.assigned_user || row.assigned_asset)) {
            return '<a href="{{ config('app.url') }}/licenses/' + row.id + '/checkin" class="btn btn-sm bg-purple" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">{{ trans('general.checkin') }}</a>';
        }
        if (row.disabled) {
            return '<a href="{{ config('app.url') }}/licenses/' + row.id + '/checkin" class="btn btn-sm bg-maroon btn-checkout disabled" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">{{ trans('general.checkout') }}</a>';
        }
        // The user is allowed to check the license seat out and it's available
        if ((row.available_actions.checkout === true) && (row.user_can_checkout === true) && ((!row.assigned_asset) && (!row.assigned_user))) {
            return '<a href="{{ config('app.url') }}/licenses/' + row.license_id + '/checkout/'+row.id+'" class="btn btn-sm bg-maroon btn-checkout" data-tooltip="true" title="{{ trans('general.checkout_tooltip') }}">{{ trans('general.checkout') }}</a>';
        }

        // The user is allowed to check the license seat in and it's available
        if ((row.available_actions.checkin === true) && ((row.assigned_asset) || (row.assigned_user))) {
            return '<a href="{{ config('app.url') }}/licenses/' + row.id + '/checkin/" class="btn btn-sm bg-purple btn-checkin" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">{{ trans('general.checkin') }}</a>';
        }

    }

    function genericCheckinCheckoutFormatter(destination) {
        return function (value, row) {

            // The user is allowed to check items out, AND the item is deployable
            if ((row.available_actions.checkout == true) && (row.user_can_checkout == true) && ((!row.asset_id) && (!row.assigned_to))) {

                    return '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '/checkout" class="btn btn-sm bg-maroon btn-checkout" data-tooltip="true" title="{{ trans('general.checkout_tooltip') }}">{{ trans('general.checkout') }}</a>';

            // The user is allowed to check items out, but the item is not able to be checked out
            } else if (((row.user_can_checkout == false)) && (row.available_actions.checkout == true) && (!row.assigned_to)) {

                // We use slightly different language for assets versus other things, since they are the only
                // item that has a status label
                if (destination =='hardware') {
                    return '<span  data-tooltip="true" title="{{ trans('admin/hardware/general.undeployable_tooltip') }}"><a class="btn btn-sm bg-maroon btn-checkout disabled">{{ trans('general.checkout') }}</a></span>';
                } else {
                    return '<span  data-tooltip="true" title="{{ trans('general.undeployable_tooltip') }}"><a class="btn btn-sm bg-maroon btn-checkout disabled">{{ trans('general.checkout') }}</a></span>';
                }

            // The user is allowed to check items in
            } else if (row.available_actions.checkin == true)  {
                if (row.assigned_to) {
                    return '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '/checkin" class="btn btn-sm bg-purple btn-checkin" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">{{ trans('general.checkin') }}</a>';
                } else if (row.assigned_pivot_id) {
                    return '<a href="{{ config('app.url') }}/' + destination + '/' + row.assigned_pivot_id + '/checkin" class="btn btn-sm bg-purple btn-checkin" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">{{ trans('general.checkin') }}</a>';
                }

            }

        }


    }


    // This is only used by the requestable assets section
    function assetRequestActionsFormatter (row, value) {
        if (value.assigned_to_self == true){
            return '<button class="btn btn-danger btn-sm btn-block disabled" data-tooltip="true" title="{{ trans('admin/hardware/message.requests.cancel') }}">{{ trans('button.cancel') }}</button>';
        } else if (value.available_actions.cancel == true)  {
            return '<form action="{{ config('app.url') }}/account/request-asset/' + value.id + '/cancel" method="POST">@csrf<button class="btn btn-danger btn-block btn-sm" data-tooltip="true" title="{{ trans('admin/hardware/message.requests.cancel') }}">{{ trans('button.cancel') }}</button></form>';
        } else if (value.available_actions.request == true)  {
            return '<form action="{{ config('app.url') }}/account/request-asset/'+ value.id + '" method="POST">@csrf<button class="btn btn-block btn-primary btn-sm" data-tooltip="true" title="{{ trans('general.request_item') }}">{{ trans('button.request') }}</button></form>';
        }

    }



    var formatters = [
        'accessories',
        'categories',
        'companies',
        'components',
        'consumables',
        'departments',
        'depreciations',
        'fieldsets',
        'groups',
        'hardware',
        'kits',
        'licenses',
        'locations',
        'maintenances',
        'manufacturers',
        'models',
        'statuslabels',
        'suppliers',
        'users',
        'inventories',
        'purchases',
        'inventorystatuslabels',
        'contracts',
        'deals',
        'bulk',
        'devices',
        'invoicetypes'
    ];

    for (var i in formatters) {
        window[formatters[i] + 'LinkFormatter'] = genericRowLinkFormatter(formatters[i]);
        window[formatters[i] + 'LinkObjFormatter'] = genericColumnObjLinkFormatter(formatters[i]);
        window[formatters[i] + 'ActionsFormatter'] = genericActionsFormatter(formatters[i]);
        window[formatters[i] + 'InOutFormatter'] = genericCheckinCheckoutFormatter(formatters[i]);
    }

    var child_formatters = [
        ['kits', 'models'],
        ['kits', 'licenses'],
        ['kits', 'consumables'],
        ['kits', 'accessories'],
    ];

    for (var i in child_formatters) {
        var owner_name = child_formatters[i][0];
        var child_name = child_formatters[i][1];
        window[owner_name + '_' + child_name + 'ActionsFormatter'] = genericActionsFormatter(owner_name, child_name);
    }



    // This is  gross, but necessary so that we can package the API response
    // for custom fields in a more useful way.
    function customFieldsFormatter(value, row) {


            if ((!this) || (!this.title)) {
                return '';
            }

            var field_column = this.title;

            // Pull out any HTMl that might be passed via the presenter
            // (for example, the locked icon for encrypted fields)
            var field_column_plain = field_column.replace(/<(?:.|\n)*?> ?/gm, '');
            if ((row.custom_fields) && (row.custom_fields[field_column_plain])) {

                // If the field type needs special formatting, do that here
                if ((row.custom_fields[field_column_plain].field_format) && (row.custom_fields[field_column_plain].value)) {
                    if (row.custom_fields[field_column_plain].field_format=='URL') {
                        return '<a href="' + row.custom_fields[field_column_plain].value + '" target="_blank" rel="noopener">' + row.custom_fields[field_column_plain].value + '</a>';
                    } else if (row.custom_fields[field_column_plain].field_format=='BOOLEAN') {
                        return (row.custom_fields[field_column_plain].value == 1) ? "<span class='fas fa-check-circle' style='color:green'>" : "<span class='fas fa-times-circle' style='color:red' />";
                    } else if (row.custom_fields[field_column_plain].field_format=='EMAIL') {
                        return '<a href="mailto:' + row.custom_fields[field_column_plain].value + '" style="white-space: nowrap" data-tooltip="true" title="{{ trans('general.send_email') }}"><x-icon type="email" /> ' + row.custom_fields[field_column_plain].value + '</a>';
                    }
                }
                return row.custom_fields[field_column_plain].value;

            }

    }


    function createdAtFormatter(value) {
        if ((value) && (value.formatted)) {
            return value.formatted;
        }
    }

    function externalLinkFormatter(value) {

        if (value) {
            if ((value.indexOf("{") === -1) || (value.indexOf("}") ===-1)) {
                return '<nobr><a href="' + value + '" target="_blank" title="{{ trans('general.external_link_tooltip') }} ' + value + '" data-tooltip="true"><x-icon type="external-link" /> ' + value + '</a></nobr>';
            }
            return value;
        }
    }

    function groupsFormatter(value) {

        if (value) {
            var groups = '';
            for (var index in value.rows) {
                groups += '<a href="{{ config('app.url') }}/admin/groups/' + value.rows[index].id + '" class="label label-default">' + value.rows[index].name + '</a> ';
            }
            return groups;
        }
    }



    function changeLogFormatter(value) {

        var result = '';
        var pretty_index = '';

            for (var index in value) {


                // Check if it's a custom field
                if (index.startsWith('_snipeit_')) {
                    pretty_index = index.replace("_snipeit_", "Custom:_");
                } else {
                    pretty_index = index;
                }

                extra_pretty_index = prettyLog(pretty_index);

                result += extra_pretty_index + ': <del>' + value[index].old + '</del>  <x-icon type="long-arrow-right" /> ' + value[index].new + '<br>'
            }

        return result;

    }

    function prettyLog(str) {
        let frags = str.split('_');
        for (let i = 0; i < frags.length; i++) {
            frags[i] = frags[i].charAt(0).toUpperCase() + frags[i].slice(1);
        }
        return frags.join(' ');
    }

    // Show the warning if below min qty
    function minAmtFormatter(row, value) {

        if ((row) && (row!=undefined)) {
            
            if (value.remaining <= value.min_amt) {
                return  '<span class="text-danger text-bold" data-tooltip="true" title="{{ trans('admin/licenses/general.below_threshold_short') }}"><x-icon type="warning" class="text-yellow" /> ' + value.min_amt + '</span>';
            }
            return value.min_amt
        }
        return '--';
    }

    

    // Create a linked phone number in the table list
    function phoneFormatter(value) {
        if (value) {
            return  '<span style="white-space: nowrap;"><a href="tel:' + value + '" data-tooltip="true" title="{{ trans('general.call') }}"><x-icon type="phone" /> ' + value + '</a></span>';
        }
    }

    // Create a linked phone number in the table list
    function mobileFormatter(value) {
        if (value) {
            return  '<span style="white-space: nowrap;"><a href="tel:' + value + '" data-tooltip="true" title="{{ trans('general.call') }}"><x-icon type="mobile" /> ' + value + '</a></span>';
        }
    }


    function deployedLocationFormatter(row, value) {
        if ((row) && (row!=undefined)) {
            // Handle the preceding icon if a tag_color is given in the API response
            if ((row.tag_color) && (row.tag_color!='')) {
                var tag_icon = '<i class="fa-solid fa-square" style="color: ' + row.tag_color + ';" aria-hidden="true"></i> ';
            } else {
                var tag_icon = '';
            }

            return '<nobr>' + tag_icon +'<a href="{{ config('app.url') }}/locations/' + row.id + '">' + row.name + '</a></nobr>';
        } else if (value.rtd_location) {
            return '<a href="{{ config('app.url') }}/locations/' + value.rtd_location.id + '">' + value.rtd_location.name + '</a>';
        }

    }

    function groupsAdminLinkFormatter(value, row) {
        return '<a href="{{ config('app.url') }}/admin/groups/' + row.id + '">' + value + '</a>';
    }

    function assetTagLinkFormatter(value, row) {
        if ((row.asset) && (row.asset.id)) {
            if (row.asset.deleted_at) {
                return '<span style="white-space: nowrap;"><x-icon type="x" class="text-danger" /><span class="sr-only">{{ trans('admin/hardware/general.deleted') }}</span> <del><a href="{{ config('app.url') }}/hardware/' + row.asset.id + '" data-tooltip="true" title="{{ trans('admin/hardware/general.deleted') }}">' + row.asset.asset_tag + '</a></del></span>';
            }
            return '<a href="{{ config('app.url') }}/hardware/' + row.asset.id + '">' + row.asset.asset_tag + '</a>';
        }
        return '';

    }

    function departmentNameLinkFormatter(value, row) {
        if ((row.assigned_user) && (row.assigned_user.department) && (row.assigned_user.department.name)) {
            return '<a href="{{ config('app.url') }}/departments/' + row.assigned_user.department.id + '">' + row.assigned_user.department.name + '</a>';
        }

    }

    function assetNameLinkFormatter(value, row) {
        if ((row.asset) && (row.asset.name)) {
            return '<a href="{{ config('app.url') }}/hardware/' + row.asset.id + '">' + row.asset.name + '</a>';
        }
    }

    function assetSerialLinkFormatter(value, row) {

        if ((row.asset) && (row.asset.serial)) {
            if (row.asset.deleted_at) {
                return '<span style="white-space: nowrap;"><x-icon type="x" class="text-danger" /><span class="sr-only">deleted</span> <del><a href="{{ config('app.url') }}/hardware/' + row.asset.id + '" data-tooltip="true" title="{{ trans('admin/hardware/general.deleted') }}">' + row.asset.serial + '</a></del></span>';
            }
            return '<a href="{{ config('app.url') }}/hardware/' + row.asset.id + '">' + row.asset.serial + '</a>';
        }
        return '';
    }

    function trueFalseFormatter(value) {
        if ((value) && ((value == 'true') || (value == '1'))) {
            return '<x-icon type="checkmark" class="text-success" /><span class="sr-only">{{ trans('general.true') }}</span>';
        } else {
            return '<x-icon type="x" class="text-danger" /><span class="sr-only">{{ trans('general.false') }}</span>';
        }
    }

    function dateDisplayFormatter(value) {
        if (value) {
            return  value.formatted;
        }
    }

    function iconFormatter(value) {
        if (value) {
            return '<i class="' + value + '  icon-med"></i>';
        }
    }

    function emailFormatter(value) {
        if (value) {
            return '<a href="mailto:' + value + '" style="white-space: nowrap" data-tooltip="true" title="{{ trans('general.send_email') }}"><x-icon type="email" /> ' + value + '</a>';
        }
    }

    function linkFormatter(value) {
        if (value) {
            return '<a href="' + value + '">' + value + '</a>';
        }
    }

    function assetCompanyFilterFormatter(value, row) {
        if (value) {
            return '<a href="{{ config('app.url') }}/hardware/?company_id=' + row.id + '">' + value + '</a>';
        }
    }

    function assetCompanyObjFilterFormatter(value, row) {
        if ((row) && (row.company)) {
            return '<a href="{{ config('app.url') }}/hardware/?company_id=' + row.company.id + '">' + row.company.name + '</a>';
        }
    }

    function usersCompanyObjFilterFormatter(value, row) {
        if (value) {
            return '<a href="{{ config('app.url') }}/users/?company_id=' + row.id + '">' + value + '</a>';
        } else {
            return value;
        }
    }

    function locationCompanyObjFilterFormatter(value, row) {
        if (value) {
            return '<a href="{{ url('/') }}/locations/?company_id=' + row.company.id + '">' + row.company.name + '</a>';
        } else {
            return value;
        }
    }

    function employeeNumFormatter(value, row) {

        if ((row) && (row.assigned_to) && ((row.assigned_to.employee_number))) {
            return '<a href="{{ config('app.url') }}/users/' + row.assigned_to.id + '">' + row.assigned_to.employee_number + '</a>';
        }
    }

    function jobtitleFormatter(value, row) {
        if ((row) && (row.assigned_to) && ((row.assigned_to.jobtitle))) {
            return '<a href="{{ config('app.url') }}/users/' + row.assigned_to.id + '">' + row.assigned_to.jobtitle + '</a>';
        }
    }

    function orderNumberObjFilterFormatter(value, row) {
        if (value) {
            return '<a href="{{ config('app.url') }}/hardware/?order_number=' + row.order_number + '">' + row.order_number + '</a>';
        }
    }

    function auditImageFormatter(value, row) {
        if ((row) && (row.file) && (row.file.url)) {
            return '<a href="' + row.file.url + '" data-toggle="lightbox" data-type="image"><img src="' + row.file.url + '" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive" alt=""></a>'
        }
    }


   function imageFormatter(value, row) {

        if (value) {

            // This is a clunky override to handle unusual API responses where we're presenting a link instead of an array
            if (row.avatar) {
                var altName = '';
            }
            else if (row.name) {
                var altName = row.name;
            }
            else if ((row) && (row.model)) {
                var altName = row.model.name;
           }
            return '<a href="' + value + '" data-toggle="lightbox" data-type="image"><img src="' + value + '" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive" alt="' + altName + '"></a>';
        }
    }


    // This is users in the user accounts section for EULAs
    function downloadFormatter(value) {
        if (value) {
            return '<a href="' + value + '" class="btn btn-sm btn-theme"><x-icon type="download" /></a>';
        }
    }

    // This is used by the UploadedFilesPresenter and the HistoryPresenter
    // It handles the download and inline buttons for files that are uploaded to assets, users, etc
    function fileDownloadButtonsFormatter(row, value) {

        if (value)  {
            if (value.url) {
                var inlinable = value.inlineable;
                var exists_on_disk = value.exists_on_disk;
                var download_url = value.url;
            } else if (value.file) {
                var inlinable = value.file.inlineable;
                var exists_on_disk = value.file.exists_on_disk;
                var download_url = value.file.url;
            } else {
                return '';
            }

            var download_button = '<a href="' + download_url + '" class="btn btn-sm btn-theme" data-tooltip="true" title="{{ trans('general.download') }}"><x-icon type="download" /></a>';
            var download_button_disabled = '<span data-tooltip="true" title="{{ trans('general.file_does_not_exist') }}"><a class="btn btn-sm btn-theme disabled"><x-icon type="download" /></a></span>';
            var inline_button = '<a href="'+ download_url +'?inline=true" class="btn btn-sm btn-theme" target="_blank" data-tooltip="true" title="{{ trans('general.open_new_window') }}"><x-icon type="external-link" /></a>';
            var inline_button_disabled = '<span data-tooltip="true" title="{{ trans('general.file_not_inlineable') }}"><a class="btn btn-sm btn-theme disabled" target="_blank" data-tooltip="true" title="{{ trans('general.file_does_not_exist') }}"><x-icon type="external-link" /></a></span>';

            if (exists_on_disk === true) {
                if (inlinable === true) {
                    return '<span style="white-space: nowrap;">' + download_button + ' ' + inline_button + '</span>';
                } else {
                    return '<span style="white-space: nowrap;">' + download_button + ' ' + inline_button_disabled + '</span>';
                }
            } else {
                return '<span style="white-space: nowrap;">' + download_button_disabled + ' ' + inline_button_disabled + '</span>';
            }

        }
    }


    function filePreviewFormatter(row, value) {

        if ((value) && (value.url) && (value.inlineable)) {

            if (value.mediatype == 'image') {
                return '<a href="' + value.url + '" data-toggle="lightbox" data-type="image"><img src="' + value.url + '" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive" alt=""></a>';
            } else if (value.mediatype == 'video') {
                return '<a href="' + value.url + '?inline=true" data-toggle="lightbox" data-type="video"><video style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive"><source src="' + value.url + '?inline=true"></video></a>';
            } else if (value.mediatype == 'audio') {
                return '<audio controls><source src="' + value.url + '?inline=true" type="audio/mp3">Your browser does not support the audio element.</audio>';
            }
            return '{{ trans('general.preview_not_available') }}';
        }
        return '{{ trans('general.preview_not_available') }}';

    }




    // This is used in the table listings
    function deleteUploadFormatter(value, row) {

        if ((row.available_actions) && (row.available_actions.delete === true)) {
            var destination;

            // This is kinda gross, but for right now we're posting to the GUI delete routes
            // All of these URLS and storage directories need to be updated to be more consistent :(
            if (row.item.type === 'assetmodels') {
                destination = 'models';
            } else if (row.item.type === 'assets') {
                destination = 'hardware';
            } else {
                destination = row.item.type;
            }

            return '<a href="{{ config('app.url') }}/' + destination + '/' + row.item.id + '/files/' + row.id + '/delete" '
                + ' data-target="#dataConfirmModal" class="actions btn btn-danger btn-sm delete-asset" data-tooltip="true"  '
                + ' data-toggle="modal" data-icon="fa-trash"'
                + ' data-content="{{ trans('general.file_upload_status.confirm_delete') }}: ' + row.filename + '?" '
                + ' data-title="{{  trans('general.delete') }}" onClick="return false;" data-icon="fa-trash">'
                + '<x-icon type="delete" /><span class="sr-only">{{ trans('general.delete') }}</span></a>&nbsp;';
        }
    }

    // This handles the custom view for the filestable blade component gallery-card component
    window.customViewFormatter = data => {
        const template = $('#fileGalleryTemplate').html()
        let view = ''

        $.each(data, function (i, row) {

            delete_url = row.url +'/delete';

            if (row.exists_on_disk === true)
            {
                if (row.mediatype === 'image') {
                    embed_code = '<a href="' + row.url + '" data-toggle="lightbox" data-type="image" data-title="' + row.filename + row.filename + '" data-footer="' + row.note + '" class="embed-responsive-item"><img src="' + row.url + '?inline=true" alt="" style="max-width: 100%"></a>';
                } else if (row.mediatype === 'video') {
                    embed_code = '<a href="' + row.url + '" data-toggle="lightbox" data-type="video" data-title="' + row.filename + row.filename + '" data-footer="' + row.note + '" class="embed-responsive-item"><video controls><source src="' + row.url + '?inline=true" type="video/mp4">Your browser does not support the video tag.</video></a>';
                } else if (row.mediatype === 'audio') {
                    embed_code = '<audio style="width: 100%" controls><source src="' + row.url + '?inline=true" type="audio/mpeg">Your browser does not support the audio element.</audio>';
                } else if (row.mediatype === 'pdf') {
                    embed_code = '<object height="200" style="width: 100%" type="application/pdf" data="' + row.url + '?inline=true">File cannot be displayed</object>';
                } else {
                    embed_code = '<div class="text-center"><a href="' + row.url + '?inline=true"><i class="' + row.icon + '" style="font-size: 50px" /></i></a></div>';
                }
            } else {
                embed_code = '<div class="text-center text-danger" style="padding-top: 20px;"><i class="fa-solid fa-heart-crack" style="font-size: 80px" /></i> <br><br>{{ trans('general.file_upload_status.file_not_found') }}</div>';
            }

            view += template.replace('%ID%', row.id)
                .replace('%ICON%', row.icon)
                .replace('%FILETYPE%', row.filetype)
                .replace('%FILE_URL%', row.url)
                .replace('%LINK_URL%', row.url)
                .replace('%FILENAME%', (row.exists_on_disk === true) ? row.filename : '<x-icon type="x" /> <del>' + row.filename + '</del>')
                .replace('%CREATED_AT%', row.created_at.formatted)
                .replace('%CREATED_BY%', (row.created_by) ? row.created_by.name : '')
                .replace('%NOTE%', (row.note) ? row.note : '')
                .replace('%PANEL_CLASS%', (row.exists_on_disk === true) ? 'default' : 'danger')
                .replace('%FILE_EMBED%', embed_code)
                .replace('%DOWNLOAD_BUTTON%', (row.exists_on_disk === true) ? '<a href="'+ row.url +'" class="btn btn-sm btn-theme"><x-icon type="download" /></a> ' : '<span class="btn btn-sm btn-theme disabled" data-tooltip="true" title="{{ trans('general.file_upload_status.file_not_found') }}"><x-icon type="download" /></span>')
                .replace('%NEW_WINDOW_BUTTON%', (row.exists_on_disk === true) ? '<a href="'+ row.url +'?inline=true" class="btn btn-sm btn-theme" target="_blank"><x-icon type="external-link" /></a> ' : '<span class="btn btn-sm btn-theme disabled" data-tooltip="true" title="{{ trans('general.file_upload_status.file_not_found') }}"><x-icon type="external-link"/></span>')
                .replace('%DELETE_BUTTON%', (row.available_actions.delete === true) ?
                    '<a href="'+delete_url+'" class="delete-asset btn btn-danger btn-sm" data-icon="fa-trash" data-toggle="modal" data-content="{{ trans('general.file_upload_status.confirm_delete') }} '+ row.filename +'?" data-title="{{ trans('general.delete') }}" onClick="return false;" data-target="#dataConfirmModal"><x-icon type="delete" /><span class="sr-only">{{ trans('general.delete') }}</span></a>' :
                    '<a class="btn btn-sm btn-danger disabled" data-tooltip="true" title="{{ trans('general.file_upload_status.file_not_found') }}"><x-icon type="delete" /><span class="sr-only">{{ trans('general.delete') }}</span></a>'
                );
        })

        return `<div class="row">${view}</div>`
    }



    function fileNameFormatter(row, value) {

        if (value) {
            if ((value.file) && (value.file.filename) && (value.file.url)) {

                if (value.file.exists_on_disk === true) {
                    return '<a href="' + value.file.url + '">' + value.file.filename + '</a>';
                }

                return '<span class="text-danger" style="text-decoration: line-through;" data-tooltip="true" title="{{ trans('general.file_does_not_exist') }}"><x-icon type="x" /> ' + value.file.filename + '</span>';

            } else if ((value.filename) && (value.url)) {
                if (value.exists_on_disk === true) {
                    return '<a href="' + value.url + '">' + value.filename + '</a>';
                }
                return '<span class="text-danger" style="text-decoration: line-through;" data-tooltip="true" title="{{ trans('general.file_does_not_exist') }}"><x-icon type="x" /> ' + value.filename + '</span>';
            }
        }

    }


    function linkToUserSectionBasedOnCount (count, id, section) {
        if (count) {
            return '<a href="{{ config('app.url') }}/users/' + id + '#' + section +'">' + count + '</a>';
        }

        return count;
    }

    function linkNumberToUserAssetsFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'asset');
    }

    function linkNumberToUserLicensesFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'licenses');
    }

    function linkNumberToUserConsumablesFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'consumables');
    }

    function linkNumberToUserAccessoriesFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'accessories');
    }

    function linkNumberToUserManagedUsersFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'managed-users');
    }

    function linkNumberToUserManagedLocationsFormatter(value, row) {
        return linkToUserSectionBasedOnCount(value, row.id, 'managed-locations');
    }

    function labelPerPageFormatter(value, row, index, field) {
        if (row) {
            if (!row.hasOwnProperty('sheet_info')) { return 1; }
            else { return row.sheet_info.labels_per_page; }
        }
    }

    function labelRadioFormatter(value, row, index, field) {
        if (row) {
            return row.name == '{{ str_replace("\\", "\\\\", $snipeSettings->label2_template) }}';
        }
    }

    function labelSizeFormatter(value, row) {
        if (row) {
            return row.width + ' x ' + row.height + ' ' + row.unit;
        }
    }

    function cleanFloat(number) {
        if(!number) { // in a JavaScript context, meaning, if it's null or zero or unset
            return 0.0;
        }
        if ("{{$snipeSettings->digit_separator}}" == "1.234,56") {
            // yank periods, change commas to periods
            periodless = number.toString().replace(/\./g,"");
            decimalfixed = periodless.replace(/,/g,".");
        } else {
            // yank commas, that's it.
            decimalfixed = number.toString().replace(/\,/g,"");
        }
        return parseFloat(decimalfixed);
    }


    function qtySumFormatter(data) {
        var currentField = this.field;
        var total = 0;
        var fieldname = this.field;

        $.each(data, function() {
            var r = this;
            total += this[currentField];
        });
        return total;
    }

    function sumFormatter(data) {
        if (Array.isArray(data)) {
            var field = this.field;
            var total_sum = data.reduce(function(sum, row) {
                
                return (sum) + (cleanFloat(row[field]) || 0);
            }, 0);
            
            return numberWithCommas(total_sum.toFixed(2));
        }
        return 'not an array';
    }

    function sumFormatterQuantity(data){
        if(Array.isArray(data)) {
            
            // Prevents issues on page load where data is an empty array
            if(data[0] == undefined){
                return 0.00
            }
            // Check that we are actually trying to sum cost from a table
            // that has a quantity column. We must perform this check to
            // support licences which use seats instead of qty
            if('qty' in data[0]) {
                var multiplier = 'qty';
            } else if('seats' in data[0]) {
                var multiplier = 'seats';
            } else {
                return 'no quantity';
            }
            var total_sum = data.reduce(function(sum, row) {
                return (sum) + (cleanFloat(row["purchase_cost"])*row[multiplier] || 0);
            }, 0);
            return numberWithCommas(total_sum.toFixed(2));
        }
        return 'not an array';
    }

    function numberWithCommas(value) {
        
        if ((value) && ("{{$snipeSettings->digit_separator}}" == "1.234,56")){
            var parts = value.toString().split(".");
             parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ".");
             return parts.join(",");
         } else {
             var parts = value.toString().split(",");
             parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
             return parts.join(".");
        }
        return value
    }

        /**
        * START CUSTOM
         */
        function contractsPriceFormatter(value,row) {
            return value.toLocaleString('ru');
        }
        function contractsFullPriceFormatter(value,row) {
            var full_price = row.assets_sum_purchase_cost+ row.consumables_cost;
            return full_price.toLocaleString('ru');
        }

        {{--function hardwareCustomInOutFormatter(value,row) {--}}
        {{--        var destination = "hardware";--}}

        {{--        if ((row.available_actions.review == true) && (row.user_can_review == true)) {--}}
        {{--            return '<button type="button" class="btn btn-primary btn-sm review" data-tooltip="true" title="Проверка">Проверить</button>';--}}
        {{--        }--}}

        {{--        // The user is allowed to check items out, AND the item is deployable--}}
        {{--        if ((row.available_actions.checkout == true) && (row.user_can_checkout == true) && ((!row.asset_id) && (!row.assigned_to))) {--}}
        {{--            return '<div class="btn-group" style="min-width:270px">' +--}}
        {{--                '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '/checkout" class="btn btn-sm bg-maroon" data-tooltip="true" title="{{ trans('general.checkout_tooltip') }}">{{ trans('general.checkout') }}</a>'+--}}
        {{--                '</div>';--}}
        {{--            // The user is allowed to check items out, but the item is not deployable--}}
        {{--        } else if (((row.user_can_checkout == false)) && (row.available_actions.checkout == true) && (!row.assigned_to)) {--}}
        {{--            return '<span  data-tooltip="true" title="{{ trans('admin/hardware/general.undeployable_tooltip') }}"><a class="btn btn-sm bg-maroon disabled">{{ trans('general.checkout') }}</a></span>';--}}
        {{--            // The user is allowed to check items in--}}
        {{--        } else if (row.available_actions.checkin == true)  {--}}
        {{--            if (row.assigned_to) {--}}
        {{--                return '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '/checkin" class="btn btn-sm bg-purple" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">{{ trans('general.checkin') }}</a>';--}}
        {{--            } else if (row.assigned_pivot_id) {--}}
        {{--                return '<a href="{{ config('app.url') }}/' + destination + '/' + row.assigned_pivot_id + '/checkin" class="btn btn-sm bg-purple" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">{{ trans('general.checkin') }}</a>';--}}
        {{--            }--}}
        {{--        }--}}
        {{--}--}}

        function hardwareCustomInOutFormatter(value,row) {
            const destination = "hardware";

            if ((row.available_actions.review == true) && (row.user_can_review == true)) {
                return '<button type="button" class="btn btn-primary btn-sm review" data-tooltip="true" title="Проверка">Проверить</button>';
            }

            // The user is allowed to check items out, AND the item is deployable
                if ((row.available_actions.checkout == true) && (row.user_can_checkout == true) && ((!row.asset_id) && (!row.assigned_to))) {

                    return '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '/checkout" class="btn btn-sm bg-maroon" data-tooltip="true" title="{{ trans('general.checkout_tooltip') }}">{{ trans('general.checkout') }}</a>';

                    // The user is allowed to check items out, but the item is not able to be checked out
                } else if (((row.user_can_checkout == false)) && (row.available_actions.checkout == true) && (!row.assigned_to)) {

                    // We use slightly different language for assets versus other things, since they are the only
                    // item that has a status label
                    if (destination =='hardware') {
                        return '<span  data-tooltip="true" title="{{ trans('admin/hardware/general.undeployable_tooltip') }}"><a class="btn btn-sm bg-maroon disabled">{{ trans('general.checkout') }}</a></span>';
                    } else {
                        return '<span  data-tooltip="true" title="{{ trans('general.undeployable_tooltip') }}"><a class="btn btn-sm bg-maroon disabled">{{ trans('general.checkout') }}</a></span>';
                    }

                    // The user is allowed to check items in
                } else if (row.available_actions.checkin == true)  {
                    if (row.assigned_to) {
                        return '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '/checkin" class="btn btn-sm bg-purple" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">{{ trans('general.checkin') }}</a>';
                    } else if (row.assigned_pivot_id) {
                        return '<a href="{{ config('app.url') }}/' + destination + '/' + row.assigned_pivot_id + '/checkin" class="btn btn-sm bg-purple" data-tooltip="true" title="{{ trans('general.checkin_tooltip') }}">{{ trans('general.checkin') }}</a>';
                    }

                }
        }


        function consumablesCustomInOutFormatter(value,row) {
            var destination = "consumables";
            // The user is allowed to check items out, AND the item is deployable
            if ((row.available_actions.checkout == true) && (row.user_can_checkout == true) && ((!row.asset_id) && (!row.assigned_to))) {
                return '<div class="btn-group" style="min-width:180px">' +
                    '<a href="{{ url('/') }}/' + destination + '/' + row.id + '/checkout" class="btn btn-sm bg-maroon" data-toggle="tooltip" title="{{ trans('general.checkout_tooltip') }}">{{ trans('general.checkout') }}</a>'+
                    '</div>';

                // The user is allowed to check items out, but the item is not deployable
            } else if (((row.user_can_checkout == false)) && (row.available_actions.checkout == true) && (!row.assigned_to)) {
                return '<div  data-toggle="tooltip" title="This item has a status label that is undeployable and cannot be checked out at this time."><a class="btn btn-sm bg-maroon disabled">{{ trans('general.checkout') }}</a></div>';

                // The user is allowed to check items in
            } else if (row.available_actions.checkin == true)  {
                if (row.assigned_to) {
                    return '<a href="{{ url('/') }}/' + destination + '/' + row.id + '/checkin" class="btn btn-sm bg-purple" data-toggle="tooltip" title="Check this item in so it is available for re-imaging, re-issue, etc.">{{ trans('general.checkin') }}</a>';
                } else if (row.assigned_pivot_id) {
                    return '<a href="{{ url('/') }}/' + destination + '/' + row.assigned_pivot_id + '/checkin" class="btn btn-sm bg-purple" data-toggle="tooltip" title="Check this item in so it is available for re-imaging, re-issue, etc.">{{ trans('general.checkin') }}</a>';
                }

            }
        }

        // This just prints out the item type in the activity report
        function quantityItemFormatter(value, row) {
            if ((row) && (row.type)) {
                switch (row.type) {
                    case "purchase":
                        return "<span class='text-success'  style='font-size: 130%; font-weight: bold'> +" + value + "</span>";
                    case "issued":
                        return "<span class='text-danger' style='font-size: 130%; font-weight: bold'> -" + value + "</span>";
                    case "converted":
                        return "<span class='text-success' style='font-size: 130%; font-weight: bold'> +" + value + "</span>";
                    case "sold":
                        return "<span class='text-danger' style='font-size: 130%; font-weight: bold'> -" + value + "</span>";
                    case "manually":
                        return "<span class='text-success' style='font-size: 130%; font-weight: bold'> +" + value + "</span>";
                    case "collected":
                        return "<span class='text-success' style='font-size: 130%; font-weight: bold'> +" + value + "</span>";
                }
            }
        }

        function inventoryStatusFormatter(value, row) {
            if ((row.status)) {
                $label = "label-default";
                switch (row.status) {
                    case 'START':
                        $label = "label-info";
                        break;
                    case 'FINISH_OK':
                        $label = "label-success";
                        break;
                    case 'FINISH_BAD':
                        $label = "label-danger";
                        break;
                }
                return '<span class="label ' + $label + '">' + row.status_text + '</span>';
            }

        }

        function purchaseStatusFormatter(value, row) {
            if (value) {
                switch (value) {
                    case "inventory":
                        return '<span class="label label-warning">В процессе инвентаризации</span>';
                        break;
                    case "in_payment":
                        return '<span class="label label-primary">В оплате</span>';
                        break;
                    case "review":
                        return '<span class="label label-warning">В процессе проверки</span>';
                        break;
                    case "finished":
                        return '<span class="label label-success">Завершено</span>';
                        break;
                    case "rejected":
                        return '<span class="label label-danger">Отклонено</span>';
                        break;
                    case "paid":
                        return '<span class="label label-success">Оплачено</span>';
                        break;
                    case "inprogress":
                        return '<span class="label label-primary">На согласовании</span>';
                        break;
                }
            } else {
                return 'error';
            }
        }

        function inventoryCountFormatter(value, row) {
            if (row.total >= 0 && row.checked >= 0) {
                var destination = 'inventories';
                if (row.total == row.checked) {
                    return '<a href="{{ url('/') }}/' + destination + '/' + row.id + '" style="color: green"> ' + row.checked + '/' + row.total + '</a>';
                } else {
                    return '<a href="{{ url('/') }}/' + destination + '/' + row.id + '"> ' + row.checked + '/' + row.total + '</a>';
                }
            }

        }

        function inventorySuccessfullyFormatter(value, row) {
            if (row.total >= 0 && row.successfully >= 0) {
                var destination = 'inventories';
                if (row.total == row.successfully) {
                    return '<a href="{{ url('/') }}/' + destination + '/' + row.id + '" style="color: green"> ' + row.successfully + '/' + row.total + '</a>';
                } else {
                    return '<a href="{{ url('/') }}/' + destination + '/' + row.id + '"> ' + row.successfully + '/' + row.total + '</a>';
                }
            }

        }

        function inventoriesResultFormatter(value, row) {
            if (row.total >= 0 && row.checked >= 0) {
                if (row.total == row.checked) {
                    return '<a href="#" style="color: green"> ' + row.checked + '/' + row.total + '</a>';
                } else {
                    return '<a href="#"> ' + row.checked + '/' + row.total + '</a>';
                }
            }
        }

        function photoDisplayFormatter(value,row) {
            if (value) {
                return '<a href="' + value + '" data-title="'+ row.tag +'" data-toggle="lightbox" data-gallery="inv-gallery" data-lightbox="inventory"><i class="fa fa-camera fa-lg" aria-hidden="true"></i></a>';
            }
        }

        function statusInventoryItemFormatter(value) {
            if (value) {
                return '<span class="label label-default" style="background-color:' + value.color + '; color:white">' + value.name + '</span>';
            }
        }

        function bitrixIdLocationFormatter(value, row) {
            if (value) {
                return '<a href="https://bitrix.legis-s.ru/crm/object/details/' + value + '/"   target="_blank" >' + value + '</a>';
            }
        }

        function bitrixNewIdLocationFormatter(value, row) {
            if (value) {
                return '<a href="https://bitrix.legis-s.ru/crm/type/1032/details/' + value + '/"   target="_blank" >' + value + '</a>';
            }
        }

        function fileFormatter(value) {
            if (value) {
                return '<a href="' + value + '" class="btn btn-default btn-sm" target="_blank"><i class="fas fa-download"> Скачать</i></a>'
            }
        }

        function assetsCountFormatter(value, row) {
            if (row.assets_count_ok > 0) {
                return row.assets_count_ok + "/" + value;
            } else {
                return value;
            }
        }

        function consumablesCountFormatter(value, row) {
            if (row.consumables_count_real > 0) {
                return row.consumables_count_real + "/" + value;
            } else {
                return value;
            }
        }

        function lifetimeFormatter(value, row) {
            if (row.model && row.model.lifetime) {
                return row.model.lifetime
            } else if (row.category && row.category.lifetime) {

            } else {
                return "";
            }
        }

        function qualityFormatter(value, row) {
            switch (value) {
                case 1:
                    return '<i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i>';
                    break;
                case 2:
                    return '<i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i>';
                    break;
                case 3:
                    return '<i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i>';
                    break;
                case 4:
                    return '<i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i>';
                    break;
                case 5:
                    return '<i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i>';
                    break;
                default:
                    return '<i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i>';
            }
        }

        function bitrixIdFormatter(value, row) {
            if (value) {
                return "<a href='https://bitrix.legis-s.ru/services/lists/52/element/0/" + value + "/?list_section_id=' target='_blank'>" + value + "</a>";
            } else {
                if (row.user) {
                        return '<button type="button"  class="btn btn-default  btn-sm resend">Отправить заново</button>'
                } else {
                    return ' ';
                }
            }
        }

        function bitrixTaskIdFormatter(value, row) {
            if (value > 0) {
                return "<a href='https://bitrix.legis-s.ru/company/personal/user/1/tasks/task/view/" + value + "/' target='_blank'>" + value + "</a>";
            } else {
                return ' ';
            }
        }

        function priceFormatter(value, row) {
            if (row.currency && value) {
                return "<span style='font-size: 120%; font-weight: bold;' class='text-primary'>" + value + " " + row.currency + "</span>";
            } else {
            }
        }

        function bitrixIdContractFormatter(value, row) {
            if (value) {
                return "<a href='https://bitrix.legis-s.ru/crm/contract/details/" + value + "/' target='_blank'>" + value + "</a>";
            }
        }
        function bitrixIdDealFormatter(value, row) {
            if (value) {
                return "<a href='https://bitrix.legis-s.ru/crm/deal/details/" + value + "/' target='_blank'>" + value + "</a>";
            }
        }

        function bulkListRemoveFormatter(value, row) {
            return "<button type='button' class='btn btn-danger  btn-sm bulk-clear'>Убрать</button>"
        }

        function consumablesReturnFormatter(value, row) {
            if (row.can_return == true && row.quantity != 0) {
                return '<button class="btn btn-sm bg-maroon return" data-tooltip="true" title="Вернуть">Вернуть</button>';
            } else {
                return '';
            }
        }
        function mdmStatusCodeFormatter(value, row) {
            switch (value) {
                case "red":
                    return '<i class="fas fa-circle text-danger"></i>'
                case "yellow":
                    return '<i class="fas fa-circle text-warning"></i>'
                case "green":
                    return '<i class="fas fa-circle text-success"></i>'
                default:
                    return value
            }
        }

        function mdmDistanceFormatter(value, row) {
            if (value){
                if (value>1000){
                    var valuekm = value/1000;
                    return "<span class='text-danger'>"+valuekm.toFixed(1) + " км</span>";
                }else{
                    return value+ " м";
                }
            }else{
                return "";
            }
        }

        function yandexMapLinkFormatter(value, row) {
            if (value){
                var cord_array =  value.split(",");
                return "<a href='https://yandex.ru/maps/?pt="+cord_array[1].trim()+","+cord_array[0].trim()+"&z=18&l=map' target='_blank'>"+value+"</a>";
            }else{
                return "";
            }
        }

        function timeAgoFormatter(value, row) {
            if (value){
                format(value, 'ru');
            }else{
                return "";
            }
        }

        function anyDeskLinkFormatter(value, row) {
            if (value){
                return "<a href='anydesk:"+value.split(' ').join('')+"' >"+value+"</a>";
            }else{
                return "";
            }
        }


        /**
         * END CUSTOM
         */

    window.operateEvents = {
        'click .print_label': function (e, value, row, index) {
            $.ajax('http://localhost:8001/termal_print?text=' + row.asset_tag, {
                success: function (data, textStatus, xhr) {
                    console.log(xhr.status);
                    if (xhr.status === 200) {
                        $.ajax({
                            method: "POST",
                            url: '/api/v1/hardware/' + row.id + '/inventory',
                            headers: {
                                "X-Requested-With": 'XMLHttpRequest',
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function (data) {
                                $(".table").bootstrapTable('refresh');
                            },
                            error: function (data) {
                                console.log(data);
                            }
                        });
                    } else {
                        console.log(data);
                    }
                },
                error: function () {
                    console.log("error");
                }
            });
        },
        'click .inventory': function (e, value, row, index) {
            Swal.fire({
                title: "Изменить тег актива <b>" + row.asset_tag + " </b>",
                // text: 'Do you want to continue',
                icon: 'question',
                input: "text",
                inputLabel: 'Новый тег',
                inputAttributes: {
                    autocapitalize: 'on'
                },
                reverseButtons: true,
                showCancelButton: true,
                confirmButtonText: 'Подтвердить',
                cancelButtonText: 'Отменить',
            }).then((result) => {
                if (result.isConfirmed) {

                    var sendData = {
                        asset_tag: result.value,
                    };
                    console.log("asset_tag: " + result.value);
                    $.ajax({
                        type: 'POST',
                        url: '/api/v1/hardware/' + row.id + '/inventory',
                        headers: {
                            "X-Requested-With": 'XMLHttpRequest',
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        },
                        data: sendData,
                        dataType: 'json',
                        success: function (data) {
                            $(".table").bootstrapTable('refresh');
                        },
                        error: function (data) {
                            console.log(data);
                        }
                    });
                }
            });
        },
        'click .resend': function (e, value, row, index) {
            $.ajax({
                url: '/api/v1/purchases/' + row.id + '/resend',
                {{--url: '{{ route('api.purchases.resend', ['id'=> row.id]) }}',--}}
                method: "POST",
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                success: function () {
                    $(".table").bootstrapTable('refresh');
                }
            });
        },
        'click .review': function (e, value, row, index) {

            console.log(row);
            $.ajax({
                url: '/api/v1/hardware/' + row.id + '/review',
                method: "POST",
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                success: function () {
                    $(".table").bootstrapTable('refresh');
                    $.ajax({
                        type: 'GET',
                        url: "/api/v1/purchases/" + row.purchase_id,
                        headers: {
                            "X-Requested-With": 'XMLHttpRequest',
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function (data) {
                            var status = data.status;
                            var result = "";
                            switch (status) {
                                case "inventory":
                                    result = '<span class="label label-warning">В процессе инвентаризации</span>';
                                    break;
                                case "in_payment":
                                    result = '<span class="label label-primary">В оплате</span>';
                                    break;
                                case "review":
                                    result = '<span class="label label-warning">В процессе проверки</span>';
                                    break;
                                case "finished":
                                    result = '<span class="label label-success">Завершено</span>';
                                    break;
                                case "rejected":
                                    result = '<span class="label label-danger">Отклонено</span>';
                                    break;
                                case "paid":
                                    result = '<span class="label label-success">Оплачено</span>';
                                    break;
                                case "inprogress":
                                    result = '<span class="label label-primary">На согласовании</span>';
                                    break;
                            }
                            $('.status_label').html(result);

                        },
                    });
                }
            });
        },
        'click .return': function (e, value, row, index) {
            Swal.fire({
                title: "Вернуть - " + row.name + " " + row.assigned_to.name,
                // text: 'Do you want to continue',
                icon: 'question',
                input: "range",
                inputLabel: 'Количество',
                inputAttributes: {
                    min: 1,
                    max: row.quantity,
                    step: 1
                },
                inputValue: 1,
                reverseButtons: true,
                showCancelButton: true,
                confirmButtonText: 'Подтвердить',
                cancelButtonText: 'Отменить',
            }).then((result) => {
                if (result.isConfirmed) {

                    var sendData = {
                        quantity: result.value,
                        nds: row.nds,
                        purchase_cost: row.purchase_cost,
                    };
                    $.ajax({
                        type: 'POST',
                        url: "/api/v1/consumableassignments/" + row.id + "/return",
                        headers: {
                            "X-Requested-With": 'XMLHttpRequest',
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        },
                        data: sendData,
                        dataType: 'json',
                        success: function (data) {
                            $(".table").bootstrapTable('refresh');
                        },
                    });
                }
            });
        },
    };

    $(function () {
        $('#bulkEdit').click(function () {
            var selectedIds = $('.snipe-table').bootstrapTable('getSelections');
            $.each(selectedIds, function(key,value) {
                $( "#bulkForm" ).append($('<input type="hidden" name="ids[' + value.id + ']" value="' + value.id + '">' ));
            });

        });
    });

    $(function() {

        // This handles the search box highlighting on both ajax and client-side
        // bootstrap tables
        var searchboxHighlighter = function (event) {

            $('.search-input').each(function (index, element) {

                if ($(element).val() != '') {
                    $(element).addClass('search-highlight');
                    $(element).next().children().addClass('search-highlight');
                } else {
                    $(element).removeClass('search-highlight');
                    $(element).next().children().removeClass('search-highlight');
                }
            });
        };

        $('.search button[name=clearSearch]').click(searchboxHighlighter);
        searchboxHighlighter({ name:'pageload'});
        $('.search-input').keyup(searchboxHighlighter);

        //  This is necessary to make the bootstrap tooltips work inside of the
        // wenzhixin/bootstrap-table formatters
        $('#table').on('post-body.bs.table', function () {
            $('[data-tooltip="true"]').tooltip({
                container: 'body'
            });


        });
    });

</script>
    
@endpush
