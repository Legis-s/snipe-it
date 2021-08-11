@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.consumables') }}
    @parent
@stop

@section('header_right')
    @can('create', \App\Models\Consumable::class)
        <a href="{{ route('consumables.create') }}"
           class="btn btn-primary pull-right"> {{ trans('general.create') }}</a>
    @endcan
@stop

{{-- Page content --}}
@section('content')
    <link rel="stylesheet" type="text/css" href="{{ asset('css/sweetalert2.min.css') }}">
    <div class="row">
        <div class="col-md-12">

            <div class="box box-default">
                <div class="box-body">
                    {{ Form::open([
    'method' => 'POST',
    'route' => ['hardware/bulkedit'],
    'class' => 'form-inline',
     'id' => 'bulkForm']) }}
                    <div class="row">
                        <div class="col-md-12">
                            <div id="toolbar">
{{--                                <label for="bulk_actions"><span class="sr-only">Bulk Actions</span></label>--}}
{{--                                <select name="bulk_actions" class="form-control select2" aria-label="bulk_actions">--}}
{{--                                    <option value="compact">Собрать</option>--}}
{{--                                </select>--}}
{{--                                <button class="btn btn-primary" id="bulkEdit" disabled>Go</button>--}}
                                <button class="btn btn-primary" id="compact" disabled>Собрать</button>
                            </div>
                            <table
                                    data-click-to-select="true"
                                    data-columns="{{ \App\Presenters\ConsumablePresenter::dataTableLayout() }}"
                                    data-cookie-id-table="consumablesTable"
                                    data-pagination="true"
                                    data-id-table="consumablesTable"
                                    data-search="true"
                                    data-side-pagination="server"
                                    data-show-columns="true"
                                    data-show-export="true"
                                    data-show-footer="true"
                                    data-show-refresh="true"
                                    data-sort-order="asc"
                                    data-sort-name="name"
                                    data-toolbar="#toolbar"
                                    id="consumablesTable"
                                    class="table table-striped snipe-table"
                                    data-url="{{ route('api.consumables.index') }}"
                                    data-export-options='{
                "fileName": "export-consumables-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
                            </table>

                        </div><!-- /.box-body -->
                    </div><!-- /.box-body -->
                  {{ Form::close() }}
                </div><!-- /.box-body -->
            </div><!-- /.box -->

        </div> <!-- /.col-md-12 -->
    </div> <!-- /.row -->
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', ['exportFile' => 'consumables-export', 'search' => true,'showFooter' => true, 'columns' => \App\Presenters\ConsumablePresenter::dataTableLayout()])
    <script src="{{ asset('js/sweetalert2.min.js') }}"></script>
    <script nonce="{{ csrf_token() }}">
        $(function () {
            var compact = $('#compact');
            var table = $('#consumablesTable');
            var selections = []

            // function getIdSelections() {
            //     return $.map(table.bootstrapTable('getSelections'), function (row) {
            //         return row.id
            //     })
            // }

            table.on('check.bs.table uncheck.bs.table ' + 'check-all.bs.table uncheck-all.bs.table',
                function () {
                    compact.prop('disabled', !table.bootstrapTable('getSelections').length)
                    // save your data, here just save the current page
                    selections = table.bootstrapTable('getSelections');
                    // push or splice the selections if you want to save all data selections
                });
            compact.click(function () {
                var selected = table.bootstrapTable('getSelections');
                var selection_object ={};
                selected.forEach(function(item, i, arr) {
                    selection_object[item.id]=item.name;
                });

                Swal.fire({
                    title: "Собрать расходники в один?",
                    // text: 'Do you want to continue',
                    icon: 'question',
                    input:"select",
                    inputPlaceholder: 'Выберите основной',
                    inputOptions: selection_object,
                    reverseButtons:true,
                    showCancelButton: true,
                    confirmButtonText: 'Подтвердить',
                    cancelButtonText: 'Отменить',
                }).then((result) => {
                    if (result.isConfirmed) {
                        var idS=[]
                        selected.forEach(function(item, i, arr) {
                            if (item.id !=result.value){
                                idS.push(item.id);
                            }
                        });

                        var sendData = {
                            id_array:idS,
                        };
                        $.ajax({
                            type: 'POST',
                            url:"/api/v1/consumables/"+result.value+"/compact",
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
                compact.prop('disabled', true)
            });

        });
    </script>
@stop
