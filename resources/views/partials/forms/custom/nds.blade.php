<!-- Purchase Cost -->
<div class="form-group {{ $errors->has('nds') ? ' has-error' : '' }}">
    <label for="nds" class="col-md-3 control-label">НДС</label>
    <div class="col-md-9">
        <div class="input-group col-md-6" style="padding-left: 0px;">
            <input class="form-control" type="number" min="0" name="nds" aria-label="nds" id="nds" value="{{  $item->nds }}" />
            <span class="input-group-addon">%</span>
        </div>
        <div class="col-md-6" style="padding-left: 0px;">
            {!! $errors->first('nds', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
        </div>
    </div>
</div>
