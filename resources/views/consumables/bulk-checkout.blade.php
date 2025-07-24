@extends('layouts/default')

{{-- Page title --}}
@section('title')
     {{ trans('admin/hardware/general.bulk_checkout') }} расходников
@parent
@stop

{{-- Page content --}}
@section('content')

<style>
  .input-group {
    padding-left: 0px !important;
  }
</style>


<div class="row">
  <!-- left column -->
  <div class="col-md-7">
    <div class="box box-default">
      <div class="box-header with-border"></div>
      <div class="box-body">
        <form class="form-horizontal" method="post" action="" autocomplete="off">
          {{ csrf_field() }}
            @include ('partials.forms.custom.consumables-select', [
           'translated_name' => trans('general.consumables'),
           'fieldname' => 'selected_consumables[]',
           'multiple' => true,
           'required' => true,
           'consumable_status_type' => 'notnull',
           'select_id' => 'assigned_consumables_select',
           'consumable_selector_div_id' => 'consumables_to_checkout_div',
           'consumable_ids' =>  old('selected_consumables', $selected_consumables),
         ])
            <!-- Checkout selector -->
            @include ('partials.forms.checkout-selector', ['user_select' => 'true','asset_select' => 'true', 'location_select' => 'true', 'deal_select' => 'true'])

            @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.user'), 'fieldname' => 'assigned_user', 'unselect' => 'true', 'style' => 'display:none;', 'hide_new' => true])
            @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.asset'), 'asset_selector_div_id' => 'assigned_asset', 'fieldname' => 'assigned_asset', 'unselect' => 'true', 'style' => 'display:none;'])
            @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'assigned_location', 'hide_new' => true])
            @include ('partials.forms.custom.deal-select', ['translated_name' => trans('general.deal'), 'fieldname' => 'assigned_deal', 'style' => 'display:none;', 'hide_new' => true])

            <select id="consumables_json" name="consumables_json" hidden></select>

            <!-- Note -->
            <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
                <label for="note" class="col-sm-3 control-label">
                    {{ trans('general.notes') }}
                </label>
                <div class="col-md-8">
                    <textarea class="col-md-6 form-control" id="note" name="note">{{ old('note') }}</textarea>
                    {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                </div>
            </div>
      </div>
      <div class="box-footer">
          <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
          <button type="submit" class="btn btn-primary pull-right"><x-icon type="checkmark" /> {{ trans('general.checkout') }}</button>
      </div>
  </div>
    </form>
</div>
    <!-- right column -->
    <div class="col-md-5">
        <div class="box box-default">
                <div class="box-header with-border"></div>
                <div class="box-body">
                    <table id="table_consumables" class="table table-striped snipe-table" >
                        <thead>
                        <th>#</th>
                        <th>Модель</th>
                        <th>Количество</th>
                        <th>Удалить</th>
                        </thead>
                    </table>
                </div>
            </div>
    </div>
@stop


@section('moar_scripts')
        @include ('partials.bootstrap-table')

        <script nonce="{{ csrf_token() }}">
         $(function () {
             var table_consumables = $('#table_consumables');

             $('#assigned_consumables_select').on('select2:select', function (e) {
                 const data = e.params.data;
                 let remaining = data.numRemaining;

                 if (!data.selected_quantity) {
                     data.selected_quantity = 1;
                 } else {
                     remaining = remaining - data.selected_quantity;
                 }

                 Swal.fire({
                     title: "Выберите количество:<br>" + data.text,
                     icon: 'question',
                     input: "range",
                     inputLabel: 'Максимум ' + remaining,
                     inputAttributes: {
                         min: 1,
                         max: remaining,
                         step: "1"
                     },
                     inputValue: data.selected_quantity,
                     reverseButtons: true,
                     showCancelButton: true,
                     confirmButtonText: 'Подтвердить',
                     cancelButtonText: 'Отменить',
                     allowOutsideClick: false,
                     allowEscapeKey: false
                 }).then((result) => {
                     if (result.isConfirmed) {
                         var tabele_data = table_consumables.bootstrapTable('getData');
                         var c_data = {
                             id: tabele_data.length + 1,
                             consumable_id: data.id,
                             consumable: data.text,
                             quantity: result.value,
                         };
                         table_consumables.bootstrapTable('append', c_data);
                     } else {
                         $('#assigned_consumables_select option[value="'+data.id+'"]').prop('selected', false);
                         $('#assigned_consumables_select').trigger('change.select2');
                     }
                 });
             });


             $('form').on('submit', function() {
                 var tabele_data = table_consumables.bootstrapTable('getData');
                 let jsonString = JSON.stringify(tabele_data);
                 const hiddenInput = document.createElement('input');
                 hiddenInput.type = 'hidden';
                 hiddenInput.name = 'consumables_json';
                 hiddenInput.value = jsonString;
                 this.appendChild(hiddenInput);
                 return true;
             });

             table_consumables.bootstrapTable('destroy').bootstrapTable({
                 locale: 'ru',
                 data: [],
                 search: true,
                 showRefresh: false,
                 columns: [{
                     field: 'id',
                     name: '#',
                     align: 'left',
                     valign: 'middle'
                 }, {
                     field: 'consumable',
                     name: 'Модель',
                     align: 'left',
                     valign: 'middle'
                 }, {
                     field: 'quantity',
                     name: 'Количество',
                     align: 'center',
                     valign: 'middle'
                 }
                     , {
                         align: 'center',
                         valign: 'middle',
                         events: {
                             'click .remove': function (e, value, row, index) {
                                 table_consumables.bootstrapTable('remove', {
                                     field: 'id',
                                     values: [row.id]
                                 });
                                 var data = table_consumables.bootstrapTable('getData');
                                 var newData = [];
                                 var count = 0;
                                 data.forEach(function callback(currentValue, index, array) {
                                     count++;
                                     currentValue.id = count;
                                     newData.push(currentValue);
                                 });
                                 table_consumables.bootstrapTable('load', newData);

                                 $('#assigned_consumables_select option[value="'+row.consumable_id+'"]').prop('selected', false);
                                 $('#assigned_consumables_select').trigger('change.select2');
                             }
                         },
                         formatter: function () {
                             return [
                                 '<a class="remove text-danger"  href="javascript:void(0)" title="Убрать">',
                                 '<i class="remove fa fa-times fa-lg"></i>',
                                 '</a>'
                             ].join('')
                         }
                     }
                 ]
             });
         });
    </script>
@stop

