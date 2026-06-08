@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('admin/consumables/general.checkout') }}
@parent
@stop

{{-- Page content --}}
@section('content')

<x-container class="col-md-9">

    <x-form route="{{ url()->current() }}" id="checkout_form">

        <x-box header="{{ $consumable->name }}">

            @if ($consumable->name)
                <x-form.static :label="trans('admin/consumables/general.consumable_name')">{{ $consumable->name }}</x-form.static>
            @endif

            @if ($consumable->company)
                <x-form.static :label="trans('general.company')">{!! $consumable->company->present()->formattedNameLink !!}</x-form.static>
            @endif

            @if ($consumable->category)
                <x-form.static :label="trans('general.category')">{!! $consumable->category->present()->formattedNameLink !!}</x-form.static>
            @endif

            <x-form.static :label="trans('admin/components/general.total')">{{ $consumable->qty }}</x-form.static>

            <x-form.static :label="trans('admin/components/general.remaining')">{{ $consumable->numRemaining() }}</x-form.static>




          <!-- User -->
            @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.select_user'), 'fieldname' => 'assigned_to', 'required'=> 'true'])


            @if ($consumable->requireAcceptance() || $consumable->getEula() || ($snipeSettings->webhook_endpoint!=''))
              <div class="form-group notification-callout">
                <div class="col-md-8 col-md-offset-3">
                  <div class="callout callout-info">

                    @if ($consumable->category->require_acceptance=='1')
                      <i class="far fa-envelope"></i>
                      {{ trans('admin/categories/general.required_acceptance') }}
                      <br>
                    @endif

                    @if ($consumable->getEula())
                      <i class="far fa-envelope"></i>
                      {{ trans('admin/categories/general.required_eula') }}
                        <br>
                    @endif

                    @if ($snipeSettings->webhook_endpoint!='')
                        <i class="fab fa-slack"></i>
                        {{ trans('general.webhook_msg_note') }}
                    @endif
                  </div>
                </div>
              </div>
            @endif

          <!-- Checkout QTY -->
          <div class="form-group {{ $errors->has('qty') ? 'error' : '' }} ">
              <label for="qty" class="col-md-3 control-label">{{ trans('general.qty') }}</label>
              <div class="col-md-7 col-sm-12 required">
                  <div class="col-md-2" style="padding-left:0px">
                    <input class="form-control" type="number" name="checkout_qty" id="checkout_qty" value="1" min="1" max="{{$consumable->numRemaining()}}" maxlength="999999"  />
                  </div>
              </div>
              {!! $errors->first('qty', '<div class="col-md-8 col-md-offset-3"><span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span></div>') !!}
          </div>
          
          <!-- Note -->
          <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
            <label for="note" class="col-md-3 control-label">{{ trans('admin/hardware/form.notes') }}</label>
            <div class="col-md-7">
              <textarea class="col-md-6 form-control" name="note">{{ old('note') }}</textarea>
              {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
            </div>

            <x-form.row
                :label="trans('admin/hardware/form.notes')"
                :item="$consumable"
                name="note"
                type="textarea"
            />

            <x-slot:customfooter>
                <x-redirect_submit_options
                    index_route="consumables.index"
                    :button_label="trans('general.checkout')"
                    :options="[
                        'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.consumables')]),
                        'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.consumable')]),
                        'target' => trans('admin/hardware/form.redirect_to_checked_out_to'),
                    ]"
                />
            </x-slot:customfooter>

        </x-box>

    </x-form>

</x-container>

@stop
