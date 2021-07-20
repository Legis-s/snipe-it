@extends('layouts/default')

{{-- Page title --}}
@section('title')
Закупки
@parent
@stop

@section('header_right')
    @can('create', \App\Models\Purchase::class)
        <a href="{{ route('purchases.create') }}" class="btn btn-primary pull-right">
            {{ trans('general.create') }}</a>
    @endcan
@stop

{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-body">
                    <div id="toolbar">
                        <select class="js-example-basic-single" name="state">
                            <option value="AL">Alabama</option>
                            <option value="WY">Wyoming</option>
                        </select>
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
    $(document).ready(function() {
            // $('#purchasesTable').on('load-success.bs.table', function (e,data) {
            //     var rows = data.rows;
            //     var users = [];
            //     rows.forEach((element) => {
            //         var user  = element.user;
            //         users.push(user);
            //     })
            //     console.log(users)
            //     let known = {};
            //     let filtered = users.map(subarray =>
            //         subarray.filter(item => !known.hasOwnProperty(item.id) && (known[item.id] = true))
            //     )
            //     console.log(filtered)
            //
            //
            // });
            // $('.js-example-basic-single').select2();
    });
    </script>

@stop

