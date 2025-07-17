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
      <div class="box-header with-border">
        <h2 class="box-title"> {{ trans('admin/hardware/form.tag') }} </h2>
      </div>
      <div class="box-body">
        <form class="form-horizontal" method="post" action="" autocomplete="off">
          {{ csrf_field() }}
            @include ('partials.forms.custom.consumables-select', [
           'translated_name' => trans('general.consumables'),
           'fieldname' => 'selected_consumables[]',
           'multiple' => true,
           'required' => true,
           'consumable_status_type' => 'RTD',
           'select_id' => 'assigned_consumables_select',
           'consumable_selector_div_id' => 'consumableS_to_checkout_div',
           'consumable_ids' =>  old('selected_consumables', $selected_consumables),
         ])
            <!-- Checkout selector -->
            @include ('partials.forms.checkout-selector', ['user_select' => 'true','asset_select' => 'true', 'location_select' => 'true', 'deal_select' => 'true'])

            @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.user'), 'fieldname' => 'assigned_user', 'unselect' => 'true', 'style' => 'display:none;', 'hide_new' => true])
            @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.asset'), 'asset_selector_div_id' => 'assigned_asset', 'fieldname' => 'assigned_asset', 'unselect' => 'true', 'style' => 'display:none;'])
            @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'assigned_location', 'hide_new' => true])
            @include ('partials.forms.custom.deal-select', ['translated_name' => trans('general.deal'), 'fieldname' => 'assigned_deal', 'style' => 'display:none;', 'hide_new' => true])

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
        </form>
      </div>
      <div class="box-footer">
          <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
          <button type="submit" class="btn btn-primary pull-right"><x-icon type="checkmark" /> {{ trans('general.checkout') }}</button>
      </div>
  </div>
    </form>
</div>
@stop


@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        // $(function () {});
    </script>
@stop

