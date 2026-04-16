<div class="form-group">
    <label for="sklad" class="col-md-3 control-label">{{ __('general.sklad') }}</label>
    <div class="col-md-9">
        <input
                type="checkbox"
                id="sklad"
                name="sklad"
                value="1"
                aria-label="sklad"
                {{ old('sklad', $item->sklad) ? 'checked' : '' }}
        >
    </div>
</div>