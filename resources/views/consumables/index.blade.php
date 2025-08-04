@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.consumables') }}
@parent
@stop

@section('header_right')
  @can('create', \App\Models\Consumable::class)
  <a href="{{ route('consumables.create') }}" {{$snipeSettings->shortcuts_enabled == 1 ? "accesskey=n" : ''}} class="btn btn-primary pull-right"> {{ trans('general.create') }}</a>
  @endcan
@stop

{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-12">

      <div class="box box-default">
          <div class="box-body">
              <div id="toolbar">
                  <button class="btn btn-primary" id="compact" disabled>Собрать</button>
              </div>
              <table
                            data-columns="{{ \App\Presenters\ConsumablePresenter::dataTableLayout() }}"
                            data-cookie-id-table="consumablesTable"
                            data-id-table="consumablesTable"
                            data-side-pagination="server"
                            data-footer-style="footerStyle"
                            data-toolbar="#toolbar"
                            id="consumablesTable"
                            data-buttons="consumableButtons"
                            class="table table-striped snipe-table"
                            data-url="{{ route('api.consumables.index') }}"
                            data-export-options='{
                "fileName": "export-consumables-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
        </table>

      </div><!-- /.box-body -->
    </div><!-- /.box -->

  </div> <!-- /.col-md-12 -->
</div> <!-- /.row -->
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'consumables-export', 'search' => true,'showFooter' => true, 'columns' => \App\Presenters\ConsumablePresenter::dataTableLayout()])

    <script nonce="{{ csrf_token() }}">
        $(function () {
            var compact = $('#compact');
            var table = $('#consumablesTable');
            var selections = []

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
                        var idS=[];
                        selected.forEach(function(item, i, arr) {
                            if (item.id !== result.value){
                                idS.push(item.id);
                            }
                        });
                        $.ajax({
                            type: 'POST',
                            url:"/api/v1/consumables/"+result.value+"/compact",
                            headers: {
                                "X-Requested-With": 'XMLHttpRequest',
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                            },
                            data: {
                                id_array:idS,
                            },
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
