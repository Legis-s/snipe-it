@push('css')
    <link rel="stylesheet" href="{{ url(mix('css/dist/bootstrap-table.css')) }}">
@endpush

@push('js')

<script src="{{ url(mix('js/dist/bootstrap-table.js')) }}"></script>
<script src="{{ url('js/locale/bootstrap-table-ru-RU.js') }}"></script>

<script nonce="{{ csrf_token() }}">
    $(function () {

        var locale = '{{ config('app.locale') }}';
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
            export_options['jspdf']= {"orientation": "l"};
            // the following callback method is necessary to prevent XSS vulnerabilities
            // (this is taken from Bootstrap Tables's default wrapper around jQuery Table Export)
            export_options['onCellHtmlData'] = function (cell, rowIndex, colIndex, htmlData) {
                if (cell.is('th')) {
                    return cell.find('.th-inner').text()
                }
                return htmlData
            }
            $(this).bootstrapTable({
            classes: 'table table-responsive table-no-bordered',
            ajaxOptions: {
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            },
            // reorderableColumns: true,
            stickyHeader: true,
            stickyHeaderOffsetLeft: parseInt($('body').css('padding-left'), 10),
            stickyHeaderOffsetRight: parseInt($('body').css('padding-right'), 10),
            locale: locale,
            undefinedText: '',
            iconsPrefix: 'fa',
            cookieStorage: '{{ config('session.bs_table_storage') }}',
            cookie: true,
            cookieExpire: '2y',
            mobileResponsive: true,
            maintainSelected: true,
            trimOnSearch: false,
            showSearchClearButton: true,
            paginationFirstText: "{{ trans('general.first') }}",
            paginationLastText: "{{ trans('general.last') }}",
            paginationPreText: "{{ trans('general.previous') }}",
            paginationNextText: "{{ trans('general.next') }}",
            pageList: ['10','20', '30','50','100','150','200'{!! ((config('app.max_results') > 200) ? ",'500'" : '') !!}{!! ((config('app.max_results') > 500) ? ",'".config('app.max_results')."'" : '') !!}],
            pageSize: {{  (($snipeSettings->per_page!='') && ($snipeSettings->per_page > 0)) ? $snipeSettings->per_page : 20 }},
            paginationVAlign: 'both',
            queryParams: function (params) {
                var newParams = {};
                for(var i in params) {
                    if(!keyBlocked(i)) { // only send the field if it's not in blockedFields
                        newParams[i] = params[i];
                    }
                }
                return newParams;
            },
            formatLoadingMessage: function () {
                return '<h2><i class="fas fa-spinner fa-spin" aria-hidden="true"></i> {{ trans('general.loading') }} </h4>';
            },
            icons: {
                advancedSearchIcon: 'fas fa-search-plus',
                paginationSwitchDown: 'fa-caret-square-o-down',
                paginationSwitchUp: 'fa-caret-square-o-up',
                fullscreen: 'fa-expand',
                columns: 'fa-columns',
                refresh: 'fas fa-sync-alt',
                export: 'fa-download',
                clearSearch: 'fa-times'
            },
                exportOptions: export_options,

            exportTypes: ['xlsx', 'excel', 'csv', 'pdf','json', 'xml', 'txt', 'sql', 'doc' ],
            onLoadSuccess: function () {
                $('[data-tooltip="true"]').tooltip(); // Needed to attach tooltips after ajax call
            }

            });

        });
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


    // Handle whether or not the edit button should be disabled
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
        name = '';
        direction = '';
        cookies = document.cookie.split(";");
        for(i in cookies) {
            cookiedef = cookies[i].split("=", 2);
            cookiedef[0] = cookiedef[0].trim();
            if (cookiedef[0] == table_cookie_segment + ".bs.table.sortOrder") {
                direction = cookiedef[1];
            }
            if (cookiedef[0] == table_cookie_segment + ".bs.table.sortName") {
                name = cookiedef[1];
            }
        }
        if (name && direction) {
            domnode = $($(this).data('bulk-form-id')).get(0);
            if ( domnode && domnode.elements && domnode.elements.sort ) {
                domnode.elements.sort.value = name + " " + direction;
            }
        }
    });

    // If sort order changes, update the sort-order for bulk-actions (for label-generation)
    $('.snipe-table').on('sort.bs.table', function (event, name, order) {
       domnode = $($(this).data('bulk-form-id')).get(0);
       // make safe in case there isn't a bulk-form-id, or it's not found, or has no 'sort' element
       if ( domnode && domnode.elements && domnode.elements.sort ) {
           domnode.elements.sort.value = name + " " + order;
       }
    });


    

    // This only works for model index pages because it uses the row's model ID
    function genericRowLinkFormatter(destination) {
        return function (value,row) {
            if (value) {
                return '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '">' + value + '</a>';
            }
        };
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
                var dpolymorphicItemFormatterest = '';
                if (destination=='fieldsets') {
                    var dpolymorphicItemFormatterest = 'fields/';
                }

                return '<nobr><a href="{{ config('app.url') }}/' + dpolymorphicItemFormatterest + dest + '/' + value.id + '">' + value.name + '</a></span>';
            }
        };
    }

    function hardwareAuditFormatter(value, row) {
        return '<a href="{{ config('app.url') }}/hardware/audit/' + row.id + '/" class="btn btn-sm bg-yellow" data-tooltip="true" title="Audit this item">{{ trans('general.audit') }}</a>';
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

            if (dest =='maintenances') {
                var dest = 'hardware/maintenances';
            }

            if(element_name != '') {
                dest = dest + '/' + row.owner_id + '/' + element_name;
            }

            /**
             *  START CUSTOM
             */
            if ((row.available_actions) && (row.available_actions.print_label === true)) {
                actions += '<span class="actions btn btn-sm btn-primary print_label" data-tooltip="true" title="Напечатать этикетку"><i class="fas fa-barcode" style="color: white" aria-hidden="true"></i><span class="sr-only">Напечатать этикетку</span></span>&nbsp;';
            }


            if ((row.available_actions) && (row.available_actions.inventory === true)) {
                actions += '<span class="actions btn btn-sm btn-primary inventory" data-tooltip="true" title="Инвентаризировать"><i class="fas fa-key" style="color: white" aria-hidden="true"></i><span class="sr-only">Инвентаризировать</span></span>&nbsp;';
            }

            if ((row.available_actions) && (row.available_actions.impersonate === true)) {
                actions += '<a href="{{ config('app.url') }}/impersonate/take/' + row.id + '/" class="actions btn btn-sm btn-danger" data-tooltip="true" title="Зайти под пользовтелем"><i class="fas fa-unlock" aria-hidden="true"></i><span class="sr-only">Зайти под пользовтелем</span></a>&nbsp;';
            }
            /**
             *  END CUSTOM
             */

            if ((row.available_actions) && (row.available_actions.clone === true)) {
                actions += '<a href="{{ config('app.url') }}/' + dest + '/' + row.id + '/clone" class="actions btn btn-sm btn-info" data-tooltip="true" title="{{ trans('general.clone_item') }}"><i class="far fa-clone" aria-hidden="true"></i><span class="sr-only">Clone</span></a>&nbsp;';
            }

            if ((row.available_actions) && (row.available_actions.update === true)) {
                actions += '<a href="{{ config('app.url') }}/' + dest + '/' + row.id + '/edit" class="actions btn btn-sm btn-warning" data-tooltip="true" title="{{ trans('general.update') }}"><i class="fas fa-pencil-alt" aria-hidden="true"></i><span class="sr-only">{{ trans('general.update') }}</span></a>&nbsp;';
            }

            if ((row.available_actions) && (row.available_actions.delete === true)) {

                // use the asset tag if no name is provided
                var name_for_box = row.name
                if (row.name=='') {
                    var name_for_box = row.asset_tag
                }
                if( name_for_box == undefined && row.invoice_number){
                    name_for_box = row.invoice_number
                }

                actions += '<a href="{{ config('app.url') }}/' + dest + '/' + row.id + '" '
                    + ' class="actions btn btn-danger btn-sm delete-asset" data-tooltip="true"  '
                    + ' data-toggle="modal" '
                    + ' data-content="{{ trans('general.sure_to_delete') }} ' + name_for_box + '?" '
                    + ' data-title="{{  trans('general.delete') }}" onClick="return false;">'
                    + '<i class="fas fa-trash" aria-hidden="true"></i><span class="sr-only">{{ trans('general.delete') }}</span></a>&nbsp;';
            } else {
                // Do not show the delete button on things that are already deleted
                if ((row.available_actions) && (row.available_actions.restore != true)) {
                    actions += '<span data-tooltip="true" title="{{ trans('general.cannot_be_deleted') }}"><a class="btn btn-danger btn-sm delete-asset disabled" onClick="return false;"><i class="fas fa-trash"></i></a></span>&nbsp;';
                }

            }


            if ((row.available_actions) && (row.available_actions.restore === true)) {
                actions += '<form style="display: inline;" method="POST" action="{{ config('app.url') }}/' + dest + '/' + row.id + '/restore"> ';
                actions += '@csrf';
                actions += '<button class="btn btn-sm btn-warning" data-tooltip="true" title="{{ trans('general.restore') }}"><i class="fas fa-retweet"></i></button>&nbsp;';
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
                } else if (value.type == 'model') {
                    item_destination = 'models'
                    item_icon = '';
                }else if (value.type == 'purchase') {
                    item_destination = 'purchases'
                    item_icon = 'fas fa-shopping-basket';
                } else if (value.type == 'contract') {
                    item_destination = 'contracts'
                    item_icon = 'fas fa-file';
                } else if (value.type == 'sale') {
                    item_destination = 'sales'
                    item_icon = 'fas fa-usd';
                }

            // display the username if it's checked out to a user, but don't do it if the username's there already
            if (value.username && !value.name.match('\\(') && !value.name.match('\\)')) {
                value.name = value.name + ' (' + value.username + ')';
            }

            return '<nobr><a href="{{ config('app.url') }}/' + item_destination +'/' + value.id + '" data-tooltip="true" title="' + value.type + '"><i class="' + item_icon + ' text-{{ $snipeSettings->skin!='' ? $snipeSettings->skin : 'blue' }} "></i> ' + value.name + '</a></nobr>';

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
            return value.replace(/(?:\r\n|\r|\n)/g, '<br />');;
        }
    }


    // We need a special formatter for license seats, since they don't work exactly the same
    // Checkouts need the license ID, checkins need the specific seat ID

    function licenseSeatInOutFormatter(value, row) {
        // The user is allowed to check the license seat out and it's available
        if ((row.available_actions.checkout == true) && (row.user_can_checkout == true) && ((!row.asset_id) && (!row.assigned_to))) {
            return '<a href="{{ config('app.url') }}/licenses/' + row.license_id + '/checkout/'+row.id+'" class="btn btn-sm bg-maroon" data-tooltip="true" title="{{ trans('general.checkout_tooltip') }}">{{ trans('general.checkout') }}</a>';
        } else {
            return '<a href="{{ config('app.url') }}/licenses/' + row.id + '/checkin" class="btn btn-sm bg-purple" data-tooltip="true" title="Check in this license seat.">{{ trans('general.checkin') }}</a>';
        }

    }

    function genericCheckinCheckoutFormatter(destination) {
        return function (value,row) {

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
                    return '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '/checkin" class="btn btn-sm bg-purple" data-tooltip="true" title="Check this item in so it is available for re-imaging, re-issue, etc.">{{ trans('general.checkin') }}</a>';
                } else if (row.assigned_pivot_id) {
                    return '<a href="{{ config('app.url') }}/' + destination + '/' + row.assigned_pivot_id + '/checkin" class="btn btn-sm bg-purple" data-tooltip="true" title="Check this item in so it is available for re-imaging, re-issue, etc.">{{ trans('general.checkin') }}</a>';
                }

            }

        }


    }


    // This is only used by the requestable assets section
    function assetRequestActionsFormatter (row, value) {
        if (value.assigned_to_self == true){
            return '<button class="btn btn-danger btn-sm disabled" data-tooltip="true" title="Cancel this item request">{{ trans('button.cancel') }}</button>';
        } else if (value.available_actions.cancel == true)  {
            return '<form action="{{ config('app.url') }}/account/request-asset/'+ value.id + '" method="POST">@csrf<button class="btn btn-danger btn-sm" data-tooltip="true" title="Cancel this item request">{{ trans('button.cancel') }}</button></form>';
        } else if (value.available_actions.request == true)  {
            return '<form action="{{ config('app.url') }}/account/request-asset/'+ value.id + '" method="POST">@csrf<button class="btn btn-primary btn-sm" data-tooltip="true" title="Request this item">{{ trans('button.request') }}</button></form>';
        }

    }



        var formatters = [
            'hardware',
            'accessories',
            'consumables',
            'components',
            'locations',
            'users',
            'manufacturers',
            'maintenances',
            'statuslabels',
            'models',
            'licenses',
            'categories',
            'suppliers',
            'departments',
            'companies',
            'depreciations',
            'fieldsets',
            'groups',
            'kits',
            'inventories',
            'purchases',
            'inventorystatuslabels',
            'contracts',
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
                        return (row.custom_fields[field_column_plain].value == 1) ? "<span class='fas fa-check-circle' style='color:green' />" : "<span class='fas fa-times-circle' style='color:red' />";
                    } else if (row.custom_fields[field_column_plain].field_format=='EMAIL') {
                        return '<a href="mailto:' + row.custom_fields[field_column_plain].value + '">' + row.custom_fields[field_column_plain].value + '</a>';
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
                return '<nobr><a href="' + value + '" target="_blank" title="External link to ' + value + '" data-tooltip="true" ><i class="fa fa-external-link"></i> ' + value + '</a></nobr>';
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

                result += extra_pretty_index + ': <del>' + value[index].old + '</del>  <i class="fas fa-long-arrow-alt-right" aria-hidden="true"></i> ' + value[index].new + '<br>'
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


    // Create a linked phone number in the table list
    function phoneFormatter(value) {
        if (value) {
            return  '<a href="tel:' + value + '">' + value + '</a>';
        }
    }


    function deployedLocationFormatter(row, value) {
        if ((row) && (row!=undefined)) {
            return '<a href="{{ config('app.url') }}/locations/' + row.id + '">' + row.name + '</a>';
        } else if (value.rtd_location) {
            return '<a href="{{ config('app.url') }}/locations/' + value.rtd_location.id + '" data-tooltip="true" title="Default Location">' + value.rtd_location.name + '</a>';
        }

    }

    function groupsAdminLinkFormatter(value, row) {
        return '<a href="{{ config('app.url') }}/admin/groups/' + row.id + '">' + value + '</a>';
    }

    function assetTagLinkFormatter(value, row) {
        if ((row.asset) && (row.asset.id)) {
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

    function trueFalseFormatter(value) {
        if ((value) && ((value == 'true') || (value == '1'))) {
            return '<i class="fas fa-check text-success"></i><span class="sr-only">{{ trans('general.true') }}</span>';
        } else {
            return '<i class="fas fa-times text-danger"></i><span class="sr-only">{{ trans('general.false') }}</span>';
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
            return '<a href="mailto:' + value + '">' + value + '</a>';
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

    function employeeNumFormatter(value, row) {

        if ((row) && (row.assigned_to) && ((row.assigned_to.employee_number))) {
            return '<a href="{{ config('app.url') }}/users/' + row.assigned_to.id + '">' + row.assigned_to.employee_number + '</a>';
        }
    }

    function orderNumberObjFilterFormatter(value, row) {
        if (value) {
            return '<a href="{{ config('app.url') }}/hardware/?order_number=' + row.order_number + '">' + row.order_number + '</a>';
        }
    }

    function auditImageFormatter(value){
        if (value){
            return '<a href="' + value.url + '" data-toggle="lightbox" data-type="image"><img src="' + value.url + '" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive"></a>'
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
    function downloadFormatter(value) {
        if (value) {
            return '<a href="' + value + '" target="_blank"><i class="fas fa-download"></i></a>';
        }
    }

    function fileUploadFormatter(value) {
        if ((value) && (value.url) && (value.inlineable)) {
            return '<a href="' + value.url + '" data-toggle="lightbox" data-type="image"><img src="' + value.url + '" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive"></a>';
        } else if ((value) && (value.url)) {
            return '<a href="' + value.url + '" class="btn btn-default"><i class="fas fa-download"></i></a>';
        }
    }


    function fileUploadNameFormatter(value) {
        console.dir(value);
        if ((value) && (value.filename) && (value.url)) {
            return '<a href="' + value.url + '">' + value.filename + '</a>';
        }
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

        function hardwareCustomInOutFormatter(value,row) {
                var destination = "hardware";
                if ((row.available_actions.review == true) && (row.user_can_review == true)) {
                    return '<button type="button" class="btn btn-primary btn-sm review" data-tooltip="true" title="Проверка">Проверить</button>';
                }
                {{--if ((row.available_actions.checkout == true) && (row.user_can_close_sell == true)) {--}}
                {{--    // return '<span class="btn btn-sm bg-maroon closesell" data-tooltip="true" title="Есть закр. док.">Есть закр. док.</span>';--}}
                {{--    return '<div class="btn-group" style="min-width:200px">' +--}}
                {{--        '<span class="btn btn-sm bg-maroon closesell" data-tooltip="true" title="Есть закр. док.">Есть закр. док.</span>'+--}}
                {{--        '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '/checkin" class="btn btn-sm bg-purple" data-toggle="tooltip" title="Check this item in so it is available for re-imaging, re-issue, etc.">{{ trans('general.checkin') }}</a>'+--}}
                {{--        '</div>';--}}
                {{--}--}}
                // The user is allowed to check items out, AND the item is deployable
                if ((row.available_actions.checkout == true) && (row.user_can_checkout == true) && ((!row.asset_id) && (!row.assigned_to))) {
                    return '<div class="btn-group" style="min-width:270px">' +
                        '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '/checkout" class="btn btn-sm bg-maroon" data-tooltip="true" title="{{ trans('general.checkout_tooltip') }}">{{ trans('general.checkout') }}</a>'+
                        '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '/sell" class="btn btn-sm bg-red" data-tooltip="true" title="{{ trans('general.sell_tooltip') }}">{{ trans('general.sell') }}</a>'+
                        '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '/rent" class="btn btn-sm bg-yellow" data-tooltip="true" title="{{ trans('general.rent_tooltip') }}">{{ trans('general.rent') }}</a>'+
                        '</div>';
                    // The user is allowed to check items out, but the item is not deployable
                } else if (((row.user_can_checkout == false)) && (row.available_actions.checkout == true) && (!row.assigned_to)) {
                    return '<span  data-tooltip="true" title="{{ trans('admin/hardware/general.undeployable_tooltip') }}"><a class="btn btn-sm bg-maroon disabled">{{ trans('general.checkout') }}</a></span>';
                    // The user is allowed to check items in
                } else if (row.available_actions.checkin == true)  {
                    if (row.assigned_to) {
                        return '<a href="{{ config('app.url') }}/' + destination + '/' + row.id + '/checkin" class="btn btn-sm bg-purple" data-tooltip="true" title="Check this item in so it is available for re-imaging, re-issue, etc.">{{ trans('general.checkin') }}</a>';
                    } else if (row.assigned_pivot_id) {
                        return '<a href="{{ config('app.url') }}/' + destination + '/' + row.assigned_pivot_id + '/checkin" class="btn btn-sm bg-purple" data-tooltip="true" title="Check this item in so it is available for re-imaging, re-issue, etc.">{{ trans('general.checkin') }}</a>';
                    }
                }
        }

        function consumablesCustomInOutFormatter(value,row) {
            var destination = "consumables";
            // The user is allowed to check items out, AND the item is deployable
            if ((row.available_actions.checkout == true) && (row.user_can_checkout == true) && ((!row.asset_id) && (!row.assigned_to))) {
                return '<div class="btn-group" style="min-width:180px">' +
                    '<a href="{{ url('/') }}/' + destination + '/' + row.id + '/checkout" class="btn btn-sm bg-maroon" data-toggle="tooltip" title="{{ trans('general.checkout_tooltip') }}">{{ trans('general.checkout') }}</a>'+
                    '<a href="{{ url('/') }}/' + destination + '/' + row.id + '/sell" class="btn btn-sm bg-red" data-toggle="tooltip" title="{{ trans('general.sell_tooltip') }}">{{ trans('general.sell') }}</a>'+
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

        function photosFormatter(value, row) {
            var result = "";
            if (value) {
                if (value.length > 0) {
                    result = ' <div class="aniimated-thumbnials" >';
                    value.forEach((photo) => {
                        if (!photo.comment) {
                            photo.comment = "";
                        }
                        result += '<a href="' + photo.path + '" data-lightbox="image-1" data-title="' + photo.comment + '"><img width="200" class="img-thumbnail"  data-toggle="tooltip" data-placement="bottom" title="' + photo.comment + '" src="' + photo.path + '" /></a>';
                    });
                    result += '  </div>';
                    return result;
                } else {
                    return '';
                }
            } else {
                return '';
            }

        }

        // Create a linked phone number in the table list
        function massOperationsFormatter(value, row) {
            if (value) {
                return '<a href="/massoperations/' + row.id + '">' + value + '</a>';
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

        function photoDisplayFormatter(value) {
            if (value) {
                // return  '<img height="50" src="'+value+'" class="img-responsive img-thumbnail">';
                return '<a href="' + value + '" data-lightbox="roadtrip"><i class="fa fa-camera fa-lg" aria-hidden="true"></i></a>';
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
                    {{--return '<a href="{{ url('/') }}/api/v1/purchases/' + row.id + '/resend"> Отправить заново</a>';--}}
                        return '<button type="button"  class="btn btn-default  btn-sm resend">Отправить заново</button>'
                } else {
                    return ' ';
                }
            }
        }

        function bitrixTaskIdFormatter(value, row) {
            if (value) {
                return "<a href='https://bitrix.legis-s.ru/company/personal/user/" + row.user.bitrix_id + "/tasks/task/view/" + value + "/' target='_blank'>" + value + "</a>";
            } else {
                return ' ';
            }
        }

        function priceFormatter(value, row) {
            if (row.currency && row.final_price) {
                return "<span style='font-size: 120%; font-weight: bold;' class='text-primary'>" + row.final_price + " " + row.currency + "</span>";
            } else {
            }
        }

        function deliveryСostFormatter(value, row) {
            if (row.currency && row.delivery_cost) {
                return "<span style='font-size: 120%; font-weight: bold;' class='text-primary'>" + row.delivery_cost + " " + row.currency + "</span>";
            } else {
            }
        }

        function bitrixIdContractFormatter(value, row) {
            if (value) { //https://bitrix.legis-s.ru/crm/contract/details/4537/
                return "<a href='https://bitrix.legis-s.ru/crm/contract/details/" + value + "/' target='_blank'>" + value + "</a>";
            }
        }

        function bulkListRemoveFormatter(value, row) {
            return "<button type='button' class='btn btn-danger  btn-sm bulk-clear'>Убрать</button>"
        }


        function fileUploadFormatter(value) {
            if ((value) && (value.url) && (value.inlineable)) {
                return '<a href="' + value.url + '" data-toggle="lightbox" data-type="image"><img src="' + value.url + '" style="max-height: {{ $snipeSettings->thumbnail_max_h }}px; width: auto;" class="img-responsive"></a>';
            } else if ((value) && (value.url)) {
                return '<a href="' + value.url + '" class="btn btn-default"><i class="fas fa-download"></i></a>';
            }
        }

        function consumablesReturnFormatter(value, row) {
            if (row.can_return == true && row.quantity != 0) {
                // if (row.can_close_documents == true) {
                //     return '<button class="btn btn-sm bg-maroon return" data-tooltip="true" title="Вернуть">Вернуть</button><br><button class="btn btn-sm bg-maroon close_documents" data-tooltip="true" title="Вернуть">Получены закр. док.</button>';
                // }
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

        $(function () {
            $('#bulkEdit').click(function () {
                var selectedIds = $('.snipe-table').bootstrapTable('getSelections');
                $.each(selectedIds, function(key,value) {
                    $( "#bulkForm" ).append($('<input type="hidden" name="ids[' + value.id + ']" value="' + value.id + '">' ));
                });
            });
        });

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
                                }
                            });
                        } else {
                            console.log(data) ;
                        }
                    },
                    error: function () {
                        console.log("error");
                    }
                });
            },
            {{--'click .closesell': function (e, value, row, index) {--}}
            {{--    $.ajax({--}}
            {{--        url: '/api/v1/hardware/' + row.id + '/closesell',--}}
            {{--        --}}{{--url: '{{ route('api.purchases.resend', ['id'=> row.id]) }}',--}}
            {{--        method: "POST",--}}
            {{--        headers: {--}}
            {{--            "X-Requested-With": 'XMLHttpRequest',--}}
            {{--            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')--}}
            {{--        },--}}
            {{--        success: function () {--}}
            {{--            $(".table").bootstrapTable('refresh');--}}
            {{--        }--}}
            {{--    });--}}
            {{--},--}}
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
            'click .close_documents': function (e, value, row, index) {
                if (row.contract) {
                    $.ajax({
                        type: 'POST',
                        url: "/api/v1/consumableassignments/" + row.id + "/close_documents",
                        headers: {
                            "X-Requested-With": 'XMLHttpRequest',
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        success: function (data) {
                            $(".table").bootstrapTable('refresh',{"silent":true});
                        },
                    });
                } else {
                    Swal.fire({
                        title: "Закрывающие документы - " + row.name + " " + row.assigned_to.name,
                        // text: 'Do you want to continue',
                        icon: 'question',
                        html:
                            '<select class="js-data-ajax" data-endpoint="contracts" data-placeholder="Выберите договор" name="assigned_contract" style="width: 100%" id="assigned_contract_contract_select" aria-label="assigned_contract">' +
                            '<option value=""  role="option">Выберите договор</option>' +
                            '</select>',
                        reverseButtons: true,
                        showCancelButton: true,
                        confirmButtonText: 'Подтвердить',
                        cancelButtonText: 'Отменить',
                        preConfirm: () => {
                            return [
                                $('#assigned_contract_contract_select').val(),
                            ]
                        },
                        didOpen: (toast) => {
                            // Crazy select2 rich dropdowns with images!
                            $('.js-data-ajax').each(function (i, item) {
                                console.log("js-data-ajax")
                                var link = $(item);
                                var endpoint = link.data("endpoint");
                                var select = link.data("select");
                                link.select2({

                                    /**
                                     * Adds an empty placeholder, allowing every select2 instance to be cleared.
                                     * This placeholder can be overridden with the "data-placeholder" attribute.
                                     */
                                    placeholder: '',
                                    allowClear: true,

                                    ajax: {

                                        // the baseUrl includes a trailing slash
                                        url: baseUrl + 'api/v1/' + endpoint + '/selectlist',
                                        dataType: 'json',
                                        delay: 250,
                                        headers: {
                                            "X-Requested-With": 'XMLHttpRequest',
                                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                                        },
                                        data: function (params) {
                                            var data = {
                                                search: params.term,
                                                page: params.page || 1,
                                                assetStatusType: link.data("asset-status-type"),
                                            };
                                            return data;
                                        },
                                        processResults: function (data, params) {

                                            params.page = params.page || 1;

                                            var answer = {
                                                results: data.items,
                                                pagination: {
                                                    more: "true" //(params.page  < data.page_count)
                                                }
                                            };

                                            return answer;
                                        },
                                        cache: true
                                    },
                                    escapeMarkup: function (markup) {
                                        return markup;
                                    }, // let our custom formatter work
                                    templateResult: formatDatalist,
                                    templateSelection: formatDataSelection
                                });

                            });
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            var contract_id = result.value[0];
                            var sendData = {
                                contract_id: contract_id,
                            };

                            $.ajax({
                                type: 'POST',
                                url: "/api/v1/consumableassignments/" + row.id + "/close_documents",
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
                }
            }
        }

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