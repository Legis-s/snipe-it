<div class="form-group {{ $errors->has('bitrix_id') ? ' has-error' : '' }}">
    {{ Form::label('bitrix_id', trans('general.bitrix_id'), array('class' => 'col-md-3 control-label')) }}
    <div class="col-md-7">
        {{Form::number('bitrix_id', old('bitrix_id', ($item->bitrix_id ?? $item->bitrix_id ?? '')), array('class' => 'form-control', "disabled" => "disabled",'aria-label'=>'bitrix_id')) }}
        {!! $errors->first('bitrix_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>
