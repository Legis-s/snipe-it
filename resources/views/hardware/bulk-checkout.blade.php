<?php $item = new \App\Models\MassOperation() ?>

@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Массовая выдача
    @parent
@stop

{{-- Page content --}}
@section('content')
    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>
    <style>
        .input-group {
            padding-left: 0px !important;
        }
    </style>


    <div class="row">
        <!-- left column -->
        <div class="col-md-9">
            <div class="box box-default">
                <div class="box-header with-border">
                </div>
                <form class="form-horizontal" method="post" action="" autocomplete="off">
                    <div class="box-body">
                        {{ csrf_field() }}

                        <!-- Checkout selector -->
                        @include ('partials.forms.checkout-selector', ['user_select' => 'true','asset_select' => 'true', 'location_select' => 'true'])

                        @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'assigned_location', 'required'=>'true'])
                        @include ('partials.forms.edit.user-select',     ['translated_name' => trans('general.user'),     'fieldname' => 'assigned_user', 'unselect' => 'true', 'style' => 'display:none;', 'required'=>'true'])
                        @include ('partials.forms.edit.asset-select',    ['translated_name' => trans('general.asset'),    'fieldname' => 'assigned_asset', 'unselect' => 'true', 'style' => 'display:none;', 'required'=>'true'])

                        {{--                        Bitrix task id--}}
                        <div class="form-group {{ $errors->has('bitrix_task_id') ? 'error' : '' }}">
                            {{ Form::label('bitrix_task_id', trans('admin/hardware/form.bitrix_task_id'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <input type = 'text' class = 'form-control' name="bitrix_task_id">
                                {!! $errors->first('bitrix_task_id', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        <!-- Checkout/Checkin Date -->
                        <div class="form-group {{ $errors->has('checkout_at') ? 'error' : '' }}">
                            {{ Form::label('checkout_at', trans('admin/hardware/form.checkout_date'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <div class="input-group date col-md-5" data-provide="datepicker"
                                     data-date-format="yyyy-mm-dd" data-date-end-date="0d">
                                    <input type="text" class="form-control"
                                           placeholder="{{ trans('general.select_date') }}" name="checkout_at"
                                           id="checkout_at" value="{{ Input::old('checkout_at') }}">
                                    <span class="input-group-addon"><i class="fa fa-calendar"
                                                                       aria-hidden="true"></i></span>
                                </div>
                                {!! $errors->first('checkout_at', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        <!-- Expected Checkin Date -->
                        <div class="form-group {{ $errors->has('expected_checkin') ? 'error' : '' }}">
                            {{ Form::label('expected_checkin', trans('admin/hardware/form.expected_checkin'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <div class="input-group date col-md-5" data-provide="datepicker"
                                     data-date-format="yyyy-mm-dd" data-date-start-date="0d">
                                    <input type="text" class="form-control"
                                           placeholder="{{ trans('general.select_date') }}" name="expected_checkin"
                                           id="expected_checkin" value="{{ Input::old('expected_checkin') }}">
                                    <span class="input-group-addon"><i class="fa fa-calendar"
                                                                       aria-hidden="true"></i></span>
                                </div>
                                {!! $errors->first('expected_checkin', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>


                        <!-- Note -->
                        <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
                            {{ Form::label('note', trans('admin/hardware/form.notes'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                                        <textarea class="col-md-6 form-control" id="note"
                                                                  name="note">{{ Input::old('note') }}</textarea>
                                {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        <h2>Активы</h2>
                        <!-- We have to pass unselect here so that we don't default to the asset that's being checked out. We want that asset to be pre-selected everywhere else. -->
                        @include ('partials.forms.edit.asset-select-bulk-form', [
                                    'translated_name' => trans('general.asset'),
                                    'fieldname' => 'asset_select',
                                     'unselect' => 'true',
                                     'asset_status_type' => 'RTD',
                                     'select_id' => 'asset_select',
                                     ])



                        <select id="assigned_assets_select" name="selected_assets[]" multiple hidden>
                            @foreach ($ids as $a_id)
                            <option value = '{{ $a_id }}' selected="selected">{{ $a_id }}</option>
                            @endforeach
                        </select>

                        <select id="assigned_consumables_select" name="selected_consumables[]" multiple hidden>
{{--                            @foreach ($ids as $a_id)--}}
{{--                                <option value = '{{ $a_id }}' selected="selected">{{ $a_id }}</option>--}}
{{--                            @endforeach--}}
                        </select>

                        <table
                                data-advanced-search="true"
                                data-click-to-select="true"
                                data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayoutBulk() }}"
                                data-cookie-id-table="assetsBulkTable"
                                data-pagination="true"
                                data-id-table="assetsBulkTable"
                                data-search="false"
                                {% if ids is defined %}
                                data-query-params="queryParams"
                                {% endif %}
                                data-side-pagination="server"
                                data-show-columns="true"
                                data-show-footer="true"
                                data-show-refresh="true"
                                data-sort-order="asc"
                                data-sort-name="name"
                                data-toolbar="#toolbar"
                                data-queryParams="#toolbar"
                                id="assetsBulkTable"
                                class="table table-striped snipe-table"
                                data-url="{{ route('api.assets.index',array('bulk' => true ))}}">
                        </table>
                        <h2>Расходники</h2>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="table table-responsive">
                                    <div id="toolbar_consumables">
                                        <button type="button" class="btn btn-success" data-toggle="modal" data-target="#modal_consumables">
                                            Добавить расходник
                                        </button>
                                    </div>
                                    <p class="activ text-center text-bold text-danger hidden">Добавьте хотя бы один расходник</p>
                                    <table id="table_consumables" class="table table-striped snipe-table">
                                        <thead>
                                        <th>#</th>
                                        <th>Модель</th>
                                        <th>Количество</th>
                                        <th>Удалить</th>
                                        </thead>
                                    </table>
                                </div><!-- /.table-responsive -->
                            </div>
                        </div>
                    </div> <!--./box-body-->
                    <div class="box-footer">
                        <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
                        <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-check icon-white"
                                                                                    aria-hidden="true"></i> {{ trans('general.checkout') }}
                        </button>
                    </div>
                </form>
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
                                    @include ('partials.forms.edit.consumables-select', ['translated_name' => 'Название', 'fieldname' => 'consumable_id', 'required' => 'true'])
                                    <p class="duble text-center text-bold text-danger hidden">Такая модель уже есть</p>
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
        @stop

        @section('moar_scripts')
            @include('partials.bootstrap-table')
            @include('partials/assets-assigned')
            <script src="{{ asset('js/onscan.js') }}"></script>
            <script nonce="{{ csrf_token() }}">
                var select = $('#asset_select');
                var add_asset = $('#add_asset');
                var assigned_assets_select = $('#assigned_assets_select');
                var selected = [];
                Array.from(document.getElementById("assigned_assets_select")).forEach(function(item) {
                    selected.push(item.innerHTML);
                })

                function queryParams(params) {
                    params.data = JSON.stringify(selected);
                    return params
                }

                updData();
                add_asset.prop('disabled', true);
                add_asset.click(function () {
                    var selected_id = select.val();
                    if (!selected.includes(selected_id)) {
                        selected.push(selected_id);
                    }
                    updData();
                    select.val(null).trigger('change');
                    add_asset.prop('disabled', true);
                });

                select.on('select2:select', function (e) {
                    // var data = e.params.data;
                    // selected.push(data.id)
                    // updData();
                    add_asset.prop('disabled', false);
                });
                select.on('select2:unselect', function (e) {
                    // var data = e.params.data;
                    // console.log("select2:unselect");
                    // console.log(data);
                    // selected = selected.filter(item => item !== parseInt(data.id))
                    // updData();
                    add_asset.prop('disabled', true);
                });
                // select.on('select2:clear', function (e) {
                //     // var data = e.params.data;
                //     // console.log("select2:clear");
                //     // console.log(data);
                //     selected = [];
                //     updData();
                // });

                function updData() {
                    // console.log("upd");
                    console.log(selected);
                    $("#assetsBulkTable").bootstrapTable("refresh", {
                        "query": {
                            "data": JSON.stringify(selected),
                        },
                        "silent": true,
                    });
                    $('#assigned_assets_select').empty();
                    $.each(selected, function (i, item) {

                        $('#assigned_assets_select').append($('<option>', {
                            value: item,
                            text: item,
                            selected: true
                        }));
                    });

                }

                updData();

                // Enable scan events for the entire document
                onScan.attachTo(document, {
                    suffixKeyCodes: [13], // enter-key expected at the end of a scan
                    // reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
                    minLength:4,
                    onScan: function (sCode, iQty) { // Alternative to document.addEventListener('scan')
                        console.log("sCode"+sCode);
                        scanCode(auto_layout_keyboard(String(sCode)));

                    },
                    // onKeyDetect: function(iKeyCode){ // output all potentially relevant key events - great for debugging!
                    //     console.log('Pressed: ' + iKeyCode);
                    // }
                });

                function scanCode(code) {
                    console.log("scan: " + code);
                    ///hardware/bytag/:asset_tag
                    $.ajax({
                        type: 'GET',
                        url: '/api/v1/hardware/bytag/' + code,
                        headers: {
                            "X-Requested-With": 'XMLHttpRequest',
                            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                        },

                        dataType: 'json',
                        success: function (data, textStatus, xhr) {
                            console.log(data);
                            if (xhr.status === 200) {

                                if (data.hasOwnProperty("messages")){
                                    Swal.fire({
                                        title: "Актив "+ code+" не найден",
                                        icon: 'info',
                                        text: data.messages,
                                        timer: 1500
                                    });
                                }else{

                                    if (data.user_can_checkout){
                                        if (!selected.includes(data.id)) {
                                            selected.push(data.id);
                                            updData();
                                            Swal.fire({
                                                title: "Актив "+ code+" найден",
                                                icon: 'success',
                                                text: data.messages,
                                                timer: 1500
                                            });
                                        }else {
                                            Swal.fire({
                                                title: "Актив "+ code+" уже в списке",
                                                icon: 'info',
                                                text: data.messages,
                                                timer: 1500
                                            });
                                        }

                                    }else{
                                        Swal.fire({
                                            title: "Актив "+ code+" найден",
                                            icon: 'info',
                                            text: "Этот актив уже выдан, верните его на склад и попробуйте заново",
                                            timer: 2000
                                        });
                                    }

                                }
                            } else {
                                Swal.fire({
                                    title: "Ошибка сервера",
                                    icon: 'error',
                                    text: 'Что то пошло не так!',
                                    timer: 1500
                                })
                            }
                        },
                        error: function (data) {
                            Swal.fire({
                                title: "Ошибка сервера",
                                icon: 'error',
                                text: 'Что то пошло не так!',
                                timer: 1500
                            })
                        }
                    });
                }
                function auto_layout_keyboard(str) {
                    var replacer = {
                        "й": "q", "ц": "w", "у": "e", "к": "r", "е": "t", "н": "y", "г": "u",
                        "ш": "i", "щ": "o", "з": "p", "х": "[", "ъ": "]", "ф": "a", "ы": "s",
                        "в": "d", "а": "f", "п": "g", "р": "h", "о": "j", "л": "k", "д": "l",
                        "ж": ";", "э": "'", "я": "z", "ч": "x", "с": "c", "м": "v", "и": "b",
                        "т": "n", "ь": "m", "б": ",", "ю": ".", ".": "/"
                    };

                    return str.replace(/[А-я/,.;\'\]\[]/g, function (x) {
                        return x == x.toLowerCase() ? replacer[x] : replacer[x.toLowerCase()].toUpperCase();
                    });
                }

                var operateEventsBulk = {
                    'click .bulk-clear':function (e, value, row, index) {
                        var selected_id =String(row.id);
                        console.log(selected_id);
                        console.log(selected);
                        if (selected.includes(selected_id)) {
                            selected = selected.filter(item => item !== selected_id)
                            // selected.remove(selected_id);
                            updData();
                        }
                    }
                };
            </script>
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
                    $('#modal_consumables').on("show.bs.modal", function (event) {
                        var modal = $(this);
                        modal.find('#name').val("");
                        modal.find("#model_id").removeClass("has-error");
                        modal.find("#model_select_id").val('');
                        modal.find('#model_select_id').trigger('change');
                        modal.find('#model_number').val('');
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
                    $('#addСonsumablesButton').click(function (e) {
                        e.preventDefault();
                        var modal = $('#modal_consumables');
                        var consumable_id = modal.find('select[name=consumable_id] option').filter(':selected').val();
                        var consumable_name = modal.find('select[name=consumable_id] option').filter(':selected').text();
                        var quantity = modal.find('#quantity').val();

                        var tabele_data = table_consumables.bootstrapTable('getData');
                        if (consumable_id > 0) {
                            var data = {
                                id: tabele_data.length + 1,
                                consumable_id: consumable_id,
                                consumable: consumable_name,
                                quantity: quantity,
                                check: false,
                                status: "На согласовании",
                            };
                            $('#assigned_consumables_select').append($('<option>', {
                                value: consumable_id + ":" + quantity,
                                text: consumable_id + ":" + quantity,
                                selected: "selected"
                            }));
                            table_consumables.bootstrapTable('append', data);
                            // console.log( Array.from(table_consumables.bootstrapTable('getData'))[0]);
                            // Array.from(table_consumables.bootstrapTable('getData')).forEach(function(item) {
                            //     selected.push(item['consumable_id']);
                            // })

                            $('#modal_consumables').modal('hide');
                        } else {
                            modal.find("#category_id").addClass("has-error");
                            modal.find("#name").addClass("has-error");
                        }
                    });

                });
            </script>

@stop