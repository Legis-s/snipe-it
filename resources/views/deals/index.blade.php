@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.deals') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-page-column class="col-md-12">
            <x-box>
                <x-table
                        name="dealsTable"
                        buttons="dealsButtons"
                        fixed_right_number="1"
                        fixed_number="1"
                        api_url="{{ route('api.deals.index') }}"
                        :presenter="\App\Presenters\DealPresenter::dataTableLayout()"
                        export_filename="export-deals-{{ date('Y-m-d') }}"
                />
            </x-box>
        </x-page-column>
    </x-container>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table')
@stop