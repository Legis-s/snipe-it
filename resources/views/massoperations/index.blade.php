@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/massoperations/general.title') }}
    @parent
@stop


@section('header_right')
{{--    @can('create', \App\Models\License::class)--}}

{{--    @endcan--}}
@stop

{{-- Page content --}}
@section('content')


    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <div id="toolbar">

                        <a href="{{ route('hardware/bulksell') }}" class="btn btn-primary pull-right">
                            {{ trans('admin/massoperations/general.sell') }}
                        </a>
                        <a href="{{ route('hardware/bulkcheckout') }}" class="btn btn-primary pull-right">
                            {{ trans('admin/massoperations/general.checkout') }}
                        </a>
{{--                        <a href="{{ route('licenses.create') }}" class="btn btn-primary pull-right">--}}
{{--                            {{ trans('admin/massoperations/general.return') }}--}}
{{--                        </a>--}}
                    </div>
                    <table
                            data-columns="{{ \App\Presenters\MassOperationPresenter::dataTableAllLayout() }}"
                            data-cookie-id-table="massOperationsPresenterTable"
                            data-pagination="true"
                            data-toolbar="#toolbar"
                            data-search="true"
                            data-side-pagination="server"
                            data-show-columns="true"
                            data-show-export="true"
                            data-show-footer="true"
                            data-show-refresh="true"
                            data-sort-order="asc"
                            data-sort-name="name"
                            id="massOperationsTable"
                            class="table table-striped snipe-table"
{{--                            data-url="{{ route('api.licenses.index') }}"--}}
                            data-export-options='{
            "fileName": "export-massOperations-{{ date('Y-m-d') }}"
{{--            "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]--}}
            }'>
                    </table>

                </div><!-- /.box-body -->

                <div class="box-footer clearfix">
                </div>
            </div><!-- /.box -->
        </div>
    </div>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')

@stop
