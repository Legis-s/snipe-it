@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.purchases') }}
    @parent
@stop

@section('header_right')
    @can('create', \App\Models\Purchase::class)
        <a href="{{ route('purchases.create') }}" class="btn btn-primary pull-right">
            {{ trans('general.create') }}</a>
    @endcan
    @can('delete', \App\Models\Purchase::class)
        <a href="{{ route('purchases.delete_all_rejected') }}" class="btn btn-danger pull-right">
            Удалить отклоненные</a>
    @endcan
@stop

{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-body">
                    <div class="table-responsive">
                    <div id="toolbar">
                        @isset($users)
                            <select class="js-select-user">
                                <option></option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->last_name }} {{ $user->first_name }}</option>
                                @endforeach
                            </select>
                        @endisset
                        <select class="js-select-status">
                            <option></option>
                            <option value="inventory">В процессе инвентаризации</option>
                            <option value="in_payment">В оплате</option>
                            <option value="review">В процессе проверки</option>
                            <option value="finished">Завершено</option>
                            <option value="rejected">Отклонено</option>
                            <option value="paid">Оплачено</option>
                            <option value="inprogress">>На согласовании</option>
                        </select>

                        @isset($suppliers)
                            <select class="js-select-supplier">
                                <option></option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        @endisset
                    </div>
                    <table
                            data-columns="{{ \App\Presenters\PurchasePresenter::dataTableLayout() }}"
                            data-cookie-id-table="purchasesTable"
                            data-pagination="true"
                            data-id-table="purchasesTable"
                            data-search="true"
                            data-show-footer="true"
                            data-side-pagination="server"
                            data-show-columns="true"
                            data-show-export="true"
                            data-show-refresh="true"
                            data-sort-order="desc"
                            data-toolbar="#toolbar"
                            id="purchasesTable"
                            class="table table-striped snipe-table"
                            data-url="{{ route('api.purchases.index') }}"
                            data-export-options='{
              "fileName": "export-purchases-{{ date('Y-m-d') }}",
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
    @include ('partials.bootstrap-table', ['exportFile' => 'purchases-export', 'search' => true])
    <script nonce="{{ csrf_token() }}">
        $(function ()  {
            var query_holder = {};

            $('.js-select-user').select2({
                placeholder: 'Выберите пользователя',
                allowClear: true,
                width: "200px"
            });
            $('.js-select-status').select2({
                placeholder: 'Выберите статус',
                allowClear: true,
                width: "200px"
            });
            $('.js-select-supplier').select2({
                placeholder: 'Выберите поставщика',
                allowClear: true,
                width: "200px"
            });
            $('.js-select-user').on('select2:select', function (e) {
                $('.js-select-status').val(null).trigger('change');
                $('.js-select-supplier').val(null).trigger('change');
                var data = e.params.data;
                $('#purchasesTable').bootstrapTable('refresh', {
                    query: {user_id: data.id}
                })
            });
            $('.js-select-user').on('select2:clear', function (e) {
                $('#purchasesTable').bootstrapTable('refresh')
            });
            $('.js-select-status').on('select2:select', function (e) {
                $('.js-select-user').val(null).trigger('change');
                $('.js-select-supplier').val(null).trigger('change');
                var data = e.params.data;
                $('#purchasesTable').bootstrapTable('refresh', {
                    query: {status: data.id}
                })
            });
            $('.js-select-status').on('select2:clear', function (e) {

                $('#purchasesTable').bootstrapTable('refresh')
            });
            $('.js-select-supplier').on('select2:select', function (e) {
                $('.js-select-user').val(null).trigger('change');
                $('.js-select-status').val(null).trigger('change');
                var data = e.params.data;
                $('#purchasesTable').bootstrapTable('refresh', {
                    query: {supplier: data.id}
                })
            });
            $('.js-select-supplier').on('select2:clear', function (e) {
                $('#purchasesTable').bootstrapTable('refresh')
            });
        });
    </script>
@stop

