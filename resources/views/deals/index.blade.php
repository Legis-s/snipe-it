@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ trans('general.deals') }}
@parent
@stop

{{-- Page content --}}
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-body">
        <div class="table-responsive">
          <div id="toolbar">
            <label class="form-control">
              <input type="checkbox" value="" id="sum_checkbox">Превышение суммы
            </label>
          </div>
          <table
                  data-columns="{{ \App\Presenters\DealPresenter::dataTableLayout() }}"
                  data-cookie-id-table="dealsTable"
                  data-pagination="true"
                  data-id-table="dealsTable"
                  data-search="true"
                  data-toolbar="#toolbar"
                  data-show-footer="true"
                  data-side-pagination="server"
                  data-show-columns="true"
                  data-show-export="true"
                  data-show-refresh="true"
                  data-sort-order="asc"
                  data-query-params="dealsQueryParams"
                  id="dealsTable"
                  class="table table-striped snipe-table"
                  data-url="{{ route('api.deals.index') }}"
                  data-export-options='{
              "fileName": "export-deals-{{ date('Y-m-d') }}",
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
<script nonce="{{ csrf_token() }}">
  $(function () {
    $( "#dealsTable" ).bootstrapTable('refreshOptions', {
      queryParams:function (params) {
        if ($('#sum_checkbox').is(":checked")) {
          params.sum_error = 1
        }else{
          params.sum_error = 0
        }
        // if ($('#only_consumables_checkbox').is(":checked")) {
        //   params.only_consumables = 1
        // }else{
        //   params.only_consumables = 0
        // }
        // if ($('#only_assets_checkbox').is(":checked")) {
        //   params.only_assets = 1
        // }else{
        //   params.only_assets = 0
        // }
        return params
      },
    })

    $('#only_assets_checkbox').on('change', function(){ // on change of state
      $( "#dealsTable" ).bootstrapTable('refresh');
    });
    $('#only_consumables_checkbox').on('change', function(){ // on change of state
      $( "#dealsTable" ).bootstrapTable('refresh');
    });
    $('#sum_checkbox').on('change', function(){ // on change of state
      $( "#dealsTable" ).bootstrapTable('refresh');
    });

  });
</script>
@include ('partials.bootstrap-table', ['exportFile' => 'deals-export', 'search' => true])
@stop
