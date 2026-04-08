@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.devices') }}
    @parent
@stop

@section('header_right')
@stop
{{-- Page content --}}
@section('content')
    <x-container>
        <x-box>

            <table
                    data-columns="{{ \App\Presenters\DevicePresenter::dataTableLayout() }}"
                    data-cookie-id-table="devicesTable"
                    data-id-table="devicesTable"
                    data-side-pagination="server"
                    data-sort-order="asc"
                    id="devicesTable"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.devices.index') }}"
                    data-export-options='{
              "fileName": "export-devices-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
            </table>
        </x-box>
    </x-container>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', ['exportFile' => 'devices-export', 'search' => true])

@stop
