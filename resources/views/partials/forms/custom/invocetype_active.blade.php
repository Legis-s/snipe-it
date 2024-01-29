<div class="form-group">
    <label class="col-md-3 control-label">Активность</label>
    <div class="col-md-9 checkbox">
        <label>
            {{ Form::checkbox('active', '1', old('active', $item->active), ['aria-label'=>'active']) }}
        </label>
    </div>
</div>