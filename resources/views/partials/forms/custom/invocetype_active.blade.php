<div class="form-group">
    <label for="active" class="col-md-3 control-label">{{ trans('general.activated') }}</label>
    <div class="col-md-9 checkbox">
        <label>
            <input type="hidden" name="active" value="0">
            <input type="checkbox" name="active" id="active" value="1" aria-label="{{ trans('general.activated') }}" @checked(old('active', $item->active))>
        </label>
    </div>
</div>
