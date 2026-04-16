{{--                        Bitrix task id--}}
<div class="form-group {{ $errors->has('bitrix_task_id') ? 'error' : '' }}">
    <label for="bitrix_task_id" class="col-md-3 control-label">{{ trans('general.bitrix_task_id') }}</label>
    <div class="col-md-7">
        <div class="col-md-8" style="padding-left:0px">
            <input class="form-control" type="number" min="0" name="bitrix_task_id" aria-label="bitrix_task_id" id="bitrix_task_id" value="0"/>
        </div>
        <div class="col-md-4" style="padding-left: 0px;">
            {!! $errors->first('bitrix_task_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>
</div>