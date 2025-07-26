@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('general.invoicetypes') }}
@parent
@stop

{{-- Page content --}}
@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box box-default">
      <div class="box-body">
        <div class="table-responsive">

          <table
                  data-columns="{{ \App\Presenters\InvoiceTypePresenter::dataTableLayout() }}"
                  data-cookie-id-table="invoicetypesTable"
                  data-pagination="true"
                  data-id-table="invoicetypesTable"
                  data-search="true"
                  data-show-footer="true"
                  data-side-pagination="server"
                  data-show-columns="true"
                  data-show-fullscreen="true"
                  data-show-export="true"
                  data-show-refresh="true"
                  data-sort-order="asc"
                  id="invoicetypesTable"
                  class="table table-striped snipe-table"
                  data-url="{{ route('api.invoicetypes.index') }}">
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

@stop

@section('moar_scripts')
@include ('partials.bootstrap-table')

@stop
