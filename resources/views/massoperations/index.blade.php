@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/massoperations/general.title') }}
    @parent
@stop

@section('header_right')
    @can('checkout', \App\Models\Asset::class)
        <a href="{{ route('bulk.checkout.show') }}" class="btn  bg-maroon pull-right" style="margin-left: 10px">
            <i class="fa-solid fa-rotate-left  "></i> {{ trans('admin/massoperations/general.checkout') }}</a>
        <a href="{{ route('bulk.sell.show') }}" class="btn bg-red pull-right" style="margin-left: 10px">
            <i class="fa-solid fa-ruble-sign  "></i>  {{ trans('admin/massoperations/general.sell') }}</a>
        <a href="{{ route('bulk.checkin.show') }}" class="btn bg-purple pull-right" style="margin-left: 10px">
            <i class="fa-solid fa-rotate-right  "></i> {{ trans('admin/massoperations/general.return') }}</a>
    @endcan
@stop

{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-body">
                    <div class="table-responsive">
                        <table
                                data-columns="{{ \App\Presenters\MassOperationsPresenter::dataTableAllLayout() }}"
                                data-cookie-id-table="massOperationsTable"
                                data-pagination="true"
                                data-toolbar="#toolbar"
                                data-search="true"
                                data-side-pagination="server"
                                data-show-columns="true"
                                data-show-export="true"
                                data-show-footer="true"
                                data-show-refresh="true"
                                data-sort-order="desc"
                                id="massOperationsTable"
                                class="table table-striped snipe-table"
                                data-url="{{ route('api.massoperations.index') }}"
                                data-export-options='{
            "fileName": "export-massOperations-{{ date('Y-m-d') }}"
            }'>
                        </table>
                    </div>
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
