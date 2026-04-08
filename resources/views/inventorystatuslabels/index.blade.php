@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/inventorystatuslabels/table.title') }}
    @parent
@stop

@section('content')
    <x-container columns="2">
        <x-page-column class="col-md-9">
            <x-box>
                <x-table
                        name="inventoryStatuslabelsTable"
                        buttons="inventoryStatusButtons"
                        fixed_right_number="1"
                        fixed_number="1"
                        api_url="{{ route('api.inventorystatuslabels.index') }}"
                        :presenter="\App\Presenters\InventoryStatusLabelPresenter::dataTableLayout()"
                        export_filename="export-inventorystatuslabels-{{ date('Y-m-d') }}"
                />
            </x-box>
        </x-page-column>
        <x-page-column class="col-md-3">
        </x-page-column>
    </x-container>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')

    <script nonce="{{ csrf_token() }}">
        function colorSqFormatter(value, row) {
            if (value) {
                return '<span class="label" style="background-color: ' + value + ';">&nbsp;</span> ' + value;
            }
        }

        function statusLabelSuccessFormatter(row, value) {
            let text_color;
            let icon_style;
            let trans;
            if (value.success === "1") {
                text_color = 'green';
                icon_style = 'fa-circle';
                trans = 'Успешно';
            } else {
                text_color = 'red';
                icon_style = 'fa-circle';
                trans = 'Не успешно';
            }
            const typename_lower = trans;
            const typename = typename_lower.charAt(0).toUpperCase() + typename_lower.slice(1);
            return '<i class="fa ' + icon_style + ' text-' + text_color + '"></i> ' + typename;
        }
    </script>
@stop
