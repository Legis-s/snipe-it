<div class="form-group {{ $errors->has('invoice_file') ? 'has-error' : '' }}">
    <label class="col-md-3 control-label" for="invoice_file">Загрузить счет</label>
    <div class="col-md-8">

        <input type="file" id="invoice_file" name="invoice_file" aria-label="invoice_file" class="sr-only">

        <label class="btn btn-default" aria-hidden="true">
            {{ trans('button.select_file')  }}
            <input type="file" name="invoice_file" required class="js-uploadFile" id="uploadFile"
                   data-maxsize="{{ Helper::file_upload_max_size() }}"
                   accept="image/*, application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/pdf, application/x-pdf, application/x-bzpdf, application/x-gzpdf, image/jpeg, application/rtf, application/x-rtf, text/rtf, text/richtext"
                   style="display:none; max-width: 90%" aria-label="invoice_file" aria-hidden="true">
        </label>
        <span class='label label-default' id="uploadFile-info"></span>

        <p class="help-block"
           id="uploadFile-status">{{ trans('general.document_filetypes_help', ['size' => Helper::file_upload_max_size_readable()]) }} {{ $help_text ?? '' }}</p>

        {!! $errors->first('invoice_file', '<span class="alert-msg" aria-hidden="true">:message</span>') !!}
    </div>
</div>



