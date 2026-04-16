<div class="form-group {{ $errors->has('bitrix_id') ? ' has-error' : '' }}">
    <label for="bitrix_id" class="col-md-3 control-label">{{ trans('general.bitrix_id') }}</label>
    <div class="col-md-7">
        <input class="form-control" aria-label="bitrix_id" name="bitrix_id" type="number" id="bitrix_id" value="{{ old('bitrix_id', $item->bitrix_id) }}">
        {!! $errors->first('bitrix_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>
