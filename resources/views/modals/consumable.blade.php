{{-- See snipeit_modals.js for what powers this --}}
<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h2 class="modal-title">Создать расходник</h2>
        </div>
        <div class="modal-body">
            <form action="{{ route('api.consumables.store') }}" onsubmit="return false">
                <div class="alert alert-danger" id="modal_error_msg" style="display:none"></div>
                <div class="dynamic-form-row">
                    <div class="col-md-4 col-xs-12"><label for="modal-name">{{ trans('general.name') }}:</label></div>
                    <div class="col-md-8 col-xs-12 required"><input type='text' name="name" id='modal-name' class="form-control"></div>
                </div>
                <div class="dynamic-form-row">
                    <div class="col-md-4 col-xs-12"><label for="modal-category_id">{{ trans('general.category') }}:</label></div>
                    <div class="col-md-8 col-xs-12 required">
                        <select class="js-data-ajax" data-endpoint="categories/consumable" name="category_id" style="width: 100%" id="modal-category_id"></select>
                    </div>
                </div>
                <div class="dynamic-form-row">
                    <div class="col-md-4 col-xs-12"><label for="2modal-manufacturer_id">{{ trans('general.manufacturer') }}:
                        </label></div>
                    <div class="col-md-8 col-xs-12 required">
                        <select class="js-data-ajax" data-endpoint="manufacturers" name="manufacturer_id" style="width: 100%" id="2modal-manufactuer_id"></select>
                    </div>
                </div>
                <div class="dynamic-form-row">
                    <div class="col-md-4 col-xs-12"><label for="model_number">{{ trans('general.model_no') }}:</label></div>
                    <div class="col-md-8 col-xs-12"><input type='text' name="model_number" id='modal-model_number' class="form-control"></div>
                </div>
                <div class="dynamic-form-row">
                    <div class="col-md-4 col-xs-12"><label for="modal-location_id">{{ trans('general.location') }}:</label></div>
                    <div class="col-md-8 col-xs-12 required">
                        <select class="js-data-ajax" data-endpoint="locations" name="location_id" style="width: 100%" id="modal-location_id"></select>
                    </div>
                </div>
                <input type='text' name="qty" id="qty"  value="0" hidden>
            </form>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('button.cancel') }}</button>
            <button type="button" class="btn btn-primary" id="modal-save">{{ trans('general.save') }}</button>
        </div>
    </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->
