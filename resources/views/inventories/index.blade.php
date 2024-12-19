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

    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box-body">
                            <table
                                    data-click-to-select="true"
                                    data-columns="{{ \App\Presenters\InventoryPresenter::dataTableLayout() }}"
                                    data-cookie-id-table="inventoriesListTable"
                                    data-pagination="true"
                                    data-id-table="inventoriesListTable"
                                    data-search="true"
                                    data-side-pagination="server"
                                    data-show-columns="true"
                                    data-show-export="true"
                                    data-show-refresh="true"
                                    data-sort-order="desc"
                                    data-toolbar="#toolbar"
                                    id="inventoriesListTable"
                                    class="table table-striped snipe-table"
                                    data-url="{{ route('api.inventories.index') }}">
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')

    <script type="text/javascript">
        $( document ).ready(function() {
            console.log( "ready!" );
            $( "#clear_all_null" ).on( "click", function() {
                console.log( "clear_all_null!" );
                $.ajax({
                    type: 'POST',
                    url:"{{ route('api.inventories.clearallemply') }}",
                    headers: {
                        "X-Requested-With": 'XMLHttpRequest',
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (data) {
                        console.log( "success!" );
                        console.log( data );
                        $("#inventoriesListTable").bootstrapTable('refresh');
                    },
                });
            } );
        });
    </script>
@stop

