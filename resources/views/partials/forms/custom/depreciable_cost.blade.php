<!-- Purchase Cost -->
<div class="form-group {{ $errors->has('depreciable_cost') ? ' has-error' : '' }}">
    <label for="purchase_cost" class="col-md-3 control-label">{{ trans('general.depreciable_cost') }}</label>
    <div class="col-md-9">
        <div class="input-group col-md-6" style="padding-left: 0px;">
            <input class="form-control float" type="text" name="depreciable_cost" aria-label="depreciable_cost" id="depreciable_cost" value="{{ Request::old('depreciable_cost', \App\Helpers\Helper::formatCurrencyOutput($item->depreciable_cost)) }}" />
            <span class="input-group-addon">
                @if (isset($currency_type))
                    {{ $currency_type }}
                @else
                    {{ $snipeSettings->default_currency }}
                @endif
            </span>
        </div>
        <div class="col-md-6" style="padding-left: 0px;">
            {!! $errors->first('depreciable_cost', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>

</div>
