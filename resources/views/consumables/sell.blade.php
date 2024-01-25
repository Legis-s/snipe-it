@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Продать расходники
    @parent
@stop

{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-9">
            <form class="form-horizontal" method="post" action="" autocomplete="off">
                <!-- CSRF Token -->
                <input type="hidden" name="_token" value="{{ csrf_token() }}"/>

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

                        @include ('partials.forms.sell-selector', ['user_select' => 'true','contract_select'=> 'true'])

                        @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.user'), 'fieldname' => 'assigned_user','required'=>'true'])
                        @include ('partials.forms.edit.contract-select', ['translated_name' => "Договор", 'fieldname' => 'assigned_contract', 'style' => 'display:none;','required'=>'true'])

                        @include ('partials.forms.custom.contract-id-select', ['translated_name' => "Договор", 'fieldname' => 'contract_id','required'=>'true'])

                        <!-- Purchase Cost -->
                        <div class="form-group {{ $errors->has('purchase_cost') ? ' has-error' : '' }}">
                            <label for="purchase_cost" class="col-md-3 control-label">Закупочная стоимость</label>
                            <div class="col-md-9">
                                <div class="input-group col-md-4" style="padding-left: 0px;">
                                    <input class="form-control float" type="text"
                                           name="purchase_cost" aria-label="Purchase_cost"
                                           id="purchase_cost"
                                           disabled
                                           value="{{ old('purchase_cost', \App\Helpers\Helper::formatCurrencyOutput($consumable->purchase_cost)) }}"/>
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
                        @include ('partials.forms.edit.quantity_max')
                        @if ($consumable->requireAcceptance() || $consumable->getEula() || ($snipeSettings->slack_endpoint!=''))
                            <div class="form-group notification-callout">
                                <div class="col-md-8 col-md-offset-3">
                                    <div class="callout callout-info">

                                        @if ($consumable->category->require_acceptance=='1')
                                            <i class="fa fa-envelope"></i>
                                            {{ trans('admin/categories/general.required_acceptance') }}
                                            <br>
                                        @endif

                                        @if ($consumable->getEula())
                                            <i class="fa fa-envelope"></i>
                                            {{ trans('admin/categories/general.required_eula') }}
                                            <br>
                                        @endif

                                        @if ($snipeSettings->slack_endpoint!='')
                                            <i class="fa fa-slack"></i>
                                            A slack message will be sent
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Note -->
                        <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
                            <label for="note"
                                   class="col-md-3 control-label">{{ trans('admin/hardware/form.notes') }}</label>
                            <div class="col-md-7">
                                <textarea class="col-md-6 form-control" name="note">{{ old('note') }}</textarea>
                                {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                    </div> <!-- .box-body -->

                    <div class="box-footer">
                        <a class="btn btn-link"
                           href="{{ route('consumables.show', ['consumable'=> $consumable->id]) }}">{{ trans('button.cancel') }}</a>
                        <button type="submit" class="btn btn-primary pull-right"><i class="fas fa-check icon-white"
                                                                                    aria-hidden="true"></i> {{ trans('general.sell') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        $(function () {
            $('input[name=checkout_to_type_s]').on("change", function () {
                var assignto_type = $('input[name=checkout_to_type_s]:checked').val();
                var userid = $('#assigned_user option:selected').val();

                if (assignto_type == 'asset') {
                    $('#current_assets_box').fadeOut();
                    $('#assigned_asset').show();
                    $('#assigned_user').hide();
                    $('#assigned_location').hide();
                    $('#assigned_contract').hide();
                    $('.notification-callout').fadeOut();

                } else if (assignto_type == 'location') {
                    $('#current_assets_box').fadeOut();
                    $('#assigned_asset').hide();
                    $('#assigned_user').hide();
                    $('#assigned_location').show();
                    $('#assigned_contract').hide();
                    $('.notification-callout').fadeOut();
                } else if (assignto_type == 'contract') {
                    $('#current_assets_box').fadeOut();
                    $('#assigned_asset').hide();
                    $('#assigned_user').hide();
                    $('#assigned_location').hide();
                    $('#assigned_contract').show();
                    $('#contract_id').hide();
                    $('.notification-callout').fadeOut();
                } else {

                    $('#assigned_asset').hide();
                    $('#assigned_user').show();
                    $('#assigned_location').hide();
                    $('#assigned_contract').hide();
                    $('#contract_id').show();
                    if (userid) {
                        $('#current_assets_box').fadeIn();
                    }
                    $('.notification-callout').fadeIn();

                }
            });
        });
    </script>
@stop
