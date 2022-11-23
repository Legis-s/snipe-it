<div class="form-group" id="assignto_selector"{!!  (isset($style)) ? ' style="'.e($style).'"' : ''  !!}>
    {{ Form::label('checkout_to_type', trans('admin/hardware/form.checkout_to'), array('class' => 'col-md-3 control-label')) }}
    <div class="col-md-8">
        <div class="btn-group" data-toggle="buttons">
            @if ((isset($user_select)) && ($user_select!='false'))
            <label class="btn btn-default active">
                <input name="checkout_to_type_s" value="user" aria-label="checkout_to_type_s" type="radio" checked="checked"><i class="fas fa-user" aria-hidden="true"></i> {{ trans('general.user') }}
            </label>
            @endif
            @if ((isset($location_select)) && ($location_select!='false'))
            <label class="btn btn-default">
                <input name="checkout_to_type_s" value="location" aria-label="checkout_to_type_s" class="active" type="radio"><i class="fas fa-map-marker" aria-hidden="true"></i> {{ trans('general.location') }}
            </label>
            @endif
                @if ((isset($contract_select)) && ($contract_select!='false'))
                    <label class="btn btn-default">
                        <input name="checkout_to_type_s" value="contract" aria-label="checkout_to_type_s" class="active" type="radio"><i class="fas fa-file-lines" aria-hidden="true"></i> {{ trans('general.contract') }}
                    </label>
                @endif
            {!! $errors->first('checkout_to_type_s', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>
</div>
