<!-- Location -->
<div id="{{ $fieldname }}" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}"{!!  (isset($style)) ? ' style="'.e($style).'"' : ''  !!}>

    {{ Form::label($fieldname, $translated_name, array('class' => 'col-md-3 control-label')) }}
    <div class="col-md-7{{  ((isset($required) && ($required =='true'))) ?  ' required' : '' }}">
        <select class="js-data-ajax" data-endpoint="contracts" data-placeholder="Выберите договор" name="{{ $fieldname }}" style="width: 100%" id="{{ $fieldname }}_contract_select" aria-label="{{ $fieldname }}">
            @if ($contract_id = Input::old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
                <option value="{{ $contract_id }}" selected="selected" role="option" aria-selected="true"  role="option">
                    {{ (\App\Models\Contract::find($contract_id)) ? \App\Models\Contract::find($contract_id)->name : '' }}
                </option>
            @else
                <option value=""  role="option">Выберите договор</option>
            @endif
        </select>
    </div>

    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span></div>') !!}

    @if (isset($help_text))
        <div class="col-md-7 col-sm-11 col-md-offset-3">
            <p class="help-block">{{ $help_text }}</p>
        </div>
    @endif


</div>



