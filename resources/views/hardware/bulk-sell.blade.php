@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Массовая продажа активов
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
                    <h2 class="box-title"> Массовая продажа активов </h2>
                </div>
                <form class="form-horizontal" method="post" action="" autocomplete="off">
                    <div class="box-body">
                        {{ csrf_field() }}

                        <!-- Checkout selector -->
                        @include ('partials.forms.sellbulk-selector', ['user_select' => 'true','contract_select' => 'true'])
                        @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.user'), 'fieldname' => 'assigned_user', 'hide_new' => 'true', 'required'=>'true'])
                        @include ('partials.forms.edit.contract-id-select', ['translated_name' => "Договор", 'fieldname' => 'assigned_contract', 'unselect' => 'true', 'required'=>'true'])

                        {{--                        Bitrix task id--}}
                        <div class="form-group {{ $errors->has('bitrix_task_id') ? 'error' : '' }}">
                            {{ Form::label('bitrix_task_id', trans('admin/hardware/form.bitrix_task_id'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <input type = 'text' class = 'form-control' name="bitrix_task_id">
                                {!! $errors->first('bitrix_task_id', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        <!-- sold_at date -->
                        <div class="form-group {{ $errors->has('sold_at') ? 'error' : '' }}">
                            {{ Form::label('sold_at', trans('admin/hardware/form.sell_date'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <div class="input-group date col-md-5" data-provide="datepicker"
                                     data-date-format="yyyy-mm-dd" data-date-end-date="0d">
                                    <input type="text" class="form-control"
                                           placeholder="{{ trans('general.select_date') }}" name="sold_at"
                                           id="checkout_at" value="{{ Input::old('sold_at') }}">
                                    <span class="input-group-addon"><i class="fa fa-calendar"
                                                                       aria-hidden="true"></i></span>
                                </div>
                                {!! $errors->first('sold_at', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
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
                        <br>
                        <br>
                        @include ('partials.forms.edit.asset-select-bulk-form', [
                                    'translated_name' => trans('general.asset'),
                                    'fieldname' => 'asset_select',
                                    'unselect' => 'true',
                                    'asset_status_type' => 'RTD',
                                    'select_id' => 'asset_select',
                                     ])

                        <select id="assigned_assets_select" name="selected_assets[]" multiple hidden></select>

                        <h2> Активы к продаже:</h2>
                        <table
                                data-advanced-search="true"
                                data-click-to-select="true"
                                data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayoutBulk() }}"
                                data-cookie-id-table="assetsBulkTable"
                                data-pagination="true"
                                data-id-table="assetsBulkTable"
                                data-search="true"
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

                    </div>
                    <div class="box-footer">
                        <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
                        <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-check icon-white"
                                                                                    aria-hidden="true"></i> {{ trans('general.sell') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
        @stop

        @section('moar_scripts')
            @include('partials.bootstrap-table')
            @include('partials/assets-assigned')
            <script src="{{ asset('js/onscan.js') }}"></script>
            <script nonce="{{ csrf_token() }}">
                $(function () {
                    $('input[name=sell_to_type]').on("change", function () {
                        var assignto_type = $('input[name=sell_to_type]:checked').val();

                        if (assignto_type == 'contract') {
                            $('#assigned_user').hide();
                        } else if (assignto_type == 'user') {
                            $('#assigned_user').show();
                        }
                    });
                });

                var select = $('#asset_select');
                var add_asset = $('#add_asset');
                var assigned_assets_select = $('#assigned_assets_select');
                var selected = [];

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
                    add_asset.prop('disabled', false);
                });
                select.on('select2:unselect', function (e) {
                    add_asset.prop('disabled', true);
                });

                function updData() {
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

                // Enable scan events for the entire document
                onScan.attachTo(document, {
                    suffixKeyCodes: [13], // enter-key expected at the end of a scan
                    minLength:4,
                    onScan: function (sCode, iQty) { // Alternative to document.addEventListener('scan')
                        console.log("sCode" + sCode);
                        scanCode(auto_layout_keyboard(String(sCode)));

                    },
                });

                function scanCode(code) {
                    console.log("scan: " + code);
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
                                        title: "Актив " + code + " не найден",
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
                                                title: "Актив " + code + " найден",
                                                icon: 'success',
                                                text: data.messages,
                                                timer: 1500
                                            });
                                        }else {
                                            Swal.fire({
                                                title: "Актив " + code + " уже в списке",
                                                icon: 'info',
                                                text: data.messages,
                                                timer: 1500
                                            });
                                        }

                                    }else{
                                        Swal.fire({
                                            title: "Актив " + code + " найден",
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
                            updData();
                        }
                    }
                };
            </script>
@stop