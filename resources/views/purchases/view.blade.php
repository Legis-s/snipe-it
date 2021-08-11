@extends('layouts/default')

{{-- Page title --}}
@section('title')

 Закупка - {{ $purchase->invoice_number }}
 
@parent
@stop

@section('header_right')
{{--<a href="{{ route('locations.edit', ['location' => $inventory->id]) }}" class="btn btn-sm btn-primary pull-right">{{ trans('admin/locations/table.update') }} </a>--}}
@stop

{{-- Page content --}}
@section('content')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/sweetalert2.min.css') }}">

<div class="row">
    <div class="col-md-8">
        <div class="box box-default">
            <div class="box-header with-border">
                <div class="box-heading">
                    <h2 class="box-title">Активы </h2>
                </div>
            </div>
            <div class="box-body">
            <div class="table table-responsive">
                <table
                        data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
{{--                        data-cookie-id-table="assetsListingTable"--}}
                        data-pagination="true"
                        data-id-table="assetsListingTable"
                        data-search="true"
                        data-side-pagination="server"
                        data-show-columns="true"
                        data-show-export="true"
                        data-show-refresh="true"
{{--                        data-sort-order="asc"--}}
                        id="assetsListingTable"
                        class="table table-striped snipe-table"
                        data-url="{{route('api.assets.index', ['purchase_id' => $purchase->id]) }}">
                </table>
            </div><!-- /.table-responsive -->
          </div><!-- /.box-body -->
        </div> <!--/.box-->
        @if ($purchase->status!='paid')
            <div class="box box-default">
                <div class="box-header with-border">
                    <div class="box-heading">
                        <h2 class="box-title">Расходники</h2>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table table-responsive">
                        <table id="table_consumables" class="table table-striped snipe-table">
                            @if($old)
                                <thead>
                                <th>#</th>
                                <th>Название</th>
                                <th>Производитель</th>
                                <th>Категория</th>
                                <th>Модель</th>
                                <th>Закупочная цена</th>
                                <th>НДС</th>
                                <th>Количество</th>
                                </thead>
                            @else
                                <thead>
                                <th>#</th>
                                <th>Модель</th>
                                <th>Закупочная цена</th>
                                <th>НДС</th>
                                <th>Количество</th>
                                <th>Принято</th>
                                @can('review', \App\Models\Asset::class)
                                <th>Принять</th>
                                @endcan
                                </thead>
                            @endif
                        </table>
                    </div><!-- /.table-responsive -->
                </div><!-- /.box-body -->
            </div> <!--/.box-->
        @else
{{--            <div class="box box-default">--}}
{{--                <div class="box-header with-border">--}}
{{--                    <div class="box-heading">--}}
{{--                        <h2 class="box-title">Расходники</h2>--}}
{{--                    </div>--}}
{{--                </div>--}}
{{--                <div class="box-body">--}}
{{--                    <div class="table table-responsive">--}}
{{--                        <table--}}
{{--                                data-columns="{{ \App\Presenters\ConsumablePresenter::dataTableLayout() }}"--}}
{{--                                data-cookie-id-table="consumablesTable"--}}
{{--                                data-pagination="true"--}}
{{--                                data-id-table="consumablesTable"--}}
{{--                                data-search="true"--}}
{{--                                data-side-pagination="server"--}}
{{--                                data-show-columns="true"--}}
{{--                                data-show-export="true"--}}
{{--                                data-shconsumablesTableow-footer="true"--}}
{{--                                data-show-refresh="true"--}}
{{--                                data-sort-order="asc"--}}
{{--                                data-sort-name="name"--}}
{{--                                data-toolbar="#toolbar"--}}
{{--                                id=""--}}
{{--                                class="table table-striped snipe-table"--}}
{{--                                data-url="{{ route('api.consumables.index', ['purchase_id' => $purchase->id]) }}">--}}
{{--                        </table>--}}
{{--                    </div><!-- /.table-responsive -->--}}
{{--                </div><!-- /.box-body -->--}}
{{--            </div> <!--/.box-->--}}
        @endif

        <div class="box box-default">
            <div class="box-header with-border">
                <div class="box-heading">
                    <h2 class="box-title">Активы на продажу</h2>
                </div>
            </div>
            <div class="box-body">
                <div class="table table-responsive">
                    <table
                            data-columns="{{ \App\Presenters\SalesPresenter::dataTableLayout() }}"
{{--                            data-cookie-id-table="salesListingTable"--}}
                            data-pagination="true"
                            data-id-table="salesListingTable"
                            data-search="true"
                            data-side-pagination="server"
                            data-show-columns="true"
                            data-show-export="true"
                            data-show-refresh="true"
{{--                            data-sort-order="asc"--}}
                            id="salesListingTable"
                            class="table table-striped snipe-table"
                            data-url="{{route('api.sales.index', ['purchase_id' => $purchase->id]) }}">
                    </table>
                </div><!-- /.table-responsive -->
            </div><!-- /.box-body -->
        </div> <!--/.box-->
    </div><!--/.col-md-9-->
    <div class="col-md-4">
        <div class="box box-default">
            <div class="box-header with-border">
                <div class="box-heading">
                    <h2 class="box-title">Информация</h2>
                </div>
            </div>
            <div class="box-body">
                @if ($purchase->invoice_number)
                    <div class="row">
                        <div class="col-md-6">
                            <strong>
                                Название
                            </strong>
                        </div>
                        <div class="col-md-6">
                            {{ $purchase->invoice_number }}
                        </div>
                    </div>
                @endif
                @if ($purchase->invoice_file)
                        <div class="row">
                            <div class="col-md-6">
                                <strong>
                                    Файл счета
                                </strong>
                            </div>
                            <div class="col-md-6">
                                <a href="/uploads/purchases/{{ $purchase->invoice_file }}">Скачать</a>
                            </div>
                        </div>
                    @endif
                    @if ($purchase->bitrix_id)
                        <div class="row">
                            <div class="col-md-6">
                                <strong>
                                    Bitrix id
                                </strong>
                            </div>
                            <div class="col-md-6">
                                <a href='https://bitrix.legis-s.ru/services/lists/52/element/0/{{ $purchase->bitrix_id }}/?list_section_id='>{{ $purchase->bitrix_id }}</a>
                            </div>
                        </div>
                    @endif
                    @if ($purchase->final_price)
                        <div class="row">
                            <div class="col-md-6">
                                <strong>
                                    Цена
                                </strong>
                            </div>
                            <div class="col-md-6">
                                {{ $purchase->final_price }}
                            </div>
                        </div>
                    @endif
                @if ($purchase->supplier)
                    <div class="row">
                        <div class="col-md-6">
                            <strong>
                                {{ trans('general.supplier') }}
                            </strong>
                        </div>
                        <div class="col-md-6">
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
                            <div class="col-md-6">
                                <strong>
                                    Оплачено
                                </strong>
                            </div>
                            <div class="col-md-6">
                                {{ $purchase->paid }}
                            </div>
                        </div>
                    @endif
                    @if ($purchase->comment)
                        <div class="row">
                            <div class="col-md-6">
                                <strong>
                                    Комментарий
                                </strong>
                            </div>
                            <div class="col-md-6">
                                {{ $purchase->comment }}
                            </div>
                        </div>
                    @endif
                    @if($old)
                        @can('review', \App\Models\Asset::class)
                            @if ($purchase->consumables_json != "[]" &&  count($purchase->consumables)==0)
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button" class="btn btn-primary" id="check_consumables">Принять расходники</button>
                                    </div>
                                </div>
                            @endif
                        @endcan
                    @endif
            </div><!-- /.box-body -->
        </div> <!--/.box-->
    </div>
</div>

<!-- Modal Актив на продажу -->
<div class="modal fade" id="check_consumable" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">Принять расходник</h4>
            </div>
            <div class="modal-body2">
                <div class="row">
                    <div class="col-md-12">
                        <form class="form-horizontal">

                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                <button type="button" class="btn btn-primary" id="addSalesButton">Принять</button>
            </div>
        </div>
    </div>
</div>


@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', [
    'exportFile' => 'locations-export',
    'search' => true
 ])

    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>
    <script nonce="{{ csrf_token() }}">

        var table_consumables = $('#table_consumables');
        var check_consumables = $('#check_consumables');
        var data = {!! $purchase->consumables_json !!};

        @can('review', \App\Models\Asset::class)
                var can_reviewconsumable = true;
        @endcan
        $(function() {

            check_consumables.click(function() {
                $.ajax({
                    type: 'POST',
                    url: "{{ route('api.purchases.consumables_check', $purchase->id) }}",
                    headers: {
                        "X-Requested-With": 'XMLHttpRequest',
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    success: function (data) {
                        check_consumables.hide();
                    },
                });
            });
            var old = false;
            if ("category_id" in data[0]) {
                old= true;
            }
            if (old){
                table_consumables.bootstrapTable('destroy').bootstrapTable({
                    data: data,
                    search:true,
                    toolbar:'#toolbar_consumables',
                    columns: [{
                        field: 'id',
                        name:'#',
                        align: 'left',
                        valign: 'middle'
                    },{
                        field: 'name',
                        name:'Назвние',
                        align: 'left',
                        valign: 'middle'
                    },{
                        field: 'manufacturer_name',
                        name:'Производитель',
                        align: 'left',
                        valign: 'middle'
                    },{
                        field: 'category_name',
                        name:'Категория',
                        align: 'left',
                        valign: 'middle'
                    },{
                        field: 'model_number',
                        name:'Модель',
                        align: 'left',
                        valign: 'middle'
                    },{
                        field: 'purchase_cost',
                        name: 'Закупочная цена',
                        align: 'center',
                        valign: 'middle'
                    },{
                        field: 'nds',
                        name: 'НДС',
                        align: 'center',
                        valign: 'middle'
                    },{
                        field: 'quantity',
                        name: 'Количество',
                        align: 'center',
                        valign: 'middle'
                    }
                    ]
                });
            }else{
                table_consumables.bootstrapTable('destroy').bootstrapTable({
                    data: data,
                    search:true,
                    stickyHeader:false,
                    toolbar:'#toolbar_consumables',
                    columns: [{
                        field: 'id',
                        name:'#',
                        align: 'left',
                        valign: 'middle'
                    },{
                        field: 'consumable',
                        name:'Назвние',
                        align: 'left',
                        valign: 'middle'
                    },{
                        field: 'purchase_cost',
                        name: 'Закупочная цена',
                        align: 'center',
                        valign: 'middle'
                    },{
                        field: 'nds',
                        name: 'НДС',
                        align: 'center',
                        valign: 'middle'
                    },{
                        field: 'quantity',
                        name: 'Количество',
                        align: 'center',
                        valign: 'middle'
                    },{
                        field: 'reviewed',
                        name: 'Принято',
                        align: 'center',
                        valign: 'middle'
                    }, {
                        align: 'center',
                        valign: 'middle',
                        width:"100",
                        events: {
                            'click .check_consumable': function (e, value, row, index) {
                                // $('#check_consumable').modal("show");
                                var max_quantity = row.quantity;
                                if ("reviewed" in row){
                                    max_quantity = row.quantity - row.reviewed;
                                }
                                Swal.fire({
                                    title: "Принять - "+row.consumable,
                                    // text: 'Do you want to continue',
                                    icon: 'question',
                                    input:"range",
                                    inputLabel: 'Количество',
                                    inputAttributes: {
                                        min: 1,
                                        max: max_quantity,
                                        step: 1
                                    },
                                    inputValue: max_quantity,
                                    reverseButtons:true,
                                    showCancelButton: true,
                                    confirmButtonText: 'Подтвердить',
                                    cancelButtonText: 'Отменить',
                                }).then((result) => {
                                    if (result.isConfirmed) {

                                        var sendData = {
                                            purchase_id: {{ $purchase->id}},
                                            quantity:result.value,
                                            nds:row.nds,
                                            purchase_cost:row.purchase_cost,
                                        };
                                        $.ajax({
                                            type: 'POST',
                                            url:"/api/v1/consumables/"+row.consumable_id+"/review",
                                            headers: {
                                                "X-Requested-With": 'XMLHttpRequest',
                                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                                            },
                                            data: sendData,
                                            dataType: 'json',
                                            success: function (data) {
                                                console.log(data);
                                                if ("reviewed" in row){
                                                    row.reviewed = parseInt(result.value) + parseInt(row.reviewed);
                                                }else{
                                                    row.reviewed= parseInt(result.value);
                                                }
                                                table_consumables.bootstrapTable('updateRow', {index: index, row: row});
                                            },
                                        });
                                    }
                                });

                            }
                        },
                        formatter: function (value, row, index) {
                            var max_quantity = row.quantity;
                            if ("reviewed" in row){
                                max_quantity = row.quantity - row.reviewed;
                            }
                            if (max_quantity>0 && can_reviewconsumable){
                                return [
                                    '<button type="button" class="btn btn-sm btn-primary check_consumable">Принять</button>',
                                ].join('')
                            }else{
                                return "";
                            }
                        }
                    }
                    ]
                });
            }
        })
    </script>

@stop
