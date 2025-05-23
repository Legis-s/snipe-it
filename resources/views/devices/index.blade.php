@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Телефоны
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
                                data-pagination="true"
                                data-id-table="devicesTable"
                                data-search="true"
                                data-show-footer="true"
                                data-side-pagination="server"
                                data-show-columns="true"
                                data-show-fullscreen="true"
                                data-show-export="true"
                                data-show-refresh="true"
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
