@extends('layouts/default')

{{-- Page title --}}
@section('title')

    {{ trans('general.purchase') }}:
    {{ $purchase->invoice_number }}

    @parent
@stop

{{-- Page content --}}
@section('content')

<div class="row">
    <div class="col-md-9">
        @if (count($purchase->assets) > 0)
        <div class="box">
            <div class="box-header with-border">
                <div class="box-heading">
                    <h2 class="box-title">Активы</h2>
                </div>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
{{--                <div class="table table-responsive">--}}

                    <table
                        data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                        data-pagination="true"
                        data-id-table="assetsListingTable"
                        data-search="true"
                        data-side-pagination="server"
                        data-show-columns="true"
                        data-show-export="true"
                        data-show-refresh="true"
                        id="assetsListingTable"
                        class="table table-striped snipe-table"
                        data-url="{{route('api.assets.index', ['purchase_id' => $purchase->id]) }}">
                    </table>
{{--                </div><!-- /.table-responsive -->--}}

                    </div>
                </div>
          </div><!-- /.box-body -->
        </div> <!--/.box-->
        @endif

        @if ( strlen($purchase->consumables_json) > 2)
        @if ($purchase->status!='paid')
            <div class="box">
                <div class="box-header with-border">
                    <div class="box-heading">
                        <h2 class="box-title">Расходники не принятые</h2>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
{{--                    <div class="table table-responsive">--}}
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
                                <th>Название</th>
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
{{--                    </div><!-- /.table-responsive -->--}}
                        </div>
                    </div>
                </div><!-- /.box-body -->
            </div> <!--/.box-->
            <div class="box">
                <div class="box-header with-border">
                    <div class="box-heading">
                        <h2 class="box-title">Расходники принятые </h2>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
{{--                    <div class="table table-responsive">--}}
                        <table
                                data-columns="{{ \App\Presenters\ConsumableAssignmentPresenter::dataTableLayoutIn() }}"
                                data-cookie-id-table="сonsumableAssignmentTable"
                                data-pagination="true"
                                data-id-table="сonsumableAssignmentTable"
                                data-search="true"
                                data-side-pagination="server"
                                data-show-columns="true"
                                data-show-export="true"
                                data-show-refresh="true"
                                data-sort-order="asc"
                                id="сonsumableAssignmentTable"
                                class="table table-striped snipe-table"
                                data-url="{{route('api.consumableassignments.index',['purchase_id'=> $purchase->id])}}">

                        </table>
{{--                    </div><!-- /.table-responsive -->--}}
                        </div>
                    </div>
                </div><!-- /.box-body -->
            </div> <!--/.box-->


              @endif
            @endif
    </div><!--/.col-md-9-->
    <div class="col-md-3">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <div class="box-heading">
                            <h2 class="box-title">Информация</h2>
                        </div>
                    </div>
                    <div class="box-body">
                        @if ($purchase->status)
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>
                                        Статус
                                    </strong>
                                </div>
                                <div class="col-md-8 status_label">
                                    @switch($purchase->status)
                                        @case("inventory")
                                            <span class="label label-warning">В процессе инвентаризации</span>
                                            @break

                                        @case("in_payment")
                                            <span class="label label-primary">В оплате</span>
                                            @break

                                        @case("review")
                                            <span class="label label-warning">В процессе проверки</span>
                                            @break

                                        @case("finished")
                                            <span class="label label-success">Завершено</span>
                                            @break

                                        @case("rejected")
                                            <span class="label label-danger">Отклонено</span>
                                            @break

                                        @case("paid")
                                            <span class="label label-success">Оплачено</span>
                                            @break

                                        @case("inprogress")
                                            <span class="label label-primary">На согласовании</span>
                                            @break

                                    @endswitch
                                </div>
                            </div>
                        @endif
                        @if ($purchase->invoice_number)
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>
                                        Название
                                    </strong>
                                </div>
                                <div class="col-md-8">
                                    {{ $purchase->invoice_number }}
                                </div>
                            </div>
                        @endif
                        @if ($purchase->invoice_file)
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>
                                        Файл счета
                                    </strong>
                                </div>
                                <div class="col-md-8">
                                    <a href="/uploads/purchases/{{ $purchase->invoice_file }}">Скачать</a>
                                </div>
                            </div>
                        @endif
                        @if ($purchase->bitrix_id)
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>
                                        Bitrix id
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
                                        Цена
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
                                        Стоимость доставки
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
                                        Оплачено
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
                                        Комментарий
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
                                        Задача
                                    </strong>
                                </div>
                                <div class="col-md-8">
                                    <a href="https://bitrix.legis-s.ru/company/personal/user/290/tasks/task/view/{{ $purchase->bitrix_task_id }}/">{{ $purchase->bitrix_task_id }}</a>
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
                        @can('checkout', \App\Models\Asset::class)

                            <div class="col-md-12">
                                <br><br>
                                @if ($purchase->status == "finished")
                                    <a href="{{ route('bulk.checkout.show', ['purchase_bulk_id' => $purchase->id]) }}" style="margin-bottom:10px; width:100%" class="btn btn-primary btn-sm">
                                        {{ trans('admin/massoperations/general.checkout') }}
                                    </a>
                                    <a href="{{ route('bulk.sell.show', ['purchase_bulk_id' => $purchase->id]) }}" style="margin-bottom:10px; width:100%" class="btn btn-primary btn-sm">
                                        {{ trans('admin/massoperations/general.sell') }}
                                    </a>
                                @endif
                            </div>

                        @endcan
                    </div><!-- /.box-body -->
                </div> <!--/.box-->
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


    <script nonce="{{ csrf_token() }}">

        var table_consumables = $('#table_consumables');
        var check_consumables = $('#check_consumables');
        var data = {!! $purchase->consumables_json !!};
        var can_reviewconsumable = false;
        @can('review')
                can_reviewconsumable = true;
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
                        name:'Название',
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
                        valign: 'middle',
                        formatter:function (value,row) {
                            return "<a href='/consumables/"+row.consumable_id+"'>"+value+"</a>";
                        }
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
                                                $.ajax({
                                                    type: 'GET',
                                                    url:"/api/v1/purchases/{{ $purchase->id}}",
                                                    headers: {
                                                        "X-Requested-With": 'XMLHttpRequest',
                                                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                                                    },
                                                    dataType: 'json',
                                                    success: function (data) {
                                                        var status = data.status;
                                                        var result = "";
                                                        switch (status) {
                                                            case "inventory":
                                                                result=  '<span class="label label-warning">В процессе инвентаризации</span>';
                                                                break;
                                                            case "in_payment":
                                                                result= '<span class="label label-primary">В оплате</span>';
                                                                break;
                                                            case "review":
                                                                result= '<span class="label label-warning">В процессе проверки</span>';
                                                                break;
                                                            case "finished":
                                                                result= '<span class="label label-success">Завершено</span>';
                                                                break;
                                                            case "rejected":
                                                                result= '<span class="label label-danger">Отклонено</span>';
                                                                break;
                                                            case "paid":
                                                                result= '<span class="label label-success">Оплачено</span>';
                                                                break;
                                                            case "inprogress":
                                                                result= '<span class="label label-primary">На согласовании</span>';
                                                                break;
                                                        }
                                                        $('.status_label').html(result);
                                                        $('#сonsumableAssignmentTable').bootstrapTable('refresh');
                                                    },
                                                });

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
