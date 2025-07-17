<!-- Deal -->
<div  id="{{ $fieldname }}" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}"{!!  (isset($style)) ? ' style="'.e($style).'"' : ''  !!}>

    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>
    <div class="col-md-7">
        <select class="js-data-ajax" data-endpoint="deals" data-placeholder="{{ trans('general.select_deal') }}" name="{{ $fieldname }}" style="width: 100%" id="{{ $fieldname }}_deal_select" aria-label="{{ $fieldname }}"{{ (isset($multiple) && ($multiple=='true')) ? " multiple='multiple'" : '' }}{!!  ((isset($item)) && (Helper::checkIfRequired($item, $fieldname))) ? ' required ' : '' !!}>
            @isset($selected)
                @foreach($selected as $deal_id)
                    <option value="{{ $deal_id }}" selected="selected" role="option" aria-selected="true"  role="option">
                        {{ (\App\Models\Deal::find($deal_id))->name }}
                    </option>
                @endforeach
            @endisset
            @if ($deal_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
                <option value="{{ $deal_id }}" selected="selected" role="option" aria-selected="true"  role="option">
                    {{ (\App\Models\Deal::find($deal_id)) ? \App\Models\Deal::find($deal_id)->name : '' }}
                </option>
            @endif
        </select>
    </div>

    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}

    @if (isset($help_text))
        <div class="col-md-7 col-sm-11 col-md-offset-3">
            <p class="help-block">{{ $help_text }}</p>
        </div>
    @endif

</div>



