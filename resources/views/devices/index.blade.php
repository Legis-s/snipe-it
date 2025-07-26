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
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-body">
                    <div class="table-responsive">

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
              "fileName": "export-locations-{{ date('Y-m-d') }}",
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
    @include ('partials.bootstrap-table', ['exportFile' => 'devices-export', 'search' => true])

@stop
