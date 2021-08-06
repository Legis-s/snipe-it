<!-- Asset Model -->
<div id="{{ $fieldname }}" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }} ">

    {{ Form::label($fieldname, $translated_name, array('class' => 'col-md-3 control-label')) }}

    <div class="col-md-7{{  ((isset($required) && ($required =='true'))) ?  ' required' : '' }}">
        <select class="js-data-ajax4" data-endpoint="consumables" data-placeholder="Выберите расходник" name="{{ $fieldname }}" style="width: 100%" id="consumable_select_id" aria-label="{{ $fieldname }}">
            @if ($consumable_id = Input::old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
                <option value="{{ $consumable_id }}" selected="selected">
                    {{ (\App\Models\Consumable::find($consumable_id)) ? \App\Models\Consumable::find($consumable_id)->name : '' }}
                </option>
            @else
                <option value=""  role="option">Выберите расходник</option>
            @endif

        </select>
    </div>
    <div class="col-md-1 col-sm-1 text-left">
        @can('create', \App\Models\Consumable::class)
            @if ((!isset($hide_new)) || ($hide_new!='true'))
                <a href='{{ route('modal.model') }}' data-toggle="modal"  data-target="#createModal" data-select='model_select_id' class="btn btn-sm btn-primary">New</a>
                <span class="mac_spinner" style="padding-left: 10px; color: green; display:none; width: 30px;">
                    <i class="fa fa-spinner fa-spin" aria-hidden="true"></i>
                </span>
            @endif
        @endcan
    </div>

    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span></div>') !!}
</div>
