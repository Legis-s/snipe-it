@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Заявки
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
                    <div id="toolbar">
                    </div>
                    <table
                            data-click-to-select="true"
                            data-columns="{{ \App\Presenters\PurchasePresenter::dataTableLayout() }}"
                            data-cookie-id-table="purchasesTable"
                            data-pagination="true"
                            data-id-table="purchasesTable"
                            data-search="true"
                            data-side-pagination="server"
                            data-show-columns="true"
                            data-show-export="true"
                            data-show-refresh="true"
                            data-sort-order="asc"
                            data-toolbar="#toolbar"
                            id="purchasesTable"
                            class="table table-striped snipe-table"
                            data-url="{{ route('api.purchases.index') }}">
                    </table>
                </div>
            </div>
        </div>
    </div>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')
    <script>
        $(document).ready(function () {

        });
    </script>

@stop

