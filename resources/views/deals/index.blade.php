@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.deals') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-box.container>
            <table
                    data-columns="{{ \App\Presenters\DealPresenter::dataTableLayout() }}"
                    data-cookie-id-table="dealsTable"
                    data-id-table="dealsTable"
                    data-side-pagination="server"
                    data-sort-order="asc"
                    data-query-params="dealsQueryParams"
                    id="dealsTable"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.deals.index') }}"
                    data-export-options='{
              "fileName": "export-locations-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
            </table>
        </x-box.container>
    </x-container>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', ['exportFile' => 'deals-export', 'search' => true])
@stop
