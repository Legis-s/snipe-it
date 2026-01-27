@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.inventories') }}
    @parent
@stop

@section('header_right')
    @can('create', \App\Models\Location::class)
        <button class="btn btn-primary pull-right" id="clear_all_null">{{ trans('general.clear_all_null') }}</button>
    @endcan
@stop
{{-- Page content --}}
@section('content')
    <x-container>
        <x-box.container>
            <table
                    data-columns="{{ \App\Presenters\InventoryPresenter::dataTableLayout() }}"
                    data-cookie-id-table="inventoriesListTable"
                    data-id-table="inventoriesListTable"
                    data-side-pagination="server"
                    data-sort-order="desc"
                    id="inventoriesListTable"
                    data-buttons="inventoriesButtons"
                    class="table table-striped snipe-table"
                    data-url="{{ route('api.inventories.index') }}"
                    data-export-options='{
              "fileName": "export-inventories-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
            </table>
        </x-box.container>
    </x-container>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')

    <script type="text/javascript">
        $(document).ready(function () {
            console.log("ready!");
            $("#clear_all_null").on("click", function () {
                console.log("clear_all_null!");
                $.ajax({
                    type: 'POST',
                    url: "{{ route('api.inventories.clearallemply') }}",
                    headers: {
                        "X-Requested-With": 'XMLHttpRequest',
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        console.log("success!");
                        console.log(data);
                        $("#inventoriesListTable").bootstrapTable('refresh');
                    },
                });
            });
        });
    </script>
@stop

