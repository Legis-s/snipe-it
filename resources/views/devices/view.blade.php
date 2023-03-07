@extends('layouts/default')

{{-- Page title --}}
@section('title')

    Телефон:
    {{ $device->number }}

    @parent
@stop

{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-9">

        </div><!--/.col-md-9-->

    </div>
    </div>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', [
        'exportFile' => 'locations-export',
        'search' => true
     ])
@stop