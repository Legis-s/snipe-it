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
            <i class="fa-solid fa-ruble-sign  "></i> {{ trans('admin/massoperations/general.sell') }}</a>
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
                    <table
                            data-columns="{{ \App\Presenters\MassOperationsPresenter::dataTableAllLayout() }}"
                            data-cookie-id-table="massOperationsTable"
                            data-toolbar="#toolbar"
                            data-side-pagination="server"
                            data-sort-order="desc"
                            id="massOperationsTable"
                            class="table table-striped snipe-table"
                            data-url="{{ route('api.massoperations.index') }}"
                            data-export-options='{
            "fileName": "export-massOperations-{{ date('Y-m-d') }}"
            }'>
                    </table>
                </div><!-- /.box-body -->
            </div><!-- /.box -->
        </div>
    </div>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop
