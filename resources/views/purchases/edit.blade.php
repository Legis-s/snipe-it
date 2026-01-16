@extends('layouts/edit-form', [
    'createText' =>"Создать закупку" ,
    'updateText' => "Обновить закупку",
    'topSubmit' => false,
   'formAction' =>  route('purchases.store'),
])


{{-- Page content --}}
@section('inputFields')

    <!-- invoice_number -->
    <div class="form-group {{ $errors->has('invoice_number') ? ' has-error' : '' }}">
        <label for="name" class="col-md-3 control-label">Название</label>
        <div class="col-md-7 col-sm-12{{  (Helper::checkIfRequired($item, 'invoice_number')) ? ' required' : '' }}">
            <input class="form-control" type="text" name="invoice_number" aria-label="invoice_number"
                   id="invoice_number"
                   value="{{ old('invoice_number', $item->invoice_number) }}"{!!  (Helper::checkIfRequired($item, 'invoice_number')) ? ' data-validation="required" required' : '' !!} />
            {!! $errors->first('invoice_number', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- final_price -->
    <div class="form-group {{ $errors->has('final_price') ? ' has-error' : '' }}">
        <label for="final_price" class="col-md-3 control-label">Стоимость</label>
        <div class="col-md-7 col-sm-12{{  (Helper::checkIfRequired($item, 'final_price')) ? ' required' : '' }}">
            <input class="form-control float" type="text" name="final_price" aria-label="final_price" id="final_price"
                   value="{{ old('final_price', $item->final_price) }}"{!!  (Helper::checkIfRequired($item, 'final_price')) ? ' data-validation="required" required' : '' !!} />
            {!! $errors->first('final_price', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- delivery_cost -->
    <div class="form-group {{ $errors->has('delivery_cost') ? ' has-error' : '' }}">
        <label for="delivery_cost" class="col-md-3 control-label">Стоимость доставки</label>
        <div class="col-md-7 col-sm-12{{  (Helper::checkIfRequired($item, 'delivery_cost')) ? ' required' : '' }}">
            <input class="form-control float" type="text" name="delivery_cost" aria-label="delivery_cost"
                   id="delivery_cost"
                   value="{{ old('delivery_cost', $item->delivery_cost) }}"{!!  (Helper::checkIfRequired($item, 'delivery_cost')) ? ' data-validation="required" required' : '' !!} />
            {!! $errors->first('delivery_cost', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

    <!-- comment -->
    <div class="form-group{{ $errors->has('comment') ? ' has-error' : '' }}">
        <label for="comment" class="col-md-3 control-label">Комментарий</label>
        <div class="col-md-7 col-sm-12">
            <textarea class="col-md-6 form-control" id="comment" aria-label="comment" name="comment"
                      style="min-width:100%;">{{ old('notes', (isset($item) ? $item->comment : '')) }}</textarea>
            {!! $errors->first('comment', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>



    @include ('partials.forms.edit.supplier-select', ['translated_name' => trans('general.supplier'), 'fieldname' => 'supplier_id','required'=>true, 'hide_new'=>true ])

    @include ('partials.forms.purchases.invoice-type-select', ['translated_name' => "Тип счета", 'fieldname' => 'invoice_type_id','required'=>true])

    @include ('partials.forms.purchases.legal_person-select', ['translated_name' => "Юр. лицо", 'fieldname' => 'legal_person_id','required'=>true])

    @include ('partials.forms.purchases.invoice_file', ['required'=>true,])

    <input type="hidden" id="assets" name="assets" required value="{{ old('assets_json', $item->assets_json) }}">
    <input type="hidden" id="consumables" required name="consumables"
           value="{{ old('consumables_json', $item->consumables_json) }}">

    <div class="row">
        <div class="col-md-12">
            <div class="table table-responsive">
                <div id="toolbar_asset">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_asset">
                        Добавить актив
                    </button>
                </div>
                <p class="activ text-center text-bold text-danger hidden">Добавте хотя бы один актив</p>
                <table id="table_asset" class="table table-striped snipe-table">
                    <thead>
                    <th>#</th>
                    <th>Модель</th>
                    <th>Склад</th>
                    <th>Закупочная цена</th>
                    <th>НДС</th>
                    <th>Количество</th>
                    <th>Удалить</th>
                    </thead>
                </table>
            </div><!-- /.table-responsive -->
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="table table-responsive">
                <div id="toolbar_consumables">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal_consumables">
                        Добавить расходник
                    </button>
                </div>
                <p class="activ text-center text-bold text-danger hidden">Добавте хотя бы один расходник</p>
                <table id="table_consumables" class="table table-striped snipe-table">
                    <thead>
                    <th>#</th>
                    <th>Модель</th>
                    <th>Закупочная цена</th>
                    <th>НДС</th>
                    <th>Количество</th>
                    <th>Удалить</th>
                    </thead>
                </table>
            </div><!-- /.table-responsive -->
        </div>
    </div>

@stop

@section('content')
    @parent
    <!-- Modal Актив -->
    <div class="modal fade" id="modal_asset" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Добавить актив</h4>
                </div>
                <div class="modal-body2">
                    <form class="form-horizontal">

                        @include ('partials.forms.edit.model-select', ['translated_name' => trans('admin/hardware/form.model'), 'fieldname' => 'model_id', 'field_req' => true])

                        <p class="duble text-center text-bold text-danger hidden">Такая модель уже есть</p>
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
                            <label for="nds" class="col-md-3 control-label">НДС</label>
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
                                    <input class="form-control" type="number" min="0" name="quantity" aria-label="quantity" id="quantity" value="1"/>
                                </div>
                                <div class="col-md-4" style="padding-left: 0px;">
                                    {!! $errors->first('qty', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                </div>
                            </div>
                        </div>

                        @if(Auth::user()->favoriteLocation)
                            @include ('partials.forms.purchases.location-select-checkin', ['translated_name' =>"Ожидаемый склад", 'fieldname' => 'location_id','hide_new'=>true])
                        @else
                            @include ('partials.forms.purchases.location-select', ['translated_name' =>"Ожидаемый склад", 'fieldname' => 'location_id','hide_new'=>true ])
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

    <!-- Modal Расходник -->
    <div class="modal fade" id="modal_consumables" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Добавить расходник</h4>
                </div>
                <div class="modal-body2">
                    <div class="row">
                        <div class="col-md-12">
                            <form class="form-horizontal">
                                @include ('partials.forms.purchases.consumables-select', ['translated_name' => 'Название', 'fieldname' => 'consumable_id', 'required' => 'true'])
                                <p class="duble text-center text-bold text-danger hidden">Такая модель уже есть</p>
                                @include ('partials.forms.purchases.purchase_cost')
                                @include ('partials.forms.purchases.nds')
                                @include ('partials.forms.purchases.quantity')
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary" id="addСonsumablesButton">Добавить</button>
                </div>
            </div>
        </div>
    </div>
    <style>
        .modal-body2 {
            position: relative;
            padding: 15px;
        }
    </style>
@stop

@if (!$item->id)
    @section('moar_scripts')
        @include ('partials.bootstrap-table')
        <script nonce="{{ csrf_token() }}">
            const table_asset = $('#table_asset');
            const table_consumables = $('#table_consumables');


            $(function () {
                const baseUrl = $('meta[name="baseUrl"]').attr('content');
                $('.js-data-no-ajax').each(function (i, item) {
                    const link = $(item);
                    link.select2();
                });

                $('input.float').on('input', function () {
                    this.value = this.value.replace(',', '.')
                    this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
                    // $(this).val() // get the current value of the input field.
                });
                //generate tables vith raw data
                table_asset.bootstrapTable('destroy').bootstrapTable({
                    locale: 'ru',
                    data: [],
                    search: true,
                    toolbar: '#toolbar_asset',
                    columns: [{
                        field: 'id',
                        name: '#',
                        align: 'left',
                        valign: 'middle'
                    }, {
                        field: 'model',
                        name: 'Модель',
                        align: 'left',
                        valign: 'middle'
                    }, {
                        field: 'location',
                        name: 'Склад',
                        align: 'center',
                        valign: 'middle'
                    }, {
                        field: 'purchase_cost',
                        name: 'Закупочная цена',
                        align: 'center',
                        valign: 'middle'
                    }, {
                        field: 'nds',
                        name: 'НДС',
                        align: 'center',
                        valign: 'middle'
                    }, {
                        field: 'quantity',
                        name: 'Количество',
                        align: 'center',
                        valign: 'middle'
                    }, {
                        align: 'center',
                        valign: 'middle',
                        events: {
                            'click .remove': function (e, value, row, index) {
                                table_asset.bootstrapTable('remove', {
                                    field: 'id',
                                    values: [row.id]
                                });
                                var data = table_asset.bootstrapTable('getData');
                                var newData = [];
                                var count = 0;
                                data.forEach(function callback(currentValue, index, array) {
                                    count++;
                                    currentValue.id = count;
                                    newData.push(currentValue);
                                });
                                table_asset.bootstrapTable('load', newData);
                            }
                        },
                        formatter: function (value, row, index) {
                            return [
                                '<a class="remove text-danger"  href="javascript:void(0)" title="Убрать">',
                                '<i class="remove fas fa-times fa-lg"></i>',
                                '</a>'
                            ].join('')
                        }
                    }]
                });
                if ($('#assets').val()) {
                    table_asset.bootstrapTable('load', JSON.parse($('#assets').val()));
                }
                table_consumables.bootstrapTable('destroy').bootstrapTable({
                    locale: 'ru',
                    data: [],
                    search: true,
                    toolbar: '#toolbar_consumables',
                    columns: [{
                        field: 'id',
                        name: '#',
                        align: 'left',
                        valign: 'middle'
                    }, {
                        field: 'consumable',
                        name: 'Модель',
                        align: 'left',
                        valign: 'middle'
                    }, {
                        field: 'purchase_cost',
                        name: 'Закупочная цена',
                        align: 'center',
                        valign: 'middle'
                    }, {
                        field: 'nds',
                        name: 'НДС',
                        align: 'center',
                        valign: 'middle'
                    }, {
                        field: 'quantity',
                        name: 'Количество',
                        align: 'center',
                        valign: 'middle'
                    }
                        , {
                            align: 'center',
                            valign: 'middle',
                            events: {
                                'click .remove': function (e, value, row, index) {
                                    table_consumables.bootstrapTable('remove', {
                                        field: 'id',
                                        values: [row.id]
                                    });
                                    var data = table_consumables.bootstrapTable('getData');
                                    var newData = [];
                                    var count = 0;
                                    data.forEach(function callback(currentValue, index, array) {
                                        count++;
                                        currentValue.id = count;
                                        newData.push(currentValue);
                                    });
                                    table_consumables.bootstrapTable('load', newData);
                                }
                            },
                            formatter: function (value, row, index) {
                                return [
                                    '<a class="remove text-danger"  href="javascript:void(0)" title="Убрать">',
                                    '<i class="remove fas fa-times fa-lg"></i>',
                                    '</a>'
                                ].join('')
                            }
                        }
                    ]
                });
                if ($('#consumables').val()) {
                    table_consumables.bootstrapTable('load', JSON.parse($('#consumables').val()));
                }

                $('#modal_asset').on("show.bs.modal", function (event) {
                    const modal = $(this);
                    modal.find("#model_id").removeClass("has-error");
                    modal.find("#model_select_id").val('');
                    modal.find('#model_select_id').trigger('change');
                    modal.find('#purchase_cost').val('');
                    modal.find('#nds').val(22);
                    modal.find('#warranty_months').val(12);
                    modal.find('#quantity').val(1);
                    modal.find('.duble').addClass('hidden');
                    modal.find('select').each(function (i, item) {
                        var link = $(item);
                        var endpoint = link.data("endpoint");
                        var select = link.data("select");
                        link.select2({
                            placeholder: '',
                            allowClear: true,
                            debug: true,
                            dropdownParent: modal,
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
                                        companyId: link.data("company-id"),
                                    };
                                    return data;
                                },
                                cache: true
                            },
                            templateResult: formatDatalistSafe,
                        });
                    });
                });

                $('#modal_consumables').on("show.bs.modal", function (event) {
                    var modal = $(this);
                    modal.find('#name').val("");
                    modal.find("#model_id").removeClass("has-error");
                    modal.find("#model_select_id").val('');
                    modal.find('#model_select_id').trigger('change');
                    modal.find('#model_number').val('');
                    modal.find('#purchase_cost').val('');
                    modal.find('#nds').val(22);
                    modal.find('#quantity').val(1);
                    modal.find('.duble').addClass('hidden');
                    modal.find('select').each(function (i, item) {
                        var link = $(item);
                        var endpoint = link.data("endpoint");
                        var select = link.data("select");
                        link.select2({
                            placeholder: '',
                            allowClear: true,
                            debug: true,
                            dropdownParent: modal,
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
                                        companyId: link.data("company-id"),
                                    };
                                    return data;
                                },
                                cache: true
                            },
                            templateResult: formatDatalistSafe,
                        });
                    });
                });

                $('#addAssetButton').click(function (e) {
                    e.preventDefault();
                    var modal = $('#modal_asset');
                    var model_id = modal.find('select[name=model_id] option').filter(':selected').val();
                    var model_name = modal.find('select[name=model_id] option').filter(':selected').text();
                    var location_id = modal.find('select[name=location_id] option').filter(':selected').val();
                    var location_name = modal.find('select[name=location_id] option').filter(':selected').text();
                    var purchase_cost = modal.find('#purchase_cost').val();
                    var nds = modal.find('#nds').val();
                    var warranty = modal.find('#warranty_months').val();
                    var quantity = modal.find('#quantity').val();
                    var table_data = table_asset.bootstrapTable('getData');
                    if (model_id > 0) {
                        var rez = true;
                        table_data.forEach(function callback(currentValue, index, array) {
                            if (currentValue.model_id == model_id) {
                                rez = false;
                            }
                        });
                        if (rez) {
                            var data = {
                                id: table_data.length + 1,
                                model_id: model_id,
                                model: model_name,
                                location_id: location_id,
                                location: location_name,
                                purchase_cost: purchase_cost,
                                nds: nds,
                                warranty: warranty,
                                quantity: quantity,
                            };
                            table_asset.bootstrapTable('append', data);
                            $('#modal_asset').modal('hide');
                            $('.duble').addClass('hidden');
                        } else {
                            $('.duble').removeClass('hidden');
                        }
                    } else {
                        modal.find("#model_id").addClass("has-error");
                    }
                });

                $('#addСonsumablesButton').click(function (e) {
                    e.preventDefault();
                    var modal = $('#modal_consumables');
                    var consumable_id = modal.find('select[name=consumable_id] option').filter(':selected').val();
                    var consumable_name = modal.find('select[name=consumable_id] option').filter(':selected').text();
                    // var location_id = modal.find('select[name=location_id] option').filter(':selected').val();
                    // var location_name = modal.find('select[name=location_id] option').filter(':selected').text();
                    var purchase_cost = modal.find('#purchase_cost').val();
                    var nds = modal.find('#nds').val();
                    var quantity = modal.find('#quantity').val();

                    var tabele_data = table_consumables.bootstrapTable('getData');
                    if (consumable_id > 0) {
                        var data = {
                            id: tabele_data.length + 1,
                            consumable_id: consumable_id,
                            consumable: consumable_name,
                            // location_id: location_id,
                            // location: location_name,
                            purchase_cost: purchase_cost,
                            nds: nds,
                            quantity: quantity,
                            check: false,
                            status: "На согласовании",
                        };
                        table_consumables.bootstrapTable('append', data);
                        $('#modal_consumables').modal('hide');
                    } else {
                        modal.find("#category_id").addClass("has-error");
                        modal.find("#name").addClass("has-error");
                    }
                });

                $("#create-form").on("submit", function () {
                    var data_asset = table_asset.bootstrapTable('getData');
                    var data_consumables = table_consumables.bootstrapTable('getData');
                    var check_name = false;
                    var check_final_price = false;
                    var check_comment = false;
                    var check_supplier_id = false;
                    var check_invoice_type_id = false;
                    var check_ilegal_person_id = false;
                    var check_uploadFile = false;
                    if ($('#invoice_number').val().length > 0) {
                        check_name = true;
                        $('#invoice_number').closest(".form-group").removeClass("has-error");
                    } else {
                        //has-error
                        $('#invoice_number').closest(".form-group").addClass("has-error");
                    }
                    if ($('#final_price').val().length > 0) {
                        check_final_price = true;
                        $('#final_price').closest(".form-group").removeClass("has-error");
                    } else {
                        //has-error
                        $('#final_price').closest(".form-group").addClass("has-error");
                    }
                    if ($('#comment').val().length > 0) {
                        check_comment = true;
                        $('#comment').closest(".form-group").removeClass("has-error");
                    } else {
                        //has-error
                        $('#comment').closest(".form-group").addClass("has-error");
                    }
                    if ($('select[name=supplier_id] option').filter(':selected').val().length > 0) {
                        check_supplier_id = true;
                        $('select[name=supplier_id]').closest(".form-group").removeClass("has-error");
                    } else {
                        //has-error
                        $('select[name=supplier_id]').closest(".form-group").addClass("has-error");
                    }
                    if ($('select[name=invoice_type_id] option').filter(':selected').val().length > 0) {
                        check_invoice_type_id = true;
                        $('select[name=invoice_type_id]').closest(".form-group").removeClass("has-error");
                    } else {
                        //has-error
                        $('select[name=invoice_type_id]').closest(".form-group").addClass("has-error");
                    }
                    if ($('select[name=legal_person_id] option').filter(':selected').val().length > 0) {
                        check_ilegal_person_id = true;
                        $('select[name=legal_person_id]').closest(".form-group").removeClass("has-error");
                    } else {
                        //has-error
                        $('select[name=legal_person_id]').closest(".form-group").addClass("has-error");
                    }

                    if ($('#uploadFile').get(0).files.length === 0) {
                        console.log("No files selected.");
                        $('#uploadFile').closest(".form-group").addClass("has-error");
                    } else {
                        check_uploadFile = true;
                        $('#uploadFile').closest(".form-group").removeClass("has-error");
                    }

                    if ((data_asset.length > 0 || data_consumables.length > 0) && check_name && check_final_price && check_comment && check_supplier_id && check_invoice_type_id && check_ilegal_person_id && check_uploadFile) {
                        $('#assets').val(JSON.stringify(data_asset));
                        $('#consumables').val(JSON.stringify(data_consumables));
                        $('.activ').addClass("hidden");
                        return true;
                    } else {
                        $('.activ').removeClass("hidden");
                        return false;
                    }
                })

                function formatDatalistSafe(datalist) {

                    if (datalist.loading) {
                        return $('<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Loading...');
                    }

                    var root_div = $("<div class='clearfix'>");
                    var left_pull = $("<div class='pull-left' style='padding-right: 10px;'>");
                    if (datalist.image) {
                        var inner_div = $("<div style='width: 20px;'>");
                        /******************************************************************
                         *
                         * We are specifically chosing empty alt-text below, because this
                         * image conveys no additional information, relative to the text
                         * that will *always* be there in any select2 list that is in use
                         * in Snipe-IT. If that changes, we would probably want to change
                         * some signatures of some functions, but right now, we don't want
                         * screen readers to say "HP SuperJet 5000, .... picture of HP
                         * SuperJet 5000..." and so on, for every single row in a list of
                         * assets or models or whatever.
                         *
                         *******************************************************************/
                        var img = $("<img src='' style='max-height: 20px; max-width: 20px;' alt=''>");
                        img.attr("src", datalist.image);
                        inner_div.append(img)
                    } else if (datalist.tag_color) {
                        var inner_div = $("<div style='width: 20px;'>");
                        var icon = $('<i class="fa-solid fa-square" style="font-size: 20px;" aria-hidden="true"></i>');
                        icon.css("color", datalist.tag_color);
                        inner_div.append(icon)
                    } else {
                        var inner_div = $("<div style='height: 20px; width: 20px;'></div>");
                    }
                    left_pull.append(inner_div);
                    root_div.append(left_pull);
                    var name_div = $("<div>");
                    name_div.text(datalist.text);
                    root_div.append(name_div)
                    var safe_html = root_div.get(0).outerHTML;
                    var old_html = formatDatalist(datalist);
                    if (safe_html != old_html) {
                        //console.log("HTML MISMATCH: ");
                        //console.log("FormatDatalistSafe: ");
                        // console.dir(root_div.get(0));
                        //console.log(safe_html);
                        //console.log("FormatDataList: ");
                        //console.log(old_html);
                    }
                    return root_div;

                }

                function formatDatalist(datalist) {
                    var loading_markup = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Loading...';
                    if (datalist.loading) {
                        return loading_markup;
                    }

                    var markup = '<div class="clearfix">';
                    markup += '<div class="pull-left" style="padding-right: 10px;">';
                    if (datalist.image) {
                        markup += "<div style='width: 30px;'><img src='" + datalist.image + "' style='max-height: 20px; max-width: 30px;' alt='" + datalist.text + "'></div>";
                    } else {
                        markup += '<div style="height: 20px; width: 30px;"></div>';
                    }

                    markup += "</div><div>" + datalist.text + "</div>";
                    markup += "</div>";
                    return markup;
                }

            });
        </script>
    @stop
@endif
