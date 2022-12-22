@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ trans('general.contracts') }}
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
            <div class="form-check">
              <input class="form-check-input" type="checkbox" value="" id="sum_checkbox">
              <label class="form-check-label" for="defaultCheck1">
                Превышение суммы
              </label>
            </div>
          </div>
          <table
                  data-columns="{{ \App\Presenters\ContractPresenter::dataTableLayout() }}"
                  data-cookie-id-table="contractTable"
                  data-pagination="true"
                  data-id-table="contractTable"
                  data-search="true"
                  data-toolbar="#toolbar"
                  data-show-footer="true"
                  data-side-pagination="server"
                  data-show-columns="true"
                  data-show-export="true"
                  data-show-refresh="true"
                  data-sort-order="asc"
                  id="contractTable"
                  class="table table-striped snipe-table"
                  data-url="{{ route('api.contracts.index') }}"
                  data-export-options='{
              "fileName": "export-contracts-{{ date('Y-m-d') }}",
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
@include ('partials.bootstrap-table', ['exportFile' => 'contracts-export', 'search' => true])
<script nonce="{{ csrf_token() }}">
  $(function () {

    $('#sum_checkbox').on('change', function(){ // on change of state
      if(this.checked){
        $( "#contractTable" ).bootstrapTable('refresh', {
          query: {
            sum_error: 1
          }
        });
      }else{
        $( "#contractTable" ).bootstrapTable('refresh', {
          query: {
            sum_error: 0
          }
        });
      }
    })

  });
</script>
@stop
