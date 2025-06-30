@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/inventorystatuslabels/table.title') }}
@parent
@stop

@section('header_right')
    @can('create', \App\Models\Statuslabel::class)
        <a href="{{ route('inventorystatuslabels.create') }}" class="btn btn-primary pull-right">
        {{ trans('general.create') }}</a>
    @endcan
@stop
{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-9">
    <div class="box box-default">
      <div class="box-body">
        <div class="table-responsive">

            <table
                    data-columns="{{ \App\Presenters\InventoryStatusLabelPresenter::dataTableLayout() }}"
                    data-cookie-id-table="inventoryStatuslabelsTable"
                    data-id-table="inventoryStatuslabelsTable"
                    data-show-footer="false"
                    data-side-pagination="server"
                    data-sort-order="asc"
                    data-sort-name="name"
                    id="inventoryStatuslabelsTable"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.inventorystatuslabels.index') }}"
                    data-export-options='{
                "fileName": "export-inventorystatuslabels-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
            </table>

        </div>
      </div>
    </div>
  </div>
</div>
@stop

@section('moar_scripts')
@include ('partials.bootstrap-table')

  <script nonce="{{ csrf_token() }}">
      function colorSqFormatter(value, row) {
          if (value) {
              return '<span class="label" style="background-color: ' + value + ';">&nbsp;</span> ' + value;
          }
      }

      function statusLabelSuccessFormatter (row, value) {
          if (value.success=="1"){
              text_color = 'green';
              icon_style = 'fa-circle';
              trans  = 'Успешно';
          }else{
              text_color = 'red';
              icon_style = 'fa-circle';
              trans  ='Не успешно';
          }
          var typename_lower = trans;
          var typename = typename_lower.charAt(0).toUpperCase() + typename_lower.slice(1);
          return '<i class="fa ' + icon_style + ' text-' + text_color + '"></i> ' + typename;
      }
  </script>
@stop
