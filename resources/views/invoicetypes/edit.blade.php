@extends('layouts/edit-form', [
    'updateText' => trans('admin/invoicetypes/table.update'),
    'formAction' => route('invoicetypes.update', ['invoicetype' => $item->id]),
])

{{-- Page content --}}
@section('inputFields')

@include ('partials.forms.custom.invocetype_bitrix_id')
@include ('partials.forms.custom.invocetype_active')

@stop
