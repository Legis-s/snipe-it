<div class="form-group" id="assignto_selector"{!!  (isset($style)) ? ' style="'.e($style).'"' : ''  !!}>
    <label for="checkout_to_type" class="col-md-3 control-label">{{ trans('admin/hardware/form.checkout_to') }}</label>
    <div class="col-md-8">

        <div class="btn-group" data-toggle="buttons">
            @if ((isset($location_select)) && ($location_select!='false'))
                <label class="btn btn-theme{{ (session('checkout_to_type') ?: 'location') == 'location' ? ' active' : '' }}">
                    <input name="checkout_to_type" value="location" aria-label="checkout_to_type"
                           type="radio" {{ (session('checkout_to_type') ?: 'location') == 'location' ? 'checked' : '' }}>
                    <x-icon type="location" />
                    {{ trans('general.location') }}
                </label>
            @endif
            @if ((isset($user_select)) && ($user_select!='false'))
                <label class="btn btn-theme{{ (session('checkout_to_type') ?: 'user') == 'user' ? ' active' : '' }}">
                    <input name="checkout_to_type" value="user" aria-label="checkout_to_type"
                           type="radio" {{ (session('checkout_to_type') ?: 'user') == 'user' ? 'checked' : '' }}>
                <x-icon type="user" />
                {{ trans('general.user') }}
            </label>
            @endif
            @if ((isset($asset_select)) && ($asset_select!='false'))
                <label class="btn btn-theme{{ session('checkout_to_type') == 'asset' ? ' active' : '' }}">
                    <input name="checkout_to_type" value="asset" aria-label="checkout_to_type"
                           type="radio" {{ session('checkout_to_type') == 'asset' ? 'checked': '' }}>
                <i class="fas fa-barcode" aria-hidden="true"></i>
                {{ trans('general.asset') }}
            </label>
            @endif
            @if ((isset($deal_select)) && ($deal_select!='false'))
                <label class="btn btn-theme{{ session('checkout_to_type') == 'deal' ? ' active' : '' }}">
                    <input name="checkout_to_type" value="deal" aria-label="checkout_to_type"
                           type="radio" {{ session('checkout_to_type') == 'deal' ? 'checked' : '' }}>
                <i class="fas fa-usd" aria-hidden="true"></i>
                {{ trans('general.deal') }}
            </label>
            @endif

            {!! $errors->first('checkout_to_type', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>
</div>
