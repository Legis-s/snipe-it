@extends('layouts/default')

@php
    $purchaseAssets = $purchase->assets;
    $purchaseConsumables = $purchase->consumables;
    $consumableRows = json_decode($purchase->consumables_json ?: '[]', true);
    $consumableRows = is_array($consumableRows) ? $consumableRows : [];
    $hasConsumableRows = count($consumableRows) > 0;
    $purchaseStatuses = [
        'inventory' => ['warning', trans('general.purchase_statuses.inventory')],
        'in_payment' => ['primary', trans('general.purchase_statuses.in_payment')],
        'review' => ['warning', trans('general.purchase_statuses.review')],
        'finished' => ['success', trans('general.purchase_statuses.finished')],
        'rejected' => ['danger', trans('general.purchase_statuses.rejected')],
        'paid' => ['success', trans('general.purchase_statuses.paid')],
        'inprogress' => ['primary', trans('general.purchase_statuses.inprogress')],
    ];
    $currentStatus = $purchaseStatuses[$purchase->status] ?? ['default', $purchase->status];
@endphp

{{-- Page title --}}
@section('title')

    {{ trans('general.purchase') }}:
    {{ $purchase->invoice_number }}

    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            @if ($purchaseAssets->isNotEmpty())
                <x-box>
                    <div class="box-header with-border">
                        <div class="box-heading">
                            <h2 class="box-title">{{ trans('general.assets') }}</h2>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">

                                @include('partials.asset-bulk-actions')

                                <table
                                        data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                                        data-cookie-id-table="assetsPurchaseTable"
                                        data-id-table="assetsPurchaseTable"
                                        data-search-text="{{ e(Session::get('search')) }}"
                                        data-side-pagination="server"
                                        data-show-footer="true"
                                        data-sort-order="asc"
                                        data-sort-name="name"
                                        data-toolbar="#assetsBulkEditToolbar"
                                        data-bulk-button-id="#bulkAssetEditButton"
                                        data-bulk-form-id="#assetsBulkForm"
                                        id="assetsPurchaseTable"
                                        class="table table-striped snipe-table"
                                        data-url="{{ route('api.assets.index',['purchase_id' => $purchase->id]) }}"
                                        data-export-options='{
                    "fileName": "export_purchase_{{ (Request::has('status')) ? '-'.str_slug(Request::get('status')) : '' }}-assets-{{ date('Y-m-d') }}",
                    "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                    }'>
                                </table>
                            </div>
                        </div>
                    </div><!-- /.box-body -->
                </x-box>
            @endif
            @if ($hasConsumableRows && $purchase->status !== 'paid')
                <div class="box">
                    <div class="box-header with-border">
                        <div class="box-heading">
                            <h2 class="box-title">{{ trans('general.unaccepted_consumables') }}</h2>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table
                                        id="table_consumables"
                                        class="table table-striped snipe-table">
                                    @if($old)
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ trans('general.item_name') }}</th>
                                                <th>{{ trans('general.manufacturer') }}</th>
                                                <th>{{ trans('general.category') }}</th>
                                                <th>{{ trans('general.model_no') }}</th>
                                                <th>{{ trans('general.purchase_cost') }}</th>
                                                <th>{{ trans('general.nds') }}</th>
                                                <th>{{ trans('general.quantity') }}</th>
                                            </tr>
                                        </thead>
                                    @else
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>{{ trans('general.item_name') }}</th>
                                                <th>{{ trans('general.model_no') }}</th>
                                                <th>{{ trans('general.purchase_cost') }}</th>
                                                <th>{{ trans('general.nds') }}</th>
                                                <th>{{ trans('general.quantity') }}</th>
                                                <th>{{ trans('general.reviewed') }}</th>
                                                @can('review')
                                                    <th>{{ trans('button.actions') }}</th>
                                                @endcan
                                            </tr>
                                        </thead>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div><!-- /.box-body -->
                </div> <!--/.box-->
                <div class="box">
                    <div class="box-header with-border">
                        <div class="box-heading">
                            <h2 class="box-title">{{ trans('general.accepted_consumables') }}</h2>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table
                                        data-columns="{{ \App\Presenters\ConsumableAssignmentPresenter::dataTableLayout() }}"
                                        data-cookie-id-table="consumableAssignmentTable"
                                        data-id-table="consumableAssignmentTable"
                                        data-side-pagination="server"
                                        data-footer-style="footerStyle"
                                        data-toolbar="#toolbar"
                                        id="consumableAssignmentTable"
                                        class="table table-striped snipe-table"
                                        data-url="{{ route('api.consumableassignments.index', ['purchase_id' => $purchase->id]) }}"
                                        data-export-options='{
                "fileName": "export-consumables-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
                                </table>
                            </div>
                        </div>
                    </div><!-- /.box-body -->
                </div> <!--/.box-->
            @endif
        </x-page-column>
        <x-page-column class="col-md-3">
            <x-box>
                <div class="box-header with-border">
                    <div class="box-heading">
                        <h2 class="box-title">{{ trans('general.information') }}</h2>
                    </div>
                </div>
                <div class="box-body">
                    @if ($purchase->status)
                        <div class="row">
                            <div class="col-md-4">
                                <strong>
                                    {{ trans('general.status') }}
                                </strong>
                            </div>
                            <div class="col-md-8 status_label">
                                <span class="label label-{{ $currentStatus[0] }}">{{ $currentStatus[1] }}</span>
                            </div>
                        </div>
                    @endif
                    @if ($purchase->invoice_number)
                        <div class="row">
                            <div class="col-md-4">
                                <strong>
                                    {{ trans('general.item_name') }}
                                </strong>
                            </div>
                            <div class="col-md-8">
                                {{ $purchase->invoice_number }}
                            </div>
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-4">
                            <strong>{{ trans('general.assets_count') }}</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $purchaseAssets->count() }}
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <strong>{{ trans('general.consumables_count') }}</strong>
                        </div>
                        <div class="col-md-8">
                            {{ $purchaseConsumables->count() }}
                        </div>
                    </div>
                    @if ($purchase->invoice_file)
                        <div class="row">
                            <div class="col-md-4">
                                <strong>
                                    {{ trans('general.invoice_file') }}
                                </strong>
                            </div>
                            <div class="col-md-8">
                                <a href="{{ $purchase->getInvoiceFile() }}">{{ trans('general.download_file') }}</a>
                            </div>
                        </div>
                    @endif
                    @if ($purchase->bitrix_id)
                        <div class="row">
                            <div class="col-md-4">
                                <strong>
                                    {{ trans('general.bitrix_id') }}
                                </strong>
                            </div>
                            <div class="col-md-8">
                                <a href='https://bitrix.legis-s.ru/services/lists/52/element/0/{{ $purchase->bitrix_id }}/?list_section_id='>{{ $purchase->bitrix_id }}</a>
                            </div>
                        </div>
                    @endif
                    @if ($purchase->final_price)
                        <div class="row">
                            <div class="col-md-4">
                                <strong>
                                    {{ trans('admin/hardware/form.cost') }}
                                </strong>
                            </div>
                            <div class="col-md-8">
                                {{ $purchase->final_price }}
                            </div>
                        </div>
                    @endif
                    @if ($purchase->delivery_cost)
                        <div class="row">
                            <div class="col-md-4">
                                <strong>
                                    {{ trans('general.delivery_cost') }}
                                </strong>
                            </div>
                            <div class="col-md-8">
                                {{ $purchase->delivery_cost }}
                            </div>
                        </div>
                    @endif
                    @if ($purchase->supplier)
                        <div class="row">
                            <div class="col-md-4">
                                <strong>
                                    {{ trans('general.supplier') }}
                                </strong>
                            </div>
                            <div class="col-md-8">
                                @can ('superuser')
                                    <a href="{{ route('suppliers.show', $purchase->supplier_id) }}">
                                        {{ $purchase->supplier->name }}
                                    </a>
                                @else
                                    {{ $purchase->supplier->name }}
                                @endcan
                            </div>
                        </div>
                    @endif
                    @if ($purchase->paid)
                        <div class="row">
                            <div class="col-md-4">
                                <strong>
                                    {{ trans('general.purchase_statuses.paid') }}
                                </strong>
                            </div>
                            <div class="col-md-8">
                                {{ $purchase->paid }}
                            </div>
                        </div>
                    @endif
                    @if ($purchase->comment)
                        <div class="row">
                            <div class="col-md-4">
                                <strong>
                                    {{ trans('general.comment') }}
                                </strong>
                            </div>
                            <div class="col-md-8">
                                {{ $purchase->comment }}
                            </div>
                        </div>
                    @endif
                    @if ($purchase->bitrix_task_id)
                        <div class="row">
                            <div class="col-md-4">
                                <strong>
                                    {{ trans('general.task') }}
                                </strong>
                            </div>
                            <div class="col-md-8">
                                <a href="https://bitrix.legis-s.ru/company/personal/user/290/tasks/task/view/{{ $purchase->bitrix_task_id }}/">{{ $purchase->bitrix_task_id }}</a>
                            </div>
                        </div>
                    @endif
                    @if($old)
                        @can('review')
                            @if ($hasConsumableRows && $purchaseConsumables->isEmpty())
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button" class="btn btn-primary" id="check_consumables">
                                            {{ trans('general.accept_consumables') }}
                                        </button>
                                    </div>
                                </div>
                            @endif
                        @endcan
                    @endif
                </div><!-- /.box-body -->
                @can('checkout', \App\Models\Asset::class)
                    @if ($purchase->status == "finished")
                        @if ($purchaseAssets->isNotEmpty())
                            <div class="col-md-12 hidden-print" style="padding-top: 5px;">
                                <a href="{{ route('hardware.bulkcheckout.show', ['purchase_id' => $purchase->id]) }}"
                                   style="width:100%"
                                   class="btn btn-sm bg-maroon btn-social btn-block hidden-print">
                                    <x-icon type="checkout"/>
                                    {{ trans('general.bulk_checkout_assets') }}
                                </a>
                            </div>
                        @endif
                    @endif
                    @if ($hasConsumableRows)
                        <div class="col-md-12 hidden-print" style="padding-top: 5px;">
                            <a href="{{ route('consumables.bulkcheckout.show', ['purchase_id' => $purchase->id]) }}"
                               style="width:100%"
                               class="btn btn-sm btn-primary btn-block btn-social hidden-print">
                                <x-icon type="checkout"/>
                                {{ trans('general.bulk_checkout_consumables') }}
                            </a>
                        </div>
                    @endif
                @endcan
            </x-box>
        </x-page-column>
    </x-container>
@stop

@include ('partials.bootstrap-table')

@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        $(function () {
            const $consumablesTable = $('#table_consumables');
            const $acceptedConsumablesTable = $('#consumableAssignmentTable');
            const $checkConsumables = $('#check_consumables');
            const canReviewConsumables = {{ Illuminate\Support\Js::from(auth()->user()->can('review')) }};
            const isLegacyPurchase = {{ Illuminate\Support\Js::from((bool) $old) }};
            const purchaseId = {{ Illuminate\Support\Js::from($purchase->id) }};
            const endpoints = {
                check: {{ Illuminate\Support\Js::from(route('api.purchases.consumables_check', $purchase->id)) }},
                line: {{ Illuminate\Support\Js::from(route('api.purchases.consumables_line', $purchase->id)) }},
                purchase: {{ Illuminate\Support\Js::from(route('api.purchases.show', $purchase->id)) }},
                review: {{ Illuminate\Support\Js::from(url('/api/v1/consumables')) }},
                consumables: {{ Illuminate\Support\Js::from(url('/consumables')) }}
            };
            const requestHeaders = {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            };
            const statusLabels = {{ Illuminate\Support\Js::from($purchaseStatuses) }};
            const labels = {
                error: {{ Illuminate\Support\Js::from(trans('general.error')) }},
                unableSaveConsumable: {{ Illuminate\Support\Js::from(trans('general.unable_save_consumable')) }},
                unableUpdatePurchaseStatus: {{ Illuminate\Support\Js::from(trans('general.unable_update_purchase_status')) }},
                purchaseCost: {{ Illuminate\Support\Js::from(trans('general.purchase_cost')) }},
                nds: {{ Illuminate\Support\Js::from(trans('general.nds')) }},
                quantity: {{ Illuminate\Support\Js::from(trans('general.quantity')) }},
                invalidPurchaseCost: {{ Illuminate\Support\Js::from(trans('general.purchase_cost_invalid')) }},
                invalidNds: {{ Illuminate\Support\Js::from(trans('general.invalid_nds')) }},
                quantityBelowReviewed: {{ Illuminate\Support\Js::from(trans('general.quantity_below_reviewed')) }},
                acceptNamedItem: {{ Illuminate\Support\Js::from(trans('general.accept_named_item')) }},
                editNamedItem: {{ Illuminate\Support\Js::from(trans('general.edit_named_item')) }},
                deleteNamedItem: {{ Illuminate\Support\Js::from(trans('general.delete_named_item')) }},
                unableReviewConsumable: {{ Illuminate\Support\Js::from(trans('general.unable_review_consumable')) }},
                removePurchaseLineConfirmation: {{ Illuminate\Support\Js::from(trans('general.remove_purchase_line_confirmation')) }},
                unableReviewConsumables: {{ Illuminate\Support\Js::from(trans('general.unable_review_consumables')) }},
                confirm: {{ Illuminate\Support\Js::from(trans('general.confirm')) }},
                cancel: {{ Illuminate\Support\Js::from(trans('button.cancel')) }},
                save: {{ Illuminate\Support\Js::from(trans('general.save')) }},
                accept: {{ Illuminate\Support\Js::from(trans('button.submit')) }},
                edit: {{ Illuminate\Support\Js::from(trans('button.edit')) }},
                delete: {{ Illuminate\Support\Js::from(trans('button.delete')) }},
                actions: {{ Illuminate\Support\Js::from(trans('button.actions')) }},
                itemName: {{ Illuminate\Support\Js::from(trans('general.item_name')) }},
                manufacturer: {{ Illuminate\Support\Js::from(trans('general.manufacturer')) }},
                category: {{ Illuminate\Support\Js::from(trans('general.category')) }},
                model: {{ Illuminate\Support\Js::from(trans('general.model_no')) }},
                reviewed: {{ Illuminate\Support\Js::from(trans('general.reviewed')) }}
            };
            let consumables = {{ Illuminate\Support\Js::from($consumableRows) }};

            function apiRequest(method, url, requestData) {
                return $.ajax({
                    type: method,
                    url: url,
                    headers: requestHeaders,
                    data: requestData,
                    dataType: 'json'
                });
            }

            function messageText(message, fallbackMessage) {
                if (typeof message === 'string' && message.length > 0) {
                    return message;
                }

                if (Array.isArray(message)) {
                    return message.join(' ');
                }

                if (message && typeof message === 'object') {
                    return Object.keys(message).map(function (key) {
                        return Array.isArray(message[key]) ? message[key].join(' ') : message[key];
                    }).join(' ');
                }

                return fallbackMessage;
            }

            function showApiError(response, fallbackMessage) {
                const responseData = response && response.responseJSON ? response.responseJSON : response;
                const messages = responseData && responseData.messages ? responseData.messages : null;

                Swal.fire({
                    title: labels.error,
                    text: messageText(messages, fallbackMessage),
                    icon: 'error'
                });
            }

            function responseSucceeded(response, fallbackMessage) {
                if (response && response.status === 'success') {
                    return true;
                }

                showApiError(response, fallbackMessage);
                return false;
            }

            function reloadConsumables(updatedConsumables) {
                consumables = Array.isArray(updatedConsumables) ? updatedConsumables : [];
                $consumablesTable.bootstrapTable('load', consumables);
            }

            function syncConsumableLine(requestData) {
                apiRequest('POST', endpoints.line, requestData)
                    .done(function (response) {
                        if (responseSucceeded(response, labels.unableSaveConsumable)) {
                            reloadConsumables(response.payload.consumables);
                        }
                    })
                    .fail(function (response) {
                        showApiError(response, labels.unableSaveConsumable);
                    });
            }

            function renderStatus(status) {
                const statusLabel = statusLabels[status] || ['default', status];
                const $label = $('<span>', {
                    class: 'label label-' + statusLabel[0],
                    text: statusLabel[1]
                });

                $('.status_label').empty().append($label);
            }

            function refreshPurchase() {
                apiRequest('GET', endpoints.purchase)
                    .done(function (purchase) {
                        renderStatus(purchase.status);
                        if ($acceptedConsumablesTable.length) {
                            $acceptedConsumablesTable.bootstrapTable('refresh');
                        }
                    })
                    .fail(function (response) {
                        showApiError(response, labels.unableUpdatePurchaseStatus);
                    });
            }

            function remainingQuantity(row) {
                return Math.max(Number(row.quantity || 0) - Number(row.reviewed || 0), 0);
            }

            function createEditForm(row, minimumQuantity) {
                const $form = $('<div>');
                const fields = [
                    ['swal_purchase_cost', labels.purchaseCost, 0, 0.01, row.purchase_cost],
                    ['swal_nds', labels.nds, 0, 0.01, row.nds],
                    ['swal_quantity', labels.quantity, minimumQuantity, 1, row.quantity]
                ];

                fields.forEach(function (field) {
                    $('<div>', {class: 'form-group text-left'})
                        .append($('<label>', {for: field[0], text: field[1]}))
                        .append($('<input>', {
                            id: field[0],
                            class: 'swal2-input',
                            type: 'number',
                            min: field[2],
                            step: field[3]
                        }).val(field[4]))
                        .appendTo($form);
                });

                return $form[0];
            }

            function validateEditForm(minimumQuantity) {
                const purchaseCost = $('#swal_purchase_cost').val();
                const nds = $('#swal_nds').val();
                const quantity = Number($('#swal_quantity').val());

                if (purchaseCost === '' || !Number.isFinite(Number(purchaseCost)) || Number(purchaseCost) < 0) {
                    Swal.showValidationMessage(labels.invalidPurchaseCost);
                    return false;
                }

                if (nds === '' || !Number.isFinite(Number(nds)) || Number(nds) < 0) {
                    Swal.showValidationMessage(labels.invalidNds);
                    return false;
                }

                if (!Number.isInteger(quantity) || quantity < minimumQuantity) {
                    Swal.showValidationMessage(labels.quantityBelowReviewed);
                    return false;
                }

                return {purchase_cost: purchaseCost, nds: nds, quantity: quantity};
            }

            const actionEvents = {
                'click .check_consumable': function (event, value, row, index) {
                    const maxQuantity = remainingQuantity(row);
                    if (maxQuantity < 1) {
                        return;
                    }

                    Swal.fire({
                        title: labels.acceptNamedItem.replace(':item', row.consumable),
                        icon: 'question',
                        input: 'range',
                        inputLabel: labels.quantity,
                        inputAttributes: {min: 1, max: maxQuantity, step: 1},
                        inputValue: maxQuantity,
                        reverseButtons: true,
                        showCancelButton: true,
                        confirmButtonText: labels.confirm,
                        cancelButtonText: labels.cancel
                    }).then(function (result) {
                        if (!result.isConfirmed) {
                            return;
                        }

                        apiRequest('POST', endpoints.review + '/' + encodeURIComponent(row.consumable_id) + '/review', {
                            purchase_id: purchaseId,
                            quantity: result.value,
                            nds: row.nds,
                            purchase_cost: row.purchase_cost
                        }).done(function (response) {
                            if (!responseSucceeded(response, labels.unableReviewConsumable)) {
                                return;
                            }

                            row.reviewed = Number(row.reviewed || 0) + Number(result.value);
                            $consumablesTable.bootstrapTable('updateRow', {index: index, row: row});
                            refreshPurchase();
                        }).fail(function (response) {
                            showApiError(response, labels.unableReviewConsumable);
                        });
                    });
                },
                'click .edit_consumable': function (event, value, row) {
                    const minimumQuantity = Math.max(Number(row.reviewed || 0), 1);

                    Swal.fire({
                        title: labels.editNamedItem.replace(':item', row.consumable),
                        icon: 'question',
                        html: createEditForm(row, minimumQuantity),
                        reverseButtons: true,
                        showCancelButton: true,
                        confirmButtonText: labels.save,
                        cancelButtonText: labels.cancel,
                        preConfirm: function () {
                            return validateEditForm(minimumQuantity);
                        }
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            syncConsumableLine({
                                action: 'update',
                                row_id: row.id,
                                purchase_cost: result.value.purchase_cost,
                                nds: result.value.nds,
                                quantity: result.value.quantity
                            });
                        }
                    });
                },
                'click .remove_consumable': function (event, value, row) {
                    Swal.fire({
                        title: labels.deleteNamedItem.replace(':item', row.consumable),
                        text: labels.removePurchaseLineConfirmation,
                        icon: 'warning',
                        reverseButtons: true,
                        showCancelButton: true,
                        confirmButtonText: labels.delete,
                        cancelButtonText: labels.cancel
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            syncConsumableLine({action: 'delete', row_id: row.id});
                        }
                    });
                }
            };

            function column(field, title, align) {
                return {field: field, title: title, align: align || 'left', valign: 'middle'};
            }

            function consumableLink(value, row) {
                return $('<a>', {
                    href: endpoints.consumables + '/' + encodeURIComponent(row.consumable_id),
                    text: value || ''
                })[0].outerHTML;
            }

            function actionButton(className, label) {
                return $('<button>', {
                    type: 'button',
                    class: 'btn btn-sm ' + className,
                    text: label
                })[0].outerHTML;
            }

            function actionButtons(value, row) {
                const buttons = [];
                if (remainingQuantity(row) > 0) {
                    buttons.push(actionButton('btn-primary check_consumable', labels.accept));
                }
                buttons.push(actionButton('btn-default edit_consumable', labels.edit));
                if (Number(row.reviewed || 0) === 0) {
                    buttons.push(actionButton('btn-danger remove_consumable', labels.delete));
                }
                return buttons.join(' ');
            }

            function tableColumns() {
                if (isLegacyPurchase) {
                    return [
                        column('id', '#'),
                        column('name', labels.itemName),
                        column('manufacturer_name', labels.manufacturer),
                        column('category_name', labels.category),
                        column('model_number', labels.model),
                        column('purchase_cost', labels.purchaseCost, 'center'),
                        column('nds', labels.nds, 'center'),
                        column('quantity', labels.quantity, 'center')
                    ];
                }

                const columns = [
                    column('id', '#'),
                    Object.assign(column('consumable', labels.itemName), {formatter: consumableLink}),
                    column('model_number', labels.model),
                    column('purchase_cost', labels.purchaseCost, 'center'),
                    column('nds', labels.nds, 'center'),
                    column('quantity', labels.quantity, 'center'),
                    column('reviewed', labels.reviewed, 'center')
                ];

                if (canReviewConsumables) {
                    columns.push({
                        title: labels.actions,
                        align: 'center',
                        valign: 'middle',
                        width: 240,
                        events: actionEvents,
                        formatter: actionButtons
                    });
                }

                return columns;
            }

            $checkConsumables.on('click', function () {
                $checkConsumables.prop('disabled', true);
                apiRequest('POST', endpoints.check)
                    .done(function (response) {
                        if (responseSucceeded(response, labels.unableReviewConsumables)) {
                            $checkConsumables.hide();
                            refreshPurchase();
                        } else {
                            $checkConsumables.prop('disabled', false);
                        }
                    })
                    .fail(function (response) {
                        $checkConsumables.prop('disabled', false);
                        showApiError(response, labels.unableReviewConsumables);
                    });
            });

            if ($consumablesTable.length) {
                $consumablesTable.bootstrapTable('destroy').bootstrapTable({
                    data: consumables,
                    search: true,
                    stickyHeader: false,
                    locale: 'ru',
                    toolbar: '#toolbar_consumables',
                    columns: tableColumns()
                });
            }
        });
    </script>
@stop
