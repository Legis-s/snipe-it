@extends('layouts/default')

{{-- Page title --}}
@section('title')

    Массовая операция - {{ $massoperation->name }}

    @parent
@stop

@section('header_right')
    {{--<a href="{{ route('locations.edit', ['location' => $inventory->id]) }}" class="btn btn-sm btn-primary pull-right">{{ trans('admin/locations/table.update') }} </a>--}}
@stop

{{-- Page content --}}
@section('content')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/sweetalert2.min.css') }}">

    <div class="row">
        <div class="col-md-10">
            <div class="box box-default">
                <div class="box-header with-border">
                    <div class="box-heading">
                        <h2 class="box-title">Активы </h2>
                    </div>
                </div>
                <div class="box-body">
                    <div id="toolbar">
                    </div>
                    <div class="table table-responsive">
                        <select id="assigned_assets_select" name="selected_assets[]" multiple hidden>

                            @foreach ($assets as $asset)
                                <option value = '{{ $asset->id }}' selected="selected">{{ $asset->id }}</option>
                            @endforeach

                        </select>

                        <h2> Активы к продаже:</h2>
                        <table
                                data-advanced-search="true"
                                data-click-to-select="true"
                                data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayoutBulk() }}"
                                data-cookie-id-table="assetsBulkTable"
                                data-pagination="true"
                                data-id-table="assetsBulkTable"
                                {% if ids is defined %}
                                data-query-params="queryParams"
                                {% endif %}
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
                    </div><!-- /.table-responsive -->
                </div><!-- /.box-body -->
            </div> <!--/.box-->
        </div><!--/.col-md-9-->
        <div class="col-md-2">
            <div class="box box-default">
                <div class="box-header with-border">
                    <div class="box-heading">
                        <h2 class="box-title">Информация</h2>
                    </div>
                </div>
                <div class="box-body">
                    @if ($massoperation->name)
                        <div class="row">
                            <div class="col-md-6">
                                <strong>
                                    Название
                                </strong>
                            </div>
                            <div class="col-md-6">
                                {{ $massoperation->name }}
                            </div>
                        </div>
                    @endif


                    @if ($massoperation->note)
                        <div class="row">
                            <div class="col-md-6">
                                <strong>
                                    Комментарий
                                </strong>
                            </div>
                            <div class="col-md-12">
                                {{ $massoperation->note }}
                            </div>
                        </div>
                    @endif
                    @if ($massoperation->bitrix_task_id)
                        <div class="row">
                            <div class="col-md-6">
                                <strong>
                                    Задача
                                </strong>
                            </div>
                            <div class="col-md-6">
                                <a href="https://bitrix.legis-s.ru/company/personal/user/290/tasks/task/view/{{ $massoperation->bitrix_task_id }}/">{{ $purchase->bitrix_task_id }}</a>
                            </div>
                        </div>
                    @endif

                </div><!-- /.box-body -->
            </div> <!--/.box-->
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
        Array.from(document.getElementById("assigned_assets_select")).forEach(function(item) {
            selected.push(item.innerHTML);
        })
        function queryParams(params) {
            params.data = JSON.stringify(selected);
            return params
        }
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