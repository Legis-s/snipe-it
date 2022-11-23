<!-- QTY -->
<div class="form-group {{ $errors->has('qty') ? ' has-error' : '' }}">
    <label for="qty" class="col-md-3 control-label">{{ trans('general.quantity') }}</label>
    <div class="col-md-7">
        <div class="col-md-8" style="padding-left:0px">
            <input class="form-control" type="number" min="0" name="quantity" aria-label="quantity" id="quantity" value="1"/>
        </div>
        <div class="col-md-4" style="padding-left: 0px;">
        {!! $errors->first('qty', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>
</div>