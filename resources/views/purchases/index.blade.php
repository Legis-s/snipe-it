@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.purchases') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-box.container>

            <div id="toolbar">
                @isset($users)
                    <select id="js-select-user">
                        <option value="0" ></option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->last_name }} {{ $user->first_name }}</option>
                        @endforeach
                    </select>
                @endisset
                <select id="js-select-status">
                    <option value="0" ></option>
                    <option value="inventory">В процессе инвентаризации</option>
                    <option value="in_payment">В оплате</option>
                    <option value="review">В процессе проверки</option>
                    <option value="finished">Завершено</option>
                    <option value="rejected">Отклонено</option>
                    <option value="paid">Оплачено</option>
                    <option value="inprogress">>На согласовании</option>
                </select>
            </div>
            <table
                    data-columns="{{ \App\Presenters\PurchasePresenter::dataTableLayout() }}"
                    data-cookie-id-table="purchasesTable"
                    data-id-table="purchasesTable"
                    data-side-pagination="server"
                    data-sort-order="desc"
                    data-toolbar="#toolbar"
                    data-buttons="purchaseButtons"
                    id="purchasesTable"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.purchases.index') }}"
                    data-export-options='{
              "fileName": "export-purchases-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
            </table>
        </x-box.container>
    </x-container>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', ['exportFile' => 'purchases-export', 'search' => true])
    <script nonce="{{ csrf_token() }}">

        $(function () {
            const $table = $('#purchasesTable');
            const $select_user = $('#js-select-user');
            const $select_status = $('#js-select-status');
            $table.bootstrapTable('refreshOptions', {
                queryParams: params => {
                    const user = $select_user.val();
                    const status =  $select_status.val();
                    if (parseInt(user)>0){
                        params.created_by = parseInt(user);
                    }else{
                        delete params.created_by;
                    }
                    if (status?.length > 3) {
                        params.status = status;
                    } else {
                        delete params.status;
                    }

                    console.log(params);
                    return params
                }
            })

            $select_user.select2({
                placeholder: 'Выберите пользователя',
                allowClear: true,
                width: "200px"
            }).on('select2:select', function (e) {
                $table.bootstrapTable('refresh');
            }).on('select2:clear', function (e) {
                $table.bootstrapTable('refresh');
            });
            $select_status.select2({
                placeholder: 'Выберите статус',
                allowClear: true,
                width: "200px"
            }).on('select2:select', function (e) {
                $table.bootstrapTable('refresh');
            }).on('select2:clear', function (e) {
                $table.bootstrapTable('refresh');
            });
        });
    </script>
@stop

