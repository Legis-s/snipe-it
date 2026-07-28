@extends('layouts/edit-form', [
    'createText' => trans('general.create_purchase'),
    'updateText' => trans('general.update_purchase'),
    'topSubmit' => true,
    'formAction' => route('purchases.store'),
])

@push('css')
    <style>
        #table_asset tbody tr.purchase-item-new > td,
        #table_consumables tbody tr.purchase-item-new > td {
            background-color: #e8f5e9 !important;
        }

        #table_asset tbody tr.purchase-item-review > td,
        #table_consumables tbody tr.purchase-item-review > td {
            background-color: #fff3cd !important;
        }
    </style>
@endpush


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
                        <th>{{ trans('button.actions') }}</th>
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
                        <th>{{ trans('button.actions') }}</th>
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

    <div class="modal fade" id="modal_purchase_item_edit" tabindex="-1" role="dialog"
         aria-labelledby="modalPurchaseItemEditTitle">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ trans('general.close') }}">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="modalPurchaseItemEditTitle"></h4>
                </div>
                <div class="modal-body">
                    <form class="form-horizontal">
                        <div class="ai-new-item-fields hidden">
                            <div class="form-group">
                                <label for="edit_item_name" class="col-md-3 control-label">{{ trans('general.item_name') }}</label>
                                <div class="col-md-7">
                                    <input type="text" class="form-control" id="edit_item_name" maxlength="255">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="edit_model_number" class="col-md-3 control-label">{{ trans('general.model_no') }}</label>
                                <div class="col-md-7">
                                    <input type="text" class="form-control" id="edit_model_number" maxlength="255">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_purchase_cost" class="col-md-3 control-label">{{ trans('general.purchase_cost') }}</label>
                            <div class="col-md-7">
                                <input type="number" class="form-control" id="edit_purchase_cost" min="0" step="0.01">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_nds" class="col-md-3 control-label">{{ trans('general.nds') }}</label>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="number" class="form-control" id="edit_nds" min="0" step="1">
                                    <span class="input-group-addon">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="edit_quantity" class="col-md-3 control-label">{{ trans('general.quantity') }}</label>
                            <div class="col-md-7">
                                <input type="number" class="form-control" id="edit_quantity" min="1" step="1">
                            </div>
                        </div>
                        <div class="asset-edit-fields hidden">
                            <div class="form-group">
                                <label for="edit_model_id" class="col-md-3 control-label">{{ trans('admin/hardware/form.model') }}</label>
                                <div class="col-md-7">
                                    <select data-endpoint="models"
                                            data-placeholder="{{ trans('general.select_model') }}"
                                            id="edit_model_id" style="width: 100%">
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="edit_warranty" class="col-md-3 control-label">{{ trans('admin/hardware/form.warranty') }}</label>
                                <div class="col-md-7">
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="edit_warranty" min="0" max="999" step="1">
                                        <span class="input-group-addon">{{ trans('admin/hardware/form.months') }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="edit_location_id" class="col-md-3 control-label">{{ trans('general.sklad') }}</label>
                                <div class="col-md-7">
                                    <select data-endpoint="locations"
                                            data-placeholder="{{ trans('general.select_location') }}"
                                            id="edit_location_id" style="width: 100%">
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="consumable-edit-fields hidden">
                            <div class="form-group">
                                <label for="edit_consumable_id" class="col-md-3 control-label">{{ trans('general.model_no') }}</label>
                                <div class="col-md-7">
                                    <select data-endpoint="consumables"
                                            data-placeholder="{{ trans('general.select_consumable') }}"
                                            id="edit_consumable_id" style="width: 100%">
                                    </select>
                                </div>
                            </div>
                        </div>
                        <p class="edit-item-error text-center text-bold text-danger hidden"></p>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('button.cancel') }}</button>
                    <button type="button" class="btn btn-primary" id="savePurchaseItemButton">{{ trans('general.save') }}</button>
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
                const recognizeInvoiceUrl = {{ Illuminate\Support\Js::from(route('purchases.recognize-invoice')) }};
                const editItemModal = $('#modal_purchase_item_edit');
                let editingItem = null;
                const labels = {
                    actions: {{ Illuminate\Support\Js::from(trans('button.actions')) }},
                    delete: {{ Illuminate\Support\Js::from(trans('button.delete')) }},
                    edit: {{ Illuminate\Support\Js::from(trans('button.edit')) }},
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

                function itemActionsColumn(table, itemType) {
                    return {
                        title: labels.actions,
                        align: 'center',
                        valign: 'middle',
                        events: {
                            'click .edit': function (event, value, row) {
                                openItemEditor(table, itemType, row);
                            },
                            'click .remove': function (event, value, row) {
                                table.bootstrapTable('remove', { field: 'id', values: [row.id] });
                                reindexTable(table);
                            }
                        },
                        formatter: function () {
                            const editButton = $('<button>', {
                                type: 'button',
                                class: 'btn btn-link btn-sm edit',
                                title: labels.edit,
                                'aria-label': labels.edit
                            }).append('<i class="fas fa-pencil-alt" aria-hidden="true"></i>');
                            const removeButton = $('<button>', {
                                type: 'button',
                                class: 'btn btn-link btn-sm remove text-danger',
                                title: labels.delete,
                                'aria-label': labels.delete
                            }).append('<i class="fas fa-times fa-lg" aria-hidden="true"></i>');

                            return $('<div>').append(editButton, removeButton).html();
                        }
                    };
                }

                function purchaseItemRowStyle(row) {
                    if (row.match_status === 'review') {
                        return { classes: 'purchase-item-review' };
                    }
                    if (row.match_status === 'new' || row.create_new) {
                        return { classes: 'purchase-item-new' };
                    }

                    return {};
                }

                function initializeTable(table, toolbar, columns, itemType) {
                    table.bootstrapTable('destroy').bootstrapTable({
                        locale: 'ru',
                        data: [],
                        search: true,
                        toolbar,
                        rowStyle: purchaseItemRowStyle,
                        columns: columns.concat(itemActionsColumn(table, itemType))
                    });
                }

                function setEditorError(message) {
                    editItemModal.find('.edit-item-error')
                        .text(message || '')
                        .toggleClass('hidden', !message);
                }

                function openItemEditor(table, itemType, row) {
                    editingItem = {
                        table: table,
                        itemType: itemType,
                        rowId: row.id,
                        wasCreateNew: Boolean(row.create_new)
                    };

                    initializeModalSelects(editItemModal);
                    setEditorError('');
                    editItemModal.find('#modalPurchaseItemEditTitle')
                        .text(itemType === 'asset' ? 'Редактировать актив' : 'Редактировать расходный материал');
                    editItemModal.find('.asset-edit-fields').toggleClass('hidden', itemType !== 'asset');
                    editItemModal.find('.consumable-edit-fields').toggleClass('hidden', itemType !== 'consumable');
                    editItemModal.find('#edit_item_name').val(row.new_item_name || '');
                    editItemModal.find('#edit_model_number').val(row.new_item_model_number || '');
                    editItemModal.find('#edit_purchase_cost').val(row.purchase_cost);
                    editItemModal.find('#edit_nds').val(row.nds);
                    editItemModal.find('#edit_quantity').val(row.quantity);
                    editItemModal.find('#edit_warranty').val(row.warranty || 0);

                    const modelSelect = editItemModal.find('#edit_model_id');
                    modelSelect.empty();
                    if (row.model_id) {
                        modelSelect.append(new Option(
                            row.model || String(row.model_id),
                            row.model_id,
                            true,
                            true
                        ));
                    }
                    modelSelect.val(row.model_id || null).trigger('change');

                    const consumableSelect = editItemModal.find('#edit_consumable_id');
                    consumableSelect.empty();
                    if (row.consumable_id) {
                        consumableSelect.append(new Option(
                            row.consumable || String(row.consumable_id),
                            row.consumable_id,
                            true,
                            true
                        ));
                    }
                    consumableSelect.val(row.consumable_id || null).trigger('change');

                    const locationSelect = editItemModal.find('#edit_location_id');
                    locationSelect.empty();
                    if (row.location_id) {
                        locationSelect.append(new Option(
                            row.location || String(row.location_id),
                            row.location_id,
                            true,
                            true
                        ));
                    }
                    locationSelect.val(row.location_id || null).trigger('change');
                    updateNewItemFieldsVisibility();
                    editItemModal.modal('show');
                }

                function updateNewItemFieldsVisibility() {
                    const showNewItemFields = editingItem
                        && editingItem.wasCreateNew
                        && (
                            (editingItem.itemType === 'asset' && !editItemModal.find('#edit_model_id').val())
                            || (editingItem.itemType === 'consumable' && !editItemModal.find('#edit_consumable_id').val())
                        );

                    editItemModal.find('.ai-new-item-fields').toggleClass('hidden', !showNewItemFields);
                }

                function newItemDisplayName(row) {
                    const name = String(row.new_item_name || '').trim();
                    const modelNumber = String(row.new_item_model_number || '').trim();
                    let displayName = name || modelNumber;

                    if (name && modelNumber && !name.toLocaleLowerCase().includes(modelNumber.toLocaleLowerCase())) {
                        displayName += ' (' + modelNumber + ')';
                    }

                    return displayName + ' — будет создано';
                }

                function editorValues(itemType, row) {
                    const purchaseCost = Number(editItemModal.find('#edit_purchase_cost').val());
                    const nds = Number(editItemModal.find('#edit_nds').val());
                    const quantity = Number(editItemModal.find('#edit_quantity').val());
                    const warranty = Number(editItemModal.find('#edit_warranty').val());

                    if (!Number.isFinite(purchaseCost) || purchaseCost < 0) {
                        setEditorError('Укажите корректную закупочную цену.');
                        return null;
                    }
                    if (!Number.isInteger(nds) || nds < 0) {
                        setEditorError('Укажите НДС целым неотрицательным числом.');
                        return null;
                    }
                    if (!Number.isInteger(quantity) || quantity < 1) {
                        setEditorError('Количество должно быть целым числом больше нуля.');
                        return null;
                    }
                    if (itemType === 'asset' && (!Number.isInteger(warranty) || warranty < 0 || warranty > 999)) {
                        setEditorError('Гарантия должна быть целым числом от 0 до 999 месяцев.');
                        return null;
                    }

                    const updated = Object.assign({}, row, {
                        purchase_cost: purchaseCost,
                        nds: nds,
                        quantity: quantity
                    });

                    if (itemType === 'asset') {
                        const model = editItemModal.find('#edit_model_id option:selected');
                        const modelId = model.val();

                        if (modelId) {
                            const confirmingSuggestion = row.match_status === 'review'
                                && String(row.model_id) === String(modelId);
                            const duplicate = !confirmingSuggestion
                                && editingItem.table.bootstrapTable('getData').some(function (otherRow) {
                                    return String(otherRow.id) !== String(editingItem.rowId)
                                        && String(otherRow.model_id) === String(modelId);
                                });
                            if (duplicate) {
                                setEditorError('Эта модель уже добавлена в закупку.');
                                return null;
                            }

                            updated.model_id = modelId;
                            updated.model = model.text();
                            updated.create_new = false;
                            updated.match_status = 'matched';
                        } else if (editingItem.wasCreateNew) {
                            updated.model_id = null;
                            updated.create_new = true;
                            updated.match_status = 'new';
                        } else {
                            setEditorError('Выберите модель актива.');
                            return null;
                        }
                    }

                    if (itemType === 'consumable') {
                        const consumable = editItemModal.find('#edit_consumable_id option:selected');
                        const consumableId = consumable.val();

                        if (consumableId) {
                            const confirmingSuggestion = row.match_status === 'review'
                                && String(row.consumable_id) === String(consumableId);
                            const duplicate = !confirmingSuggestion
                                && editingItem.table.bootstrapTable('getData').some(function (otherRow) {
                                    return String(otherRow.id) !== String(editingItem.rowId)
                                        && String(otherRow.consumable_id) === String(consumableId);
                                });
                            if (duplicate) {
                                setEditorError('Этот расходный материал уже добавлен в закупку.');
                                return null;
                            }

                            updated.consumable_id = consumableId;
                            updated.consumable = consumable.text();
                            updated.create_new = false;
                            updated.match_status = 'matched';
                        } else if (editingItem.wasCreateNew) {
                            updated.consumable_id = null;
                            updated.create_new = true;
                            updated.match_status = 'new';
                        } else {
                            setEditorError('Выберите расходный материал.');
                            return null;
                        }
                    }

                    if (updated.create_new) {
                        updated.new_item_name = String(editItemModal.find('#edit_item_name').val() || '').trim();
                        updated.new_item_model_number = String(editItemModal.find('#edit_model_number').val() || '').trim();
                        if (!updated.new_item_name && !updated.new_item_model_number) {
                            setEditorError('Укажите название или артикул новой позиции.');
                            return null;
                        }

                        if (itemType === 'asset') {
                            updated.model = newItemDisplayName(updated);
                        } else {
                            updated.consumable = newItemDisplayName(updated);
                        }
                    }

                    if (itemType === 'asset') {
                        const location = editItemModal.find('#edit_location_id option:selected');
                        updated.warranty = warranty;
                        updated.location_id = location.val() || null;
                        updated.location = location.val() ? location.text() : '';
                    }

                    return updated;
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

                function recognizedRowKey(row, key) {
                    if (row.match_status === 'review') {
                        return 'review:' + String(row.new_item_key || '');
                    }
                    if (row[key] !== null && row[key] !== undefined && String(row[key]) !== '') {
                        return 'existing:' + String(row[key]);
                    }

                    return 'new:' + String(row.new_item_key || '');
                }

                function mergeRecognizedRows(table, newRows, key) {
                    const rows = table.bootstrapTable('getData').slice();

                    newRows.forEach(function (newRow) {
                        const existing = rows.find(function (row) {
                            return recognizedRowKey(row, key) === recognizedRowKey(newRow, key);
                        });

                        if (existing) {
                            existing.quantity = Number(existing.quantity || 0) + Number(newRow.quantity || 0);
                            if (newRow.match_status === 'review') {
                                existing.match_status = 'review';
                            }
                            return;
                        }

                        rows.push(newRow);
                    });

                    table.bootstrapTable('load', rows.map(function (row, index) {
                        return Object.assign({}, row, { id: index + 1 });
                    }));
                }

                function setSelect2Value(selector, option) {
                    if (!option || !option.id) {
                        return;
                    }

                    const select = $(selector);
                    if (select.find('option[value="' + option.id + '"]').length === 0) {
                        select.append(new Option(option.text, option.id, true, true));
                    }
                    select.val(String(option.id)).trigger('change');
                }

                function showRecognitionResult(message, isError, isWarning) {
                    $('#invoiceRecognitionResult')
                        .removeClass('hidden alert-success alert-warning alert-danger')
                        .addClass(isError ? 'alert-danger' : (isWarning ? 'alert-warning' : 'alert-success'))
                        .text(message);
                }

                function applyRecognizedInvoice(data) {
                    if (data.invoice_number) {
                        $('#invoice_number').val(data.invoice_number);
                    }
                    if (Number(data.final_price) > 0) {
                        $('#final_price').val(data.final_price);
                    }
                    $('#delivery_cost').val(data.delivery_cost || 0);
                    if (data.comment) {
                        $('#comment').val(data.comment);
                    }

                    setSelect2Value('select[name="supplier_id"]', data.supplier);
                    setSelect2Value('select[name="legal_person_id"]', data.legal_person);
                    mergeRecognizedRows(tableAsset, data.assets || [], 'model_id');
                    mergeRecognizedRows(tableConsumables, data.consumables || [], 'consumable_id');

                    const assets = tableAsset.bootstrapTable('getData');
                    const consumables = tableConsumables.bootstrapTable('getData');
                    $('#assets').val(JSON.stringify(assets));
                    $('#consumables').val(JSON.stringify(consumables));

                    const recognizedRows = (data.assets || []).concat(data.consumables || []);
                    const newRows = recognizedRows.filter(function (item) {
                        return item.match_status === 'new' || item.create_new;
                    });
                    const reviewRows = recognizedRows.filter(function (item) {
                        return item.match_status === 'review';
                    });
                    const matchedInSystem = recognizedRows.filter(function (item) {
                        return item.match_status !== 'review'
                            && item.match_status !== 'new'
                            && !item.create_new;
                    });
                    let message = 'Найдено в счёте: ' + recognizedRows.length + ' позиций.'
                        + ' Сопоставлено с моделями в системе: ' + matchedInSystem.length + '.'
                        + ' Новых моделей: ' + newRows.length + '.'
                        + ' Требуют внимания: ' + reviewRows.length + '.'
                        + ' Все позиции добавлены в таблицы.';

                    showRecognitionResult(message, false, reviewRows.length > 0);
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

                $('#uploadFile').on('change', function () {
                    $('#recognizeInvoiceButton').prop('disabled', !this.files.length);
                    $('#invoiceRecognitionResult').addClass('hidden');
                });

                $('#recognizeInvoiceButton').on('click', function () {
                    const button = $(this);
                    const upload = $('#uploadFile').get(0);

                    if (!upload || !upload.files.length) {
                        showRecognitionResult('Сначала выберите файл счёта.', true, false);
                        return;
                    }

                    const formData = new FormData();
                    formData.append('invoice_file', upload.files[0]);
                    button.prop('disabled', true);
                    button.find('i').removeClass('fa-magic').addClass('fa-spinner fa-spin');
                    showRecognitionResult('Счёт распознаётся...', false, false);

                    $.ajax({
                        url: recognizeInvoiceUrl,
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        data: formData,
                        processData: false,
                        contentType: false
                    }).done(function (response) {
                        applyRecognizedInvoice(response.data);
                    }).fail(function (response) {
                        const payload = response.responseJSON || {};
                        let message = payload.message || 'Не удалось распознать счёт.';

                        if (payload.errors && payload.errors.invoice_file) {
                            message = payload.errors.invoice_file[0];
                        }

                        showRecognitionResult(message, true, false);
                    }).always(function () {
                        button.prop('disabled', false);
                        button.find('i').removeClass('fa-spinner fa-spin').addClass('fa-magic');
                    });
                });

                initializeTable(tableAsset, '#toolbar_asset', [
                    tableColumn('id', '#', 'left'),
                    tableColumn('model', labels.model, 'left'),
                    tableColumn('location', labels.location),
                    tableColumn('purchase_cost', labels.purchaseCost),
                    tableColumn('nds', labels.nds),
                    tableColumn('quantity', labels.quantity)
                ], 'asset');
                initializeTable(tableConsumables, '#toolbar_consumables', [
                    tableColumn('id', '#', 'left'),
                    tableColumn('consumable', labels.model, 'left'),
                    tableColumn('purchase_cost', labels.purchaseCost),
                    tableColumn('nds', labels.nds),
                    tableColumn('quantity', labels.quantity)
                ], 'consumable');

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

                editItemModal.on('change', '#edit_model_id, #edit_consumable_id', function () {
                    updateNewItemFieldsVisibility();
                    setEditorError('');
                });

                $('#savePurchaseItemButton').on('click', function () {
                    if (!editingItem) {
                        return;
                    }

                    const rows = editingItem.table.bootstrapTable('getData');
                    const index = rows.findIndex(function (row) {
                        return String(row.id) === String(editingItem.rowId);
                    });
                    if (index < 0) {
                        setEditorError('Строка больше не существует.');
                        return;
                    }

                    const updated = editorValues(editingItem.itemType, rows[index]);
                    if (!updated) {
                        return;
                    }

                    editingItem.table.bootstrapTable('updateRow', {
                        index: index,
                        row: updated
                    });
                    editItemModal.modal('hide');
                });

                editItemModal.on('hidden.bs.modal', function () {
                    editingItem = null;
                    setEditorError('');
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
