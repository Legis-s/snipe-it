@extends('layouts/default')

{{-- Page title --}}
@section('title')
  Выдать расходники
  @parent
@stop

{{-- Page content --}}
@section('content')

  <div class="row">
    <div class="col-md-9">

      <form class="form-horizontal" method="post" action="" autocomplete="off">
        <!-- CSRF Token -->
        <input type="hidden" name="_token" value="{{ csrf_token() }}" />

        <div class="box box-default">

          @if ($consumable->id)
            <div class="box-header with-border">
              <div class="box-heading">
                <h2 class="box-title">{{ $consumable->name }} </h2>
              </div>
            </div><!-- /.box-header -->
          @endif

          <div class="box-body">
            @if ($consumable->name)
              <!-- consumable name -->
              <div class="form-group">
                <label class="col-sm-3 control-label">{{ trans('admin/consumables/general.consumable_name') }}</label>
                <div class="col-md-6">
                  <p class="form-control-static">{{ $consumable->name }}</p>
                </div>
              </div>
            @endif

{{--          <!-- User -->--}}
{{--            @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.select_user'), 'fieldname' => 'assigned_to', 'required'=> 'true'])--}}

{{--            --}}
            @include ('partials.forms.checkout-selector', ['user_select' => 'true','asset_select' => 'true', 'location_select' => 'true'])

            @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'assigned_location', 'required'=>'true'])

            @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.user'), 'fieldname' => 'assigned_user', 'style' => 'display:none;','required'=>'true'])

            @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.asset'), 'fieldname' => 'assigned_asset', 'unselect' => 'true', 'style' => 'display:none;', 'required'=>'true'])

            @include ('partials.forms.edit.quantity_max')



            @if ($consumable->requireAcceptance() || $consumable->getEula() || ($snipeSettings->slack_endpoint!=''))
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

                    @if ($snipeSettings->slack_endpoint!='')
                      <i class="fab fa-slack"></i>
                      A slack message will be sent
                    @endif
                  </div>
                </div>
              </div>
            @endif
            <!-- Note -->
            <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
              <label for="note" class="col-md-3 control-label">{{ trans('admin/hardware/form.notes') }}</label>
              <div class="col-md-7">
                <textarea class="col-md-6 form-control" name="note">{{ old('note') }}</textarea>
                {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
              </div>
            </div>
          </div> <!-- .box-body -->
          <div class="box-footer">
            <a class="btn btn-link" href="{{ route('consumables.show', ['consumable'=> $consumable->id]) }}">{{ trans('button.cancel') }}</a>
            <button type="submit" class="btn btn-primary pull-right"><i class="fas fa-check icon-white" aria-hidden="true"></i> {{ trans('general.checkout') }}</button>
          </div>
        </div>
      </form>

    </div>
  </div>
@stop