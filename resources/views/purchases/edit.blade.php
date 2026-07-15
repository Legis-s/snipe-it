@extends('layouts/edit-form', [
    'createText' => trans('general.create_purchase'),
    'updateText' => trans('general.update_purchase'),
    'topSubmit' => true,
    'formAction' => route('purchases.store'),
])


{{-- Page content --}}
@section('inputFields')

    <!-- invoice_number -->
    <div class="form-group {{ $errors->has('invoice_number') ? ' has-error' : '' }}">
        <label for="invoice_number" class="col-md-3 control-label">{{ trans('general.item_name') }}</label>
        <div class="col-md-7 col-sm-12{{  (Helper::checkIfRequired($item, 'invoice_number')) ? ' required' : '' }}">
            <input class="form-control" type="text" name="invoice_number" aria-label="invoice_number"
                   id="invoice_number"
                   value="{{ old('invoice_number', $item->invoice_number) }}"{!!  (Helper::checkIfRequired($item, 'invoice_number')) ? ' data-validation="required" required' : '' !!} />
            {!! $errors->first('invoice_number', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- final_price -->
    <div class="form-group {{ $errors->has('final_price') ? ' has-error' : '' }}">
        <label for="final_price" class="col-md-3 control-label">{{ trans('admin/asset_maintenances/form.cost') }}</label>
        <div class="col-md-7 col-sm-12{{  (Helper::checkIfRequired($item, 'final_price')) ? ' required' : '' }}">
            <input class="form-control float" type="text" name="final_price" aria-label="final_price" id="final_price"
                   value="{{ old('final_price', $item->final_price) }}"{!!  (Helper::checkIfRequired($item, 'final_price')) ? ' data-validation="required" required' : '' !!} />
            {!! $errors->first('final_price', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- delivery_cost -->
    <div class="form-group {{ $errors->has('delivery_cost') ? ' has-error' : '' }}">
        <label for="delivery_cost" class="col-md-3 control-label">{{ trans('general.delivery_cost') }}</label>
        <div class="col-md-7 col-sm-12{{  (Helper::checkIfRequired($item, 'delivery_cost')) ? ' required' : '' }}">
            <input class="form-control float" type="text" name="delivery_cost" aria-label="delivery_cost"
                   id="delivery_cost"
                   value="{{ old('delivery_cost', $item->delivery_cost) }}"{!!  (Helper::checkIfRequired($item, 'delivery_cost')) ? ' data-validation="required" required' : '' !!} />
            {!! $errors->first('delivery_cost', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- comment -->
    <div class="form-group{{ $errors->has('comment') ? ' has-error' : '' }}">
        <label for="comment" class="col-md-3 control-label">{{ trans('general.comment') }}</label>
        <div class="col-md-7 col-sm-12">
            <textarea class="col-md-6 form-control" id="comment" aria-label="comment" name="comment"
                      style="min-width:100%;">{{ old('comment', $item->comment) }}</textarea>
            {!! $errors->first('comment', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>



    @include ('partials.forms.edit.supplier-select', ['translated_name' => trans('general.supplier'), 'fieldname' => 'supplier_id','required'=>true, 'hide_new'=>true ])

    @include ('partials.forms.purchases.invoice-type-select', ['translated_name' => trans('general.invoice_type'), 'fieldname' => 'invoice_type_id','required'=>true])

    @include ('partials.forms.purchases.legal_person-select', ['translated_name' => trans('general.legal_person'), 'fieldname' => 'legal_person_id','required'=>true])

    @include ('partials.forms.purchases.invoice_file', ['required'=>true,])

    <input type="hidden" id="assets" name="assets" required value="{{ old('assets', $item->assets_json) }}">
    <input type="hidden" id="consumables" required name="consumables"
           value="{{ old('consumables', $item->consumables_json) }}">
    <p class="purchase-items-error text-center text-bold text-danger hidden">{{ trans('general.purchase_items_required') }}</p>

    <div class="row">
        <div class="col-md-12">
            <div class="table table-responsive">
                <div id="toolbar_asset">
                    <button type="button" class="btn btn-sm btn-theme" data-toggle="modal" data-target="#modal_asset">
                        {{ trans('general.add_asset') }}
                    </button>
                </div>
                <table id="table_asset" class="table table-striped snipe-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ trans('general.model_no') }}</th>
                        <th>{{ trans('general.sklad') }}</th>
                        <th>{{ trans('general.purchase_cost') }}</th>
                        <th>{{ trans('general.nds') }}</th>
                        <th>{{ trans('general.quantity') }}</th>
                        <th>{{ trans('button.delete') }}</th>
                    </tr>
                    </thead>
                </table>
            </div><!-- /.table-responsive -->
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="table table-responsive">
                <div id="toolbar_consumables">
                    <button type="button" class="btn btn-sm btn-theme" data-toggle="modal" data-target="#modal_consumables">
                        {{ trans('admin/kits/general.append_consumable') }}
                    </button>
                </div>
                <table id="table_consumables" class="table table-striped snipe-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ trans('general.model_no') }}</th>
                        <th>{{ trans('general.purchase_cost') }}</th>
                        <th>{{ trans('general.nds') }}</th>
                        <th>{{ trans('general.quantity') }}</th>
                        <th>{{ trans('button.delete') }}</th>
                    </tr>
                    </thead>
                </table>
            </div><!-- /.table-responsive -->
        </div>
    </div>

@stop

@section('content')
    @parent
    <!-- Asset modal -->
    <div class="modal fade" id="modal_asset" tabindex="-1" role="dialog" aria-labelledby="modalAssetTitle">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('general.close') }}"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalAssetTitle">{{ trans('general.add_asset') }}</h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal">

                        @include ('partials.forms.edit.model-select', ['translated_name' => trans('admin/hardware/form.model'), 'fieldname' => 'model_id', 'field_req' => true])

                        <p class="duble text-center text-bold text-danger hidden">{{ trans('general.model_already_added') }}</p>
                        <!-- Purchase Cost -->
                        <div class="form-group {{ $errors->has('purchase_cost') ? ' has-error' : '' }}">
                            <label for="purchase_cost" class="col-md-3 control-label">{{ trans('general.purchase_cost') }}</label>
                            <div class="col-md-7">
                                <div class="input-group col-md-8" style="padding-left: 0px;">
                                    <input class="form-control float" type="text" name="purchase_cost" aria-label="purchase_cost" id="purchase_cost"
                                           value="{{ old('purchase_cost', Helper::formatCurrencyOutput($item->purchase_cost)) }}"/>
                                    <span class="input-group-addon">
                @if (isset($currency_type))
                                            {{ $currency_type }}
                                        @else
                                            {{ $snipeSettings->default_currency }}
                                        @endif
            </span>
                                </div>
                                <div class="col-md-4" style="padding-left: 0px;">
                                    {!! $errors->first('purchase_cost', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                </div>
                            </div>
                        </div>

                        <!-- nds -->
                        <div class="form-group {{ $errors->has('nds') ? ' has-error' : '' }}">
                            <label for="nds" class="col-md-3 control-label">{{ trans('general.nds') }}</label>
                            <div class="col-md-7">
                                <div class="input-group col-md-8" style="padding-left: 0px;">
                                    <input class="form-control" type="number" min="0" name="nds" aria-label="nds" id="nds" value="{{  $item->nds }}"/>
                                    <span class="input-group-addon">%</span>
                                </div>
                                <div class="col-md-4" style="padding-left: 0px;">
                                    {!! $errors->first('nds', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                                </div>
                            </div>
                        </div>

                        <!-- Warranty -->
                        <div class="form-group {{ $errors->has('warranty_months') ? ' has-error' : '' }}">
                            <label for="warranty_months" class="col-md-3 control-label">{{ trans('admin/hardware/form.warranty') }}</label>
                            <div class="col-md-7">
                                <div class="input-group col-md-8" style="padding-left: 0px;">
                                    <input class="form-control" type="text" name="warranty_months" id="warranty_months"
                                           value="{{ old('warranty_months', $item->warranty_months) }}" maxlength="3"/>
                                    <span class="input-group-addon">{{ trans('admin/hardware/form.months') }}</span>
                                </div>
                                <div class="col-md-4" style="padding-left: 0px;">
                                    {!! $errors->first('warranty_months', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                </div>
                            </div>
                        </div>

                        <!-- QTY -->
                        <div class="form-group {{ $errors->has('qty') ? ' has-error' : '' }}">
                            <label for="qty" class="col-md-3 control-label">{{ trans('general.quantity') }}</label>
                            <div class="col-md-7">
                                <div class="col-md-8" style="padding-left:0px">
                                    <input class="form-control" type="number" min="1" name="quantity" aria-label="quantity" id="quantity" value="1"/>
                                </div>
                                <div class="col-md-4" style="padding-left: 0px;">
                                    {!! $errors->first('qty', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                </div>
                            </div>
                        </div>

                        @if(Auth::user()->favoriteLocation)
                            @include ('partials.forms.purchases.location-select-checkin', ['translated_name' => trans('general.expected_location'), 'fieldname' => 'location_id','hide_new'=>true])
                        @else
                            @include ('partials.forms.purchases.location-select', ['translated_name' => trans('general.expected_location'), 'fieldname' => 'location_id','hide_new'=>true ])
                        @endif
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('button.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="addAssetButton">{{ trans('button.add') }}</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Consumable modal -->
    <div class="modal fade" id="modal_consumables" tabindex="-1" role="dialog" aria-labelledby="modalConsumablesTitle">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('general.close') }}"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="modalConsumablesTitle">{{ trans('admin/kits/general.append_consumable') }}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <form class="form-horizontal">
                                @include ('partials.forms.purchases.consumables-select', ['translated_name' => trans('general.item_name'), 'fieldname' => 'consumable_id', 'required' => 'true'])
                                <p class="duble text-center text-bold text-danger hidden">{{ trans('general.model_already_added') }}</p>
                                @include ('partials.forms.purchases.purchase_cost')
                                @include ('partials.forms.purchases.nds')
                                @include ('partials.forms.purchases.quantity')
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('button.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="addConsumablesButton">{{ trans('button.add') }}</button>
                </div>
            </div>
        </div>
    </div>
@stop

@if (!$item->id)
    @section('moar_scripts')
        @include ('partials.bootstrap-table')
        <script nonce="{{ csrf_token() }}">
            $(function () {
                const tableAsset = $('#table_asset');
                const tableConsumables = $('#table_consumables');
                const baseUrl = $('meta[name="baseUrl"]').attr('content');
                const csrfToken = $('meta[name="csrf-token"]').attr('content');
                const labels = {
                    delete: {{ Illuminate\Support\Js::from(trans('button.delete')) }},
                    loading: {{ Illuminate\Support\Js::from(trans('general.loading')) }},
                    model: {{ Illuminate\Support\Js::from(trans('general.model_no')) }},
                    location: {{ Illuminate\Support\Js::from(trans('general.sklad')) }},
                    purchaseCost: {{ Illuminate\Support\Js::from(trans('general.purchase_cost')) }},
                    nds: {{ Illuminate\Support\Js::from(trans('general.nds')) }},
                    quantity: {{ Illuminate\Support\Js::from(trans('general.quantity')) }},
                    pendingApproval: {{ Illuminate\Support\Js::from(trans('general.purchase_statuses.inprogress')) }}
                };

                function tableColumn(field, title, align = 'center') {
                    return { field, title, align, valign: 'middle' };
                }

                function reindexTable(table) {
                    const rows = table.bootstrapTable('getData').map(function (row, index) {
                        return Object.assign({}, row, { id: index + 1 });
                    });
                    table.bootstrapTable('load', rows);
                }

                function removeColumn(table) {
                    return {
                        title: labels.delete,
                        align: 'center',
                        valign: 'middle',
                        events: {
                            'click .remove': function (event, value, row) {
                                table.bootstrapTable('remove', { field: 'id', values: [row.id] });
                                reindexTable(table);
                            }
                        },
                        formatter: function () {
                            return $('<button>', {
                                type: 'button',
                                class: 'btn btn-link btn-sm remove text-danger',
                                title: labels.delete,
                                'aria-label': labels.delete
                            }).append('<i class="fas fa-times fa-lg" aria-hidden="true"></i>')[0].outerHTML;
                        }
                    };
                }

                function initializeTable(table, toolbar, columns) {
                    table.bootstrapTable('destroy').bootstrapTable({
                        locale: 'ru',
                        data: [],
                        search: true,
                        toolbar,
                        columns: columns.concat(removeColumn(table))
                    });
                }

                function loadStoredRows(table, inputSelector) {
                    const serializedRows = $(inputSelector).val();
                    if (!serializedRows) {
                        return;
                    }

                    try {
                        const rows = JSON.parse(serializedRows);
                        table.bootstrapTable('load', Array.isArray(rows) ? rows : []);
                    } catch (error) {
                        $(inputSelector).val('');
                    }
                }

                function formatPurchaseDatalist(item) {
                    if (item.loading) {
                        return $('<span>').append(
                            $('<i class="fas fa-spinner fa-spin" aria-hidden="true"></i>'),
                            ' ' + labels.loading
                        );
                    }

                    const media = $('<div class="pull-left">').css({ width: '30px', paddingRight: '10px' });
                    if (item.image) {
                        media.append($('<img alt="">').attr('src', item.image).css({ maxHeight: '20px', maxWidth: '20px' }));
                    } else if (item.tag_color) {
                        media.append($('<i class="fa-solid fa-square" aria-hidden="true"></i>').css({ color: item.tag_color, fontSize: '20px' }));
                    }

                    return $('<div class="clearfix">').append(media, $('<div>').text(item.text));
                }

                function initializeModalSelects(modal) {
                    if (modal.data('purchase-selects-initialized')) {
                        return;
                    }

                    modal.find('select[data-endpoint]').each(function () {
                        const select = $(this);
                        const endpoint = select.data('endpoint');

                        if (select.hasClass('select2-hidden-accessible')) {
                            select.select2('destroy');
                        }

                        select.select2({
                            placeholder: select.data('placeholder') || '',
                            allowClear: true,
                            dropdownParent: modal,
                            ajax: {
                                url: baseUrl + 'api/v1/' + endpoint + '/selectlist',
                                dataType: 'json',
                                delay: 250,
                                headers: {
                                    'X-Requested-With': 'XMLHttpRequest',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                data: function (params) {
                                    return {
                                        search: params.term,
                                        page: params.page || 1,
                                        assetStatusType: select.data('asset-status-type'),
                                        companyId: select.data('company-id')
                                    };
                                },
                                cache: true
                            },
                            templateResult: formatPurchaseDatalist
                        });
                    });

                    modal.data('purchase-selects-initialized', true);
                }

                function resetModal(modal, selectName) {
                    initializeModalSelects(modal);
                    modal.find('select[name="' + selectName + '"]').val(null).trigger('change');
                    modal.find('.form-group').removeClass('has-error');
                    modal.find('#purchase_cost').val('');
                    modal.find('#nds').val(22);
                    modal.find('#quantity').val(1);
                    modal.find('.duble').addClass('hidden');
                }

                function selectedOption(modal, fieldName) {
                    return modal.find('select[name="' + fieldName + '"] option:selected');
                }

                function containsRow(rows, field, value) {
                    return rows.some(function (row) {
                        return String(row[field]) === String(value);
                    });
                }

                function validateRequired(selector) {
                    const field = $(selector).first();
                    const valid = String(field.val() || '').trim().length > 0;
                    field.closest('.form-group').toggleClass('has-error', !valid);
                    return valid;
                }

                $('.js-data-no-ajax').select2();

                $('input.float').on('input', function () {
                    this.value = this.value
                        .replace(',', '.')
                        .replace(/[^0-9.]/g, '')
                        .replace(/(\..*)\./g, '$1');
                });

                initializeTable(tableAsset, '#toolbar_asset', [
                    tableColumn('id', '#', 'left'),
                    tableColumn('model', labels.model, 'left'),
                    tableColumn('location', labels.location),
                    tableColumn('purchase_cost', labels.purchaseCost),
                    tableColumn('nds', labels.nds),
                    tableColumn('quantity', labels.quantity)
                ]);
                initializeTable(tableConsumables, '#toolbar_consumables', [
                    tableColumn('id', '#', 'left'),
                    tableColumn('consumable', labels.model, 'left'),
                    tableColumn('purchase_cost', labels.purchaseCost),
                    tableColumn('nds', labels.nds),
                    tableColumn('quantity', labels.quantity)
                ]);

                loadStoredRows(tableAsset, '#assets');
                loadStoredRows(tableConsumables, '#consumables');

                $('#modal_asset').on('show.bs.modal', function () {
                    const modal = $(this);
                    resetModal(modal, 'model_id');
                    modal.find('#warranty_months').val(12);
                });

                $('#modal_consumables').on('show.bs.modal', function () {
                    resetModal($(this), 'consumable_id');
                });

                $('#addAssetButton').on('click', function () {
                    const modal = $('#modal_asset');
                    const model = selectedOption(modal, 'model_id');
                    const location = selectedOption(modal, 'location_id');
                    const modelId = model.val();
                    const rows = tableAsset.bootstrapTable('getData');

                    if (!modelId) {
                        model.closest('.form-group').addClass('has-error');
                        return;
                    }

                    if (containsRow(rows, 'model_id', modelId)) {
                        modal.find('.duble').removeClass('hidden');
                        return;
                    }

                    tableAsset.bootstrapTable('append', {
                        id: rows.length + 1,
                        model_id: modelId,
                        model: model.text(),
                        location_id: location.val(),
                        location: location.text(),
                        purchase_cost: modal.find('#purchase_cost').val(),
                        nds: modal.find('#nds').val(),
                        warranty: modal.find('#warranty_months').val(),
                        quantity: modal.find('#quantity').val()
                    });
                    modal.modal('hide');
                });

                $('#addConsumablesButton').on('click', function () {
                    const modal = $('#modal_consumables');
                    const consumable = selectedOption(modal, 'consumable_id');
                    const consumableId = consumable.val();
                    const rows = tableConsumables.bootstrapTable('getData');

                    if (!consumableId) {
                        consumable.closest('.form-group').addClass('has-error');
                        return;
                    }

                    if (containsRow(rows, 'consumable_id', consumableId)) {
                        modal.find('.duble').removeClass('hidden');
                        return;
                    }

                    tableConsumables.bootstrapTable('append', {
                        id: rows.length + 1,
                        consumable_id: consumableId,
                        consumable: consumable.text(),
                        purchase_cost: modal.find('#purchase_cost').val(),
                        nds: modal.find('#nds').val(),
                        quantity: modal.find('#quantity').val(),
                        check: false,
                        status: labels.pendingApproval
                    });
                    modal.modal('hide');
                });

                $('#create-form').on('submit', function () {
                    const assets = tableAsset.bootstrapTable('getData');
                    const consumables = tableConsumables.bootstrapTable('getData');
                    const hasItems = assets.length > 0 || consumables.length > 0;
                    const upload = $('#uploadFile').get(0);
                    const hasInvoiceFile = Boolean(upload && upload.files.length);
                    const requiredFieldsValid = [
                        '#invoice_number',
                        '#final_price',
                        '#comment',
                        'select[name="supplier_id"]',
                        'select[name="invoice_type_id"]',
                        'select[name="legal_person_id"]'
                    ].map(validateRequired).every(Boolean);

                    $('#assets').val(JSON.stringify(assets));
                    $('#consumables').val(JSON.stringify(consumables));
                    $('.purchase-items-error').toggleClass('hidden', hasItems);
                    $('#uploadFile').closest('.form-group').toggleClass('has-error', !hasInvoiceFile);

                    return hasItems && hasInvoiceFile && requiredFieldsValid;
                });
            });
        </script>
    @stop
@endif
