@extends('layouts/edit-form', [
    'updateText' => trans('admin/invoicetypes/table.update'),
    'formAction' => (isset($item->id)) ? route('invoicetypes.update', ['invoicetype' => $item->id]) : route('invoicetypes.store'),
])

{{-- Page content --}}
@section('inputFields')

@include ('partials.forms.custom.invocetype_bitrix_id')
@include ('partials.forms.custom.invocetype_active')

@stop

