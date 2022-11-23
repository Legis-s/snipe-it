@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/massoperations/general.title') }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-10">
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
        <div class="col-md-2">
            <div class="box box-default">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            @can('checkout', \App\Models\Asset::class)

                                <div class="col-md-12">
                                    <a href="{{ route('bulk.checkout.show') }}"
                                       style="margin-bottom:10px; width:100%" class="btn btn-primary pull-right">
                                        {{ trans('admin/massoperations/general.checkout') }} <i
                                                class="fa-solid fa-arrow-right"></i>
                                    </a>
                                    <a href="{{ route('bulk.sell.show') }}"
                                       style="margin-bottom:10px; width:100%" class="btn btn-primary pull-right">
                                        {{ trans('admin/massoperations/general.sell') }} <i
                                                class="fa-solid fa-arrow-right"></i>
                                    </a>
                                    <a href="{{ route('bulk.checkin.show') }}"
                                       style="margin-bottom:10px; width:100%" class="btn btn-primary pull-right">
                                        {{ trans('admin/massoperations/general.return') }} <i
                                                class="fa-solid fa-arrow-left"></i>
                                    </a>
                                </div>

                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')

@stop
