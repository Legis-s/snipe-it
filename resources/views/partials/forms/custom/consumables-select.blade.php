<!-- Consumable -->
<div id="{{ $consumable_selector_div_id ?? "assigned_consumable" }}"
     class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}"{!!  (isset($style)) ? ' style="'.e($style).'"' : ''  !!}>
    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>
    <div class="col-md-7">
        <select class="js-data-ajax select2"
                data-endpoint="consumables"
                data-placeholder="{{ trans('general.select_consumable') }}"
                aria-label="{{ $fieldname }}"
                name="{{ $fieldname }}"
                style="width: 100%"
                id="{{ (isset($select_id)) ? $select_id : 'assigned_consumable_select' }}"
                data-template-selection="formatState"
                {{ ((isset($multiple)) && ($multiple === true)) ? ' multiple' : '' }}
                {!! (!empty($consumable_status_type)) ? ' data-asset-status-type="' . $consumable_status_type . '"' : '' !!}
                {!! (!empty($company_id)) ? ' data-company-id="' .$company_id.'"'  : '' !!}
                {{  ((isset($required) && ($required =='true'))) ?  ' required' : '' }}
        >

            @if ((!isset($unselect)) && ($consumable_id = old($fieldname, (isset($consumable) ? $consumable->id  : (isset($item) ? $item->{$fieldname} : '')))))
                <option value="{{ $consumable_id }}" selected="selected" role="option" aria-selected="true"  role="option">
                    {{ (\App\Models\Consumable::find($consumable_id)) ? \App\Models\Consumable::find($consumable_id)->present()->fullName : '' }}
                </option>
            @else
                @if(!isset($multiple))
                    <option value=""  role="option">{{ trans('general.select_consumable') }}</option>
                @else
                    @if(isset($consumable_ids))
                        @foreach($consumable_ids as $consumable_id)
                            <option value="{{ $consumable_id }}" selected="selected" role="option" aria-selected="true"
                                    role="option">
                                {{ (\App\Models\Consumable::find($consumable_id)) ? \App\Models\Consumable::find($consumable_id)->present()->fullName : '' }}
                            </option>
                        @endforeach
                    @endif
                @endif
            @endif
        </select>
    </div>
    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}

</div>
