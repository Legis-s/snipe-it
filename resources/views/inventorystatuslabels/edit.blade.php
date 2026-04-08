@extends('layouts/edit-form', [
    'createText' => trans('admin/inventorystatuslabels/table.create') ,
    'updateText' => trans('admin/inventorystatuslabels/table.update'),
    'helpTitle' => trans('admin/inventorystatuslabels/table.about'),
    'helpText' => trans('admin/inventorystatuslabels/table.info'),
    'formAction' => (isset($item->id)) ? route('inventorystatuslabels.update', ['inventorystatuslabel' => $item->id]) : route('inventorystatuslabels.store'),
])

{{-- Page content --}}
@section('content')
    <style>
        .input-group-addon {
            width: 30px;
        }
    </style>

    @parent
@stop

@section('inputFields')

@include ('partials.forms.edit.name', ['translated_name' => trans('general.name')])

<!-- Chart color -->
<div class="form-group{{ $errors->has('color') ? ' has-error' : '' }}">
    <label for="color" class="col-md-3 control-label">{{ trans('admin/statuslabels/table.color') }}</label>
    <div class="col-md-9">
        <x-input.colorpicker :item="$item" id="color" :value="old('color', ($item->color ?? '#f4f4f4'))" name="color" id="color" />
        {!! $errors->first('color', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
    </div>
</div>

@include ('partials.forms.edit.notes')

<!-- Show in Nav -->
<div class="form-group{{ $errors->has('notes') ? ' has-error' : '' }}">
    <div class="col-md-9 col-md-offset-3">
        <label class="form-control">
            <input type="checkbox" value="1" name="show_in_nav" id="show_in_nav" {{ old('show_in_nav', $item->show_in_nav) == '1' ? ' checked="checked"' : '' }}> {{ trans('admin/statuslabels/table.show_in_nav') }}
        </label>
    </div>
</div>
@stop

@section('moar_scripts')
    <!-- bootstrap color picker -->
    <script nonce="{{ csrf_token() }}">

        $(function() {
            $('.color').colorpicker({
                color: `{{ old('color', $item->color) ?: '#AA3399' }}`,
                format: 'hex'
            });
        });

    </script>

@stop
