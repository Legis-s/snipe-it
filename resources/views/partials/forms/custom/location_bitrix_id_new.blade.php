<div class="form-group {{ $errors->has('bitrix_id_old') ? ' has-error' : '' }}">
    {{ Form::label('bitrix_id_old', trans('general.bitrix_id')." NEW", array('class' => 'col-md-3 control-label')) }}
    <div class="col-md-7">
        {{Form::number('bitrix_id_old', old('bitrix_id_old', ($item->bitrix_id_old ?? $item->bitrix_id_old ?? '')), array('class' => 'form-control', 'aria-label'=>'bitrix_id_old')) }}
        {!! $errors->first('bitrix_id_old', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>
