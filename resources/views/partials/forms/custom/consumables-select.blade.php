<!-- Asset Model -->
<div id="{{ $fieldname }}" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }} ">

    {{ Form::label($fieldname, $translated_name, array('class' => 'col-md-3 control-label')) }}

    <div class="col-md-7{{  ((isset($required) && ($required =='true'))) ?  ' required' : '' }}">
        <select data-endpoint="consumables" data-placeholder="Выберите расходник" name="{{ $fieldname }}" style="width: 100%" id="consumable_select_id" aria-label="{{ $fieldname }}" {!! (!empty($asset_status_type)) ? ' data-asset-status-type="' . $asset_status_type . '"' : '' !!}>
            @if ($consumable_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
                <option value="{{ $consumable_id }}" selected="selected">
                    {{ (\App\Models\Consumable::find($consumable_id)) ? \App\Models\Consumable::find($consumable_id)->name : '' }}
                </option>
            @else
                <option value=""  role="option">Выберите расходник</option>
            @endif
        </select>
    </div>
    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span></div>') !!}
</div>
