@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.contracts') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-page-column>
            <x-box>
                <x-table
                        name="contractsTable"
                        buttons="contractsButtons"
                        fixed_right_number="1"
                        fixed_number="1"
                        api_url="{{ route('api.contracts.index') }}"
                        :presenter="\App\Presenters\ContractPresenter::dataTableLayout()"
                        export_filename="export-contracts-{{ date('Y-m-d') }}"
                />
            </x-box>
        </x-page-column>
    </x-container>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop