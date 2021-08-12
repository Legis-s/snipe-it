@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Расходник:   {{ $consumable->name }}
    @parent
@stop

@section('header_right')
    <a href="{{ URL::previous() }}" class="btn btn-primary pull-right">
        {{ trans('general.back') }}</a>
@stop


{{-- Page content --}}
@section('content')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/sweetalert2.min.css') }}">
    <style>
        .select2-container--open {
            z-index: 99999999999999;
        }
    </style>
    <div class="row">
        <div class="col-md-9">
            <div class="box box-default">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table table-responsive">
                                <div id="toolbar">
                                    @if (($consumable->numRemaining() > 0))
                                        <a href="{{ url('/') }}/consumables/{{$consumable->id}}/checkout/" class="btn btn-sm bg-maroon" data-tooltip="true" title="Sell this item out">Выдать</a>
                                        <a href="{{ url('/') }}/consumables/{{$consumable->id}}/sell/" class="btn btn-sm bg-maroon" data-tooltip="true" title="Sell this item out">Продать</a>
                                    @else
                                        <a href="{{ url('/') }}/consumables/{{$consumable->id}}/checkout/" class="btn btn-sm bg-maroon" data-tooltip="true" title="Sell this item out" disabled>Выдать</a>
                                        <a href="{{ url('/') }}/consumables/{{$consumable->id}}/sell/" class="btn btn-sm bg-maroon" data-tooltip="true" title="Sell this item out" disabled>Продать</a>
                                    @endif
                                    <span class="text-primary" style="font-size: 130%">
                                        Всего: <b>{{$consumable->qty}}</b>
                                    Остаток: <b>{{$consumable->numRemaining()}}</b>
                                    </span>

                                </div>
                                <table
                                        data-columns="{{ \App\Presenters\ConsumableAssignmentPresenter::dataTableLayout() }}"
                                        data-cookie-id-table="consumablesCheckedoutTable"
                                        data-toolbar="#toolbar"
                                        data-pagination="true"
                                        data-id-table="consumablesCheckedoutTable"
                                        data-search="true"
                                        data-side-pagination="server"
                                        data-show-columns="true"
                                        data-show-export="true"
                                        data-show-footer="true"
                                        data-show-refresh="true"
                                        data-sort-order="asc"
                                        data-sort-name="name"
                                        id="consumablesCheckedoutTable"
                                        class="table table-striped snipe-table"
                                        data-url="{{route('api.consumableassignments.index',['consumable_id' => $consumable->id])}}">
                                </table>
                            </div>
                        </div> <!-- /.col-md-12-->
                    </div>
                </div>

            </div> <!-- /.box.box-default-->
        </div> <!-- /.col-md-9-->
        <div class="col-md-3">

            @if ($consumable->image!='')
                <div class="col-md-12 text-center" style="padding-bottom: 15px;">
                    <a href="{{ app('consumables_upload_url') }}/{{ $consumable->image }}" data-toggle="lightbox"><img
                                src="{{ app('consumables_upload_url') }}/{{ $consumable->image }}"
                                class="img-responsive img-thumbnail" alt="{{ $consumable->name }}"></a>
                </div>
            @endif

            @if ($consumable->purchase_date)
                <div class="col-md-12" style="padding-bottom: 5px;">
                    <strong>{{ trans('general.purchase_date') }}: </strong>
                    {{ $consumable->purchase_date }}
                </div>
            @endif

            @if ($consumable->purchase_cost)
                <div class="col-md-12" style="padding-bottom: 5px;">
                    <strong>{{ trans('general.purchase_cost') }}:</strong>
                    {{ $snipeSettings->default_currency }}
                    {{ \App\Helpers\Helper::formatCurrencyOutput($consumable->purchase_cost) }}
                </div>
            @endif

            @if ($consumable->item_no)
                <div class="col-md-12" style="padding-bottom: 5px;">
                    <strong>{{ trans('admin/consumables/general.item_no') }}:</strong>
                    {{ $consumable->item_no }}
                </div>
            @endif

            @if ($consumable->model_number)
                <div class="col-md-12" style="padding-bottom: 5px;">
                    <strong>{{ trans('general.model_no') }}:</strong>
                    {{ $consumable->model_number }}
                </div>
            @endif

            @if ($consumable->manufacturer)
                <div class="col-md-12" style="padding-bottom: 5px;">
                    <strong>{{ trans('general.manufacturer') }}:</strong>
                    {{ $consumable->manufacturer->name }}
                </div>
            @endif

            @if ($consumable->order_number)
                <div class="col-md-12" style="padding-bottom: 5px;">
                    <strong>{{ trans('general.order_number') }}:</strong>
                    {{ $consumable->order_number }}
                </div>
            @endif
            <div class="col-md-12" style="padding-bottom: 5px;">
                <h2>{{ trans('admin/consumables/general.about_consumables_title') }}</h2>
                <p>{{ trans('admin/consumables/general.about_consumables_text') }} </p>
            </div>
        </div> <!-- /.col-md-3-->
    </div> <!-- /.row-->

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', ['exportFile' => 'consumable' . $consumable->name . '-export', 'search' => false])
    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>
    <script nonce="{{ csrf_token() }}">
        $(function () {
            var table = $('#consumablesCheckedoutTable');
            window.operateEvents = {
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
                                    table.bootstrapTable('refresh');
                                },
                            });
                        }
                    });
                },
                'click .close_documents': function (e, value, row, index) {
                    console.log("test");
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
                            console.log(contract_id);
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
                                    table.bootstrapTable('refresh');
                                },
                            });
                        }
                    });
                }
            }
        });
    </script>
@stop
