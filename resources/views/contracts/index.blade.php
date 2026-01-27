@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.contracts') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-box.container>
            <div id="toolbar">
                <label class="form-control">
                    <input type="checkbox" value="" id="only_assets_checkbox">Активы без з/д
                </label>
                <label class="form-control">
                    <input type="checkbox" value="" id="only_consumables_checkbox">Расходники без з/д
                </label>
                <label class="form-control">
                    <input type="checkbox" value="" id="sum_checkbox">Превышение суммы
                </label>
            </div>
            <table
                    data-columns="{{ \App\Presenters\ContractPresenter::dataTableLayout() }}"
                    data-cookie-id-table="contractTable"
                    data-id-table="contractTable"
                    data-side-pagination="server"
                    data-sort-order="asc"
                    data-query-params="contractQueryParams"
                    id="contractTable"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.contracts.index') }}"
                    data-export-options='{
              "fileName": "export-locations-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
            </table>
        </x-box.container>
    </x-container>

@stop

@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        $(function () {
            $("#contractTable").bootstrapTable('refreshOptions', {
                queryParams: function (params) {
                    if ($('#sum_checkbox').is(":checked")) {
                        params.sum_error = 1
                    } else {
                        params.sum_error = 0
                    }
                    if ($('#only_consumables_checkbox').is(":checked")) {
                        params.only_consumables = 1
                    } else {
                        params.only_consumables = 0
                    }
                    if ($('#only_assets_checkbox').is(":checked")) {
                        params.only_assets = 1
                    } else {
                        params.only_assets = 0
                    }
                    return params
                },
            })

            $('#only_assets_checkbox').on('change', function () { // on change of state
                $("#contractTable").bootstrapTable('refresh');
            });
            $('#only_consumables_checkbox').on('change', function () { // on change of state
                $("#contractTable").bootstrapTable('refresh');
            });
            $('#sum_checkbox').on('change', function () { // on change of state
                $("#contractTable").bootstrapTable('refresh');
            });

        });
    </script>
    @include ('partials.bootstrap-table', ['exportFile' => 'contracts-export', 'search' => true])
@stop
