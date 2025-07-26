<div id="assigned_user" class="form-group{{ $errors->has($fieldname) ? ' has-error' : '' }}">

    <label for="{{ $fieldname }}" class="col-md-3 control-label">{{ $translated_name }}</label>

    <div class="col-md-7">
        <select class="js-data-ajax" data-endpoint="invoice_types" data-placeholder="Выберите тип счета" name="{{ $fieldname }}" style="width: 100%" id="invoice_type_select" aria-label="{{ $fieldname }}"{{ (isset($multiple) && ($multiple=='true')) ? " multiple='multiple'" : '' }}{{ (isset($item) && (Helper::checkIfRequired($item, $fieldname))) ? ' required' : '' }}>
            @isset ($selected)
                @foreach ($selected as $invoice_type_id)
                    <option value="{{ $invoice_type_id }}" selected="selected" role="option" aria-selected="true">
                        {{ \App\Models\InvoiceType::find($invoice_type_id)->name }}
                    </option>
                @endforeach
            @endisset
            @if ($invoice_type_id = old($fieldname, (isset($item)) ? $item->{$fieldname} : ''))
                <option value="{{ $invoice_type_id }}" selected="selected" role="option" aria-selected="true" role="option">
                    {{ (\App\Models\InvoiceType::find($invoice_type_id)) ? \App\Models\InvoiceType::find($invoice_type_id)->name : '' }}
                </option>
            @endif
        </select>
    </div>

    {!! $errors->first($fieldname, '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}

</div>
