<!-- invoice_number -->
<div class="form-group {{ $errors->has('delivery_cost') ? ' has-error' : '' }}">
    <label for="delivery_cost" class="col-md-3 control-label">{{ $translated_name }}</label>
    <div class="col-md-7 col-sm-12{{  (\App\Helpers\Helper::checkIfRequired($item, 'delivery_cost')) ? ' required' : '' }}">
        <input class="form-control float" type="text"  name="delivery_cost" aria-label="delivery_cost" id="delivery_cost" value="{{ old('delivery_cost', $item->delivery_cost) }}" />
        {!! $errors->first('delivery_cost', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
    </div>
</div>