@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Продажа
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
                <form class="form-horizontal" method="post" action="" autocomplete="off">
                    <div class="box-header with-border">
                        <h2 class="box-title"> {{ trans('admin/hardware/form.tag') }} {{ $item->asset_tag }}</h2>
                    </div>
                    <div class="box-body">
                    {{csrf_field()}}
                    <!-- AssetModel name -->
                        <div class="form-group">
                            {{ Form::label('model', trans('admin/hardware/form.model'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <p class="form-control-static">
                                    @if (($item->model) && ($item->model->name))
                                        {{ $item->model->name }}

                                    @else
                                        <span class="text-danger text-bold">
                  <i class="fa fa-exclamation-triangle"></i>This asset's model is invalid!
                  The asset <a href="{{ route('hardware.edit', $item->id) }}">should be edited</a> to correct this before attempting to check it in or out.</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Asset Name -->
                        <div class="form-group {{ $errors->has('name') ? 'error' : '' }}">
                            {{ Form::label('name', trans('admin/hardware/form.name'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <input class="form-control" type="text" name="name" id="name"
                                       value="{{ Input::old('name', $item->name) }}" tabindex="1">
                                {!! $errors->first('name', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>
{{--                    @include ('partials.forms.sell-selector', ['user_select' => 'true','contract_select' => 'true', 'location_select' => 'true'])--}}

                    @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.user'), 'fieldname' => 'user_responsible_id'  , 'hide_new' => true])

{{--                    @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'assigned_location', 'style' => 'display:none;', 'required'=>'true'])--}}

                    @include ('partials.forms.edit.contract-select', ['translated_name' => "Договор", 'fieldname' => 'contract_id'])




                    <!-- Checkout/Checkin Date -->
                        <div class="form-group {{ $errors->has('sold_at') ? 'error' : '' }}">
                            {{ Form::label('sold_at',  "Дата продажи", array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <div class="input-group date col-md-7" data-provide="datepicker"
                                     data-date-format="yyyy-mm-dd" data-date-end-date="0d">
                                    <input type="text" class="form-control"
                                           placeholder="{{ trans('general.select_date') }}" name="sold_at"
                                           id="sold_at" value="{{ Input::old('sold_at') }}">
                                    <span class="input-group-addon"><i class="fa fa-calendar"
                                                                       aria-hidden="true"></i></span>
                                </div>
                                {!! $errors->first('sold_at', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>


                        <!-- Purchase Cost -->
                        <div class="form-group {{ $errors->has('purchase_cost') ? ' has-error' : '' }}">
                            <label for="purchase_cost" class="col-md-3 control-label">Закупочная стоимость</label>
                            <div class="col-md-9">
                                <div class="input-group col-md-4" style="padding-left: 0px;">
                                    <input class="form-control float" type="text"
                                           name="purchase_cost" aria-label="Purchase_cost"
                                           id="purchase_cost"
                                           disabled
                                           value="{{ Input::old('purchase_cost', \App\Helpers\Helper::formatCurrencyOutput($item->purchase_cost)) }}"/>
                                    <span class="input-group-addon">
                @if (isset($currency_type))
                                            {{ $currency_type }}
                                        @else
                                            {{ $snipeSettings->default_currency }}
                                        @endif
            </span>
                                </div>

                                <div class="col-md-9" style="padding-left: 0px;">
                                    {!! $errors->first('depreciable_cost', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                                </div>
                            </div>
                        </div>

                        <!-- Set as Default -->
                        <div class="form-group{{ $errors->has('default_label') ? ' has-error' : '' }}">

                            <label class="col-md-offset-3" style="padding-left: 15px;">
                                <input type="checkbox" value="1" name="closing_documents" id="closing_documents" class="minimal" {{ Input::old('default_label', $item->closing_documents) == '1' ? ' checked="checked"' : '' }}> Есть закрывающие документы
                            </label>
{{--                            <p class="col-md-offset-3 help-block"> {{ trans('admin/statuslabels/table.default_label_help') }}</p>--}}
                        </div>

                        <!-- Note -->
                        <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
                            {{ Form::label('note', trans('admin/hardware/form.notes'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <textarea class="col-md-6 form-control" id="note"
                                          name="note">{{ Input::old('note', $item->note) }}</textarea>
                                {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                    </div> <!--/.box-body-->
                    <div class="box-footer">
                        <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
                        <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-check icon-white"
                                                                                    aria-hidden="true"></i> Продать
                        </button>
                    </div>
                </form>
            </div>
        </div> <!--/.col-md-7-->

        <!-- right column -->
        <div class="col-md-5" id="current_assets_box" style="display:none;">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h2 class="box-title">{{ trans('admin/users/general.current_assets') }}</h2>
                </div>
                <div class="box-body">
                    <div id="current_assets_content">
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop


@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        // $('input[name=checkout_to_type_s]').on("change",function () {
        //     var assignto_type = $('input[name=checkout_to_type_sx]:checked').val();
        //     var userid = $('#assigned_user option:selected').val();
        //
        //     if (assignto_type == 'asset') {
        //         $('#current_assets_box').fadeOut();
        //         $('#assigned_asset').show();
        //         $('#assigned_user').hide();
        //         $('#assigned_location').hide();
        //         $('#assigned_contract').hide();
        //         $('.notification-callout').fadeOut();
        //
        //     } else if (assignto_type == 'location') {
        //         $('#current_assets_box').fadeOut();
        //         $('#assigned_asset').hide();
        //         $('#assigned_user').hide();
        //         $('#assigned_location').show();
        //         $('#assigned_contract').hide();
        //         $('.notification-callout').fadeOut();
        //     } else if (assignto_type == 'contract') {
        //         $('#current_assets_box').fadeOut();
        //         $('#assigned_asset').hide();
        //         $('#assigned_user').hide();
        //         $('#assigned_location').hide();
        //         $('#assigned_contract').show();
        //         $('.notification-callout').fadeOut();
        //     } else  {
        //
        //         $('#assigned_asset').hide();
        //         $('#assigned_user').show();
        //         $('#assigned_location').hide();
        //         $('#assigned_contract').hide();
        //         if (userid) {
        //             $('#current_assets_box').fadeIn();
        //         }
        //         $('.notification-callout').fadeIn();
        //
        //     }
        // });
    </script>
@stop