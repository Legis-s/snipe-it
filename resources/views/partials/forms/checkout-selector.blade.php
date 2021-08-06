<div class="form-group" id="assignto_selector"{!!  (isset($style)) ? ' style="'.e($style).'"' : ''  !!}>
    {{ Form::label('checkout_to_type', trans('admin/hardware/form.checkout_to'), array('class' => 'col-md-3 control-label')) }}
    <div class="col-md-8">
        <div class="btn-group" data-toggle="buttons">
            @if ((isset($location_select)) && ($location_select!='false'))
                <label class="btn btn-default active">
                    <input name="checkout_to_type" value="location" aria-label="checkout_to_type" class="active" type="radio" checked="checked" ><i class="fa fa-map-marker" aria-hidden="true"></i> {{ trans('general.location') }}
                </label>
            @endif

            @if ((isset($user_select)) && ($user_select!='false'))
            <label class="btn btn-default
 @if ((isset($location_select)) && ($location_select!='false'))
            @else
 active"
            @endif     >
                <input name="checkout_to_type" value="user" aria-label="checkout_to_type" type="radio"
                @if ((isset($location_select)) && ($location_select!='false'))
                        @else
                class="active" checked="checked"
                        @endif
                ><i class="fa fa-user" aria-hidden="true"></i> {{ trans('general.user') }}
            </label>
            @endif
            @if ((isset($asset_select)) && ($asset_select!='false'))
            <label class="btn btn-default">
                <input name="checkout_to_type" value="asset" aria-label="checkout_to_type" type="radio"><i class="fa fa-barcode" aria-hidden="true"></i> {{ trans('general.asset') }}
            </label>
            @endif

                @if ((isset($contract_select)) && ($contract_select!='false'))
                    <label class="btn btn-default">
                        <input name="checkout_to_type" value="contract" aria-label="checkout_to_type" type="radio"><i class="fa fa-file" aria-hidden="true"></i> Договор
                    </label>
                @endif

            {!! $errors->first('checkout_to_type', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>
</div>
