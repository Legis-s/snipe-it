<div class="form-group">
    <label class="col-md-3 control-label">{{ trans('general.sklad') }}</label>
    <div class="col-md-9 checkbox">
        <label>
            {{ Form::checkbox('sklad', '1', old('sklad', $item->sklad), ['aria-label'=>'sklad']) }}
        </label>
    </div>
</div>