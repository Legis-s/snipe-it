@extends('layouts/edit-form', [
   'createText' => "Создать закупку" ,
   'updateText' => "Обновить закупку",
   'formAction' => ($item) ? route('purchases.update', ['purchase' => $item->id]) : route('purchases.store'),
])

{{-- Page content --}}
@section('inputFields')

    @include ('partials.forms.edit.invoice_number', ['translated_name' => "Название"])

    @include ('partials.forms.edit.final_price', ['translated_name' => "Цена"])

    @include ('partials.forms.edit.delivery_cost', ['translated_name' => "Стоимость доставки"])

    @include ('partials.forms.edit.currency-select', ['translated_name' => "Валюта", 'fieldname' => 'currency_id'])

    @include ('partials.forms.edit.comment', ['translated_name' => "Комментарий"])

    @include ('partials.forms.edit.supplier-select', ['translated_name' => trans('general.supplier'), 'fieldname' => 'supplier_id'])

    @include ('partials.forms.edit.invoice-type-select', ['translated_name' => "Тип счета", 'fieldname' => 'invoice_type_id'])

    @include ('partials.forms.edit.legal_person-select', ['translated_name' => "Юр. лицо", 'fieldname' => 'legal_person_id'])

    @include ('partials.forms.edit.invoice_file')

    <input type="hidden" id="assets" name="assets" value="{{ Input::old('assets_json', $item->assets_json) }}">
    <input type="hidden" id="consumables" name="consumables"
           value="{{ Input::old('consumables_json', $item->consumables_json) }}">
    <input type="hidden" id="sales" name="sales" value="{{ Input::old('sales_json', $item->sales_json) }}">

    <div class="row">
        <div class="col-md-12">
            <div class="table table-responsive">
                <div id="toolbar_asset">
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal_asset">
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
                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal_consumables">
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

{{--    <div class="row">--}}
{{--        <div class="col-md-12">--}}
{{--            <div class="table table-responsive">--}}
{{--                <div id="toolbar_sales">--}}
{{--                    <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal_sales">--}}
{{--                        Добавить актив на продажу--}}
{{--                    </button>--}}
{{--                </div>--}}
{{--                <p class="activ text-center text-bold text-danger hidden">Добавте хотя бы один актив на проджажу</p>--}}
{{--                <table id="table_sales" class="table table-striped snipe-table">--}}
{{--                    <thead>--}}
{{--                    <th>#</th>--}}
{{--                    <th>Модель</th>--}}
{{--                    <th>Склад</th>--}}
{{--                    <th>Закупочная цена</th>--}}
{{--                    <th>НДС</th>--}}
{{--                    <th>Количество</th>--}}
{{--                    <th>Удалить</th>--}}
{{--                    </thead>--}}
{{--                </table>--}}
{{--            </div><!-- /.table-responsive -->--}}
{{--        </div>--}}
{{--    </div>--}}
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
                    <h4 class="modal-title" id="myModalLabel">Добавить</h4>
                </div>
                <div class="modal-body2">
                    <div class="row">
                        <div class="col-md-12">
                            <form class="form-horizontal">
                                @include ('partials.forms.edit.model-select2', ['translated_name' => trans('admin/hardware/form.model'), 'fieldname' => 'model_id', 'required' => 'true'])
                                <p class="duble text-center text-bold text-danger hidden">Такая модель уже есть</p>
                                @include ('partials.forms.edit.purchase_cost')
                                @include ('partials.forms.edit.nds')
                                @include ('partials.forms.edit.warranty')
                                @include ('partials.forms.edit.quantity')
                                @if(Auth::user()->favoriteLocation)
                                    @include ('partials.forms.edit.location-select-checkin', ['translated_name' =>"Ожидаемый склад", 'fieldname' => 'location_id'])
                                @else
                                    @include ('partials.forms.edit.location-select', ['translated_name' =>"Ожидаемый склад", 'fieldname' => 'location_id' ])
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary" id="addAssetButton">Добавить</button>
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
                    <h4 class="modal-title" id="myModalLabel">Добавить</h4>
                </div>
                <div class="modal-body2">
                    <div class="row">
                        <div class="col-md-12">
                            <form class="form-horizontal">
                                {{--                                @include ('partials.forms.edit.name', ['translated_name' => trans('admin/consumables/table.title')])--}}
                                @include ('partials.forms.edit.consumables-select', ['translated_name' => 'Название', 'fieldname' => 'consumable_id', 'required' => 'true'])
                                {{--                                @include ('partials.forms.edit.category-select2', ['translated_name' => trans('general.category'), 'fieldname' => 'category_id', 'required' => 'true', 'category_type' => 'consumable'])--}}
                                {{--                                <p class="duble text-center text-bold text-danger hidden">Такая категория уже есть</p>--}}
                                {{--                                @include ('partials.forms.edit.manufacturer-select2', ['translated_name' => trans('general.manufacturer'), 'fieldname' => 'manufacturer_id', 'required' => 'true'])--}}
                                {{--                                @include ('partials.forms.edit.model_number')--}}
                                <p class="duble text-center text-bold text-danger hidden">Такая модель уже есть</p>
                                @include ('partials.forms.edit.purchase_cost')
                                @include ('partials.forms.edit.nds')
                                @include ('partials.forms.edit.quantity')
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

    <!-- Modal Актив на продажу -->
    <div class="modal fade" id="modal_sales" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Добавить</h4>
                </div>
                <div class="modal-body2">
                    <div class="row">
                        <div class="col-md-12">
                            <form class="form-horizontal">
                                @include ('partials.forms.edit.model-select3', ['translated_name' => trans('admin/hardware/form.model'), 'fieldname' => 'model_id', 'required' => 'true'])
                                <p class="duble text-center text-bold text-danger hidden">Такая модель уже есть</p>
                                @include ('partials.forms.edit.purchase_cost')
                                @include ('partials.forms.edit.nds')
                                {{--                                @include ('partials.forms.edit.warranty')--}}
                                @include ('partials.forms.edit.quantity')
                                @if(Auth::user()->favoriteLocation)
                                    @include ('partials.forms.edit.location-select-checkin', ['translated_name' =>"Ожидаемый склад", 'fieldname' => 'location_id'])
                                @else
                                    @include ('partials.forms.edit.location-select', ['translated_name' =>"Ожидаемый склад", 'fieldname' => 'location_id' ])
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary" id="addSalesButton">Добавить</button>
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
        var table_asset = $('#table_asset');
        var table_consumables = $('#table_consumables');
        var table_sales = $('#table_sales');

        $(function () {

            //select2 for no ajax lists activate
            $('.js-data-no-ajax').each(function (i, item) {
                var link = $(item);
                link.select2();
            });
            $('input.float').on('input', function () {
                this.value = this.value.replace(',', '.')
                this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1');
                // $(this).val() // get the current value of the input field.
            });
            //generate tables vith raw data
            console.log($('#assets').val())
            console.log($('#consumables').val())
            console.log($('#sales').val())

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
                            '<i class="remove fa fa-times fa-lg"></i>',
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
                                '<i class="remove fa fa-times fa-lg"></i>',
                                '</a>'
                            ].join('')
                        }
                    }
                ]
            });
            if ($('#consumables').val()) {
                table_consumables.bootstrapTable('load', JSON.parse($('#consumables').val()));
            }
            table_sales.bootstrapTable('destroy').bootstrapTable({
                data: [],
                search: true,
                toolbar: '#toolbar_sales',
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
                            table_sales.bootstrapTable('remove', {
                                field: 'id',
                                values: [row.id]
                            });
                            var data = table_sales.bootstrapTable('getData');
                            var newData = [];
                            var count = 0;
                            data.forEach(function callback(currentValue, index, array) {
                                count++;
                                currentValue.id = count;
                                newData.push(currentValue);
                            });
                            table_sales.bootstrapTable('load', newData);
                        }
                    },
                    formatter: function (value, row, index) {
                        return [
                            '<a class="remove text-danger"  href="javascript:void(0)" title="Убрать">',
                            '<i class="remove fa fa-times fa-lg"></i>',
                            '</a>'
                        ].join('')
                    }
                }]
            });
            if ($('#sales').val()) {
                table_sales.bootstrapTable('load', JSON.parse($('#sales').val()));
            }
            $('#modal_asset').on("show.bs.modal", function (event) {
                var modal = $(this);
                modal.find("#model_id").removeClass("has-error");
                modal.find("#model_select_id").val('');
                modal.find('#model_select_id').trigger('change');
                modal.find('#purchase_cost').val('');
                modal.find('#nds').val(20);
                modal.find('#warranty_months').val(12);
                modal.find('#quantity').val(1);
                modal.find('.duble').addClass('hidden');
                modal.find('select').each(function (i, item) {
                    // $('.js-data-ajax2').each(function (i, item) {
                    var link = $(item);
                    var endpoint = link.data("endpoint");
                    if (link.hasClass("select2-hidden-accessible")) {
                        link.select2('destroy');
                    }
                    link.select2({
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
                                };
                                return data;
                            },
                            processResults: function (data, params) {
                                console.log(data)
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
            });
            $('#modal_consumables').on("show.bs.modal", function (event) {
                var modal = $(this);
                modal.find('#name').val("");
                modal.find("#model_id").removeClass("has-error");
                modal.find("#model_select_id").val('');
                modal.find('#model_select_id').trigger('change');
                modal.find('#model_number').val('');
                modal.find('#purchase_cost').val('');
                modal.find('#nds').val(20);
                modal.find('#quantity').val(1);
                modal.find('.duble').addClass('hidden');
                modal.find('select').each(function (i, item) {
                    // $('.js-data-ajax2').each(function (i, item) {
                    var link = $(item);
                    var endpoint = link.data("endpoint");
                    if (link.hasClass("select2-hidden-accessible")) {
                        link.select2('destroy');
                    }
                    link.select2({
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
                                };
                                return data;
                            },
                            processResults: function (data, params) {
                                console.log(data)
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
            });
            $('#modal_sales').on("show.bs.modal", function (event) {
                var modal = $(this);
                modal.find("#model_id").removeClass("has-error");
                modal.find("#model_select_id").val('');
                modal.find('#model_select_id').trigger('change');
                modal.find('#purchase_cost').val('');
                modal.find('#nds').val(20);
                modal.find('#warranty_months').val(12);
                modal.find('#quantity').val(1);
                modal.find('.duble').addClass('hidden');
                modal.find('select').each(function (i, item) {
                    // $('.js-data-ajax3').each(function (i, item) {
                    var link = $(item);
                    var endpoint = link.data("endpoint");
                    if (link.hasClass("select2-hidden-accessible")) {
                        link.select2('destroy');
                    }
                    link.select2({
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
                                };
                                return data;
                            },
                            processResults: function (data, params) {
                                console.log(data)
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
            });

            function formatDatalist(datalist) {
                var loading_markup = '<i class="fa fa-spinner fa-spin" aria-hidden="true"></i> Loading...';
                if (datalist.loading) {
                    return loading_markup;
                }

                var markup = "<div class='clearfix'>";
                markup += "<div class='pull-left' style='padding-right: 10px;'>";
                if (datalist.image) {
                    markup += "<div style='width: 30px;'><img src='" + datalist.image + "' alt='" + datalist.tex + "' style='max-height: 20px; max-width: 30px;'></div>";
                } else {
                    markup += "<div style='height: 20px; width: 30px;'></div>";
                }

                markup += "</div><div>" + datalist.text + "</div>";
                markup += "</div>";
                return markup;
            }

            function formatDataSelection(datalist) {
                return datalist.text.replace(/>/g, '&gt;')
                    .replace(/</g, '&lt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

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
                var purchase_cost = modal.find('#purchase_cost').val();
                var nds = modal.find('#nds').val();
                var quantity = modal.find('#quantity').val();

                var tabele_data = table_consumables.bootstrapTable('getData');
                if (consumable_id > 0) {
                    var data = {
                        id: tabele_data.length + 1,
                        consumable_id: consumable_id,
                        consumable: consumable_name,
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
            $('#addSalesButton').click(function (e) {
                e.preventDefault();
                var modal = $('#modal_sales');
                var model_id = modal.find('select[name=model_id] option').filter(':selected').val();
                var model_name = modal.find('select[name=model_id] option').filter(':selected').text();
                var purchase_cost = modal.find('#purchase_cost').val();
                var location_id = modal.find('select[name=location_id] option').filter(':selected').val();
                var location_name = modal.find('select[name=location_id] option').filter(':selected').text();
                var nds = modal.find('#nds').val();
                var quantity = modal.find('#quantity').val();
                var table_data = table_sales.bootstrapTable('getData');
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
                            // warranty: warranty,
                            quantity: quantity,
                        };
                        table_sales.bootstrapTable('append', data);
                        modal.modal('hide');
                        $('.duble').addClass('hidden');
                    } else {
                        $('.duble').removeClass('hidden');
                    }
                } else {
                    modal.find("#model_id").addClass("has-error");
                }
            });

            $("#create-form").on("submit", function () {
                var data_asset = table_asset.bootstrapTable('getData');
                var data_consumables = table_consumables.bootstrapTable('getData');
                var data_sales = table_sales.bootstrapTable('getData');
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

                if ((data_asset.length > 0 || data_consumables.length > 0 || data_sales.length > 0) && check_name && check_final_price && check_comment && check_supplier_id && check_invoice_type_id && check_ilegal_person_id && check_uploadFile) {
                    $('#assets').val(JSON.stringify(data_asset));
                    $('#consumables').val(JSON.stringify(data_consumables));
                    $('#sales').val(JSON.stringify(data_sales));
                    $('.activ').addClass("hidden");
                    return true;
                } else {
                    $('.activ').removeClass("hidden");
                    return false;
                }
            })
        });
    </script>
@stop
@endif
