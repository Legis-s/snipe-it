@extends('layouts/default')

{{-- Вернуть актив на склад --}}
{{-- Page title --}}
@section('title')
    {{ trans('admin/hardware/general.checkin') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <style>

        .input-group {
            padding-left: 0px !important;
        }
        :root {
            --gl-star-empty: url(/img/star-empty.svg);
            --gl-star-full: url(/img/star-full.svg);
            --gl-star-size: 32px;
        }
    </style>


    <div class="row"><!-- .row -->
        <!-- left column -->
        <div class="col-md-7 col-sm-11 col-xs-12 col-md-offset-2">
            <div class="box box-default"><!-- .box-default -->
                <div class="box-header with-border"><!-- .box-header -->
                    <h2 class="box-title">
                        {{ trans('admin/hardware/form.tag') }}
                        {{ $asset->asset_tag }}
                    </h2>
                </div><!-- /.box-header -->

                <div class="box-body"><!-- .box-body -->
                    <div class="col-md-12"><!-- .col-md-12 -->

                        @if ($backto == 'user')
                            <form class="form-horizontal" method="post"
                                  action="{{ route('hardware.checkin.store', array('assetId'=> $asset->id, 'backto'=>'user')) }}"
                                  autocomplete="off">
                                @else
                                    <form class="form-horizontal" method="post"
                                          action="{{ route('hardware.checkin.store', array('assetId'=> $asset->id)) }}"
                                          autocomplete="off">
                                        @endif
                                        {{csrf_field()}}

                                        <!-- AssetModel name -->
                                        <div class="form-group">
                                            <label for="model" class="col-sm-3 control-label">
                                                {{ trans('admin/hardware/form.model') }}
                                            </label>
                                            <div class="col-md-8">

                                                <p class="form-control-static">
                                                    @if (($asset->model) && ($asset->model->name))
                                                        {{ $asset->model->name }}
                                                    @else
                                                        <span class="text-danger text-bold">
                                                          <x-icon type="warning" />
                                                          {{ trans('admin/hardware/general.model_invalid')}}
                                                        </span>
                                                        {{ trans('admin/hardware/general.model_invalid_fix')}}
                                                        <a href="{{ route('hardware.edit', $asset->id) }}">
                                                            <strong>{{ trans('admin/hardware/general.edit') }}</strong>
                                                        </a>
                                                    @endif
                                                </p>

                                            </div>
                                        </div>

                                        <!-- Asset Name -->
                                        <div class="form-group {{ $errors->has('name') ? 'error' : '' }}">
                                            <label for="name" class="col-sm-3 control-label">
                                                {{ trans('general.name') }}
                                            </label>
                                            <div class="col-md-8">
                                                <input class="form-control" type="text" name="name" aria-label="name"
                                                       id="name" value="{{ old('name', $asset->name) }}"/>
                                                {!! $errors->first('name', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                            </div>
                                        </div>

                                        <!-- Status -->
                                        <div class="form-group {{ $errors->has('status_id') ? 'error' : '' }}">
                                            <label for="status_id" class="col-sm-3 control-label">
                                                {{ trans('admin/hardware/form.status') }}
                                            </label>
                                            <div class="col-md-8 required">
                                                <x-input.select
                                                    name="status_id"
                                                    id="modal-statuslabel_types"
                                                    :options="$statusLabel_list"
                                                    style="width: 100%"
                                                    aria-label="status_id"
                                                />
                                                {!! $errors->first('status_id', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                            </div>
                                        </div>

                                        <x-input.location-select
                                            :label="trans('general.location')"
                                            name="location_id"
                                            :help_text="($asset->defaultLoc) ? trans('general.checkin_to_diff_location', ['default_location' => $asset->defaultLoc->name]) : null"
                                            :selected="old('location_id')"
                                        />

                                        <!-- Update actual location  -->
                                        <div class="form-group">
                                            <div class="col-md-9 col-md-offset-3">
                                                <label class="form-control">
                                                    {{ Form::radio('update_default_location', '1', old('update_default_location'), ['checked'=> 'checked', 'aria-label'=>'update_default_location']) }}
                                                    {{ trans('admin/hardware/form.asset_location') }}
                                                </label>
                                                <label class="form-control">
                                                    {{ Form::radio('update_default_location', '0', old('update_default_location'), ['aria-label'=>'update_default_location']) }}
                                                    {{ trans('admin/hardware/form.asset_location_update_default_current') }}
                                                </label>
                                            </div>
                                        </div> <!--/form-group-->

                                        <!-- Checkout/Checkin Date -->
                                        <div class="form-group{{ $errors->has('checkin_at') ? ' has-error' : '' }}">
                                            <label for="checkin_at" class="col-sm-3 col-xs-12 col-sm-12 control-label">
                                                {{ trans('admin/hardware/form.checkin_date') }}
                                            </label>

                                            <div class="col-md-8 col-xs-12 col-sm-12">
                                                <div class="input-group col-xl-5 col-lg-5 col-md-7 col-sm-9 col-xs-12 required">
                                                    <div class="input-group date" data-provide="datepicker"
                                                         data-date-format="yyyy-mm-dd" data-autoclose="true">
                                                        <input type="text" class="form-control"
                                                               placeholder="{{ trans('general.select_date') }}"
                                                               name="checkin_at" id="checkin_at"
                                                               value="{{ old('checkin_at', date('Y-m-d')) }}">
                                                        <span class="input-group-addon">
                                                            <x-icon type="calendar" />
                                                        </span>
                                                    </div>
                                                    {!! $errors->first('checkin_at', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Custom fields -->
                                        @include("models/custom_fields_form", [
                                                'model' => $asset->model,
                                                'show_display_checkin_fields' => 'true'
                                        ])







                        <!-- Note -->
                        <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
                            <label for="note" class="col-md-3 control-label">
                                {{ trans('general.notes') }}
                            </label>
                            <div class="col-md-8">
                                <textarea class="col-md-6 form-control" id="note" @required($snipeSettings->require_checkinout_notes)
                                        name="note">{{ old('note', $asset->note) }}</textarea>
                                {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                                          <!-- quality Cost -->
                                          <div class="form-group {{ $errors->has('quality') ? ' has-error' : '' }}">
                                              <label for="quality" class="col-md-3 control-label">Состояние</label>
                                              <div class="col-md-9">
                                                  <div class="input-group col-md-4" style="padding-left: 0px;">
                                                      <select class="star-rating" name="quality" id="quality">
                                                          @php
                                                              $quality = Request::old('quality', (isset($asset)) ? $asset->quality :null);
                                                          @endphp
                                                          <option @if (!isset($quality)) selected @endif value="">Оцените состояние</option>
                                                          <option @if ($quality == 5) selected @endif value="5">Новое запакованное</option>
                                                          <option @if ($quality == 4) selected @endif value="4">В отличном состоянии, но использовалось</option>
                                                          <option @if ($quality == 3) selected @endif value="3">Рабочее, но с небольшими следами повреждений,
                                                              небольшим загрязнением
                                                          </option>
                                                          <option @if ($quality == 2) selected @endif value="2">Частично рабочее или сильно загрязненное</option>
                                                          <option @if ($quality == 1) selected @endif value="1">Полностью не рабочее</option>
                                                      </select>
                                                      {{--            <input class="form-control" type="text" name="quality" aria-label="quality" id="quality" value="{{ Input::old('depreciable_cost', \App\Helpers\Helper::formatCurrencyOutput($item->depreciable_cost)) }}" />--}}
                                                  </div>
                                                  <div class="col-md-9" style="padding-left: 0px;">
                                                      {!! $errors->first('quality', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                                                  </div>
                                              </div>
                                          </div>
                  <!-- life Cost -->
                  <div class="form-group">
                      <label for="life" class="col-md-3 control-label">Срок эксплуатации
                          (прошло/рассчетный)</label>
                      <div class="col-md-9">
                          @php
                              if ($asset->purchase_date){
                                   $now = new DateTime();
                                   $d2 = new DateTime($asset->purchase_date);
                                   $interval = $d2->diff($now);
                                   $result =  $interval->m + 12*$interval->y;
//                                                         if ($asset->quality == 5){
//                                                             $result = 0;
//                                                         }

                               }else{
                                   $result = "Нет даты закупки";
                               }
                               if($asset->model && $asset->model->depreciation &&  $asset->model->depreciation->months){
                                    $months= $asset->model->depreciation->months;
                               }else{
                                   $months = 36;
                               }
                          @endphp
                          <div class="input-group col-md-4" style="padding-left: 0px;">
                              <input class="form-control float" type="text" disabled
                                     value="{{$result}}/{{ $months }}"/>
                              <span class="input-group-addon">Месяцев</span>
                          </div>
                      </div>
                  </div>
                  <!-- Purchase Cost -->
                  <div class="form-group {{ $errors->has('purchase_cost') ? ' has-error' : '' }}">
                      <label for="purchase_cost" class="col-md-3 control-label">Закупочная
                          стоимость</label>
                      <div class="col-md-9">
                          <div class="input-group col-md-4" style="padding-left: 0px;">
                              <input class="form-control float" type="text"
                                     name="purchase_cost" aria-label="Purchase_cost"
                                     id="purchase_cost"
                                     @if (isset($asset->purchase_cost))
                                         disabled
                                     @endif
                                     value="{{ Request::old('purchase_cost', \App\Helpers\Helper::formatCurrencyOutput($asset->purchase_cost)) }}"/>
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
                  @if (isset($asset->depreciable_cost))
                      <!-- depreciable Cost -->
                      <div class="form-group {{ $errors->has('depreciable_cost') ? ' has-error' : '' }}">
                          <label for="purchase_cost" class="col-md-3 control-label">Старая
                              остаточная стоимость</label>
                          <div class="col-md-9">
                              <div class="input-group col-md-4" style="padding-left: 0px;">
                                  <input class="form-control float" type="text"
                                         name="depreciable_cost" aria-label="depreciable_cost"
                                         id="depreciable_cost"
                                         disabled
                                         value="{{ Request::old('depreciable_cost', \App\Helpers\Helper::formatCurrencyOutput($asset->depreciable_cost)) }}"/>
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
                  @endif
                  <!-- new depreciable Cost -->
                  <div class="form-group {{ $errors->has('depreciable_cost') ? ' has-error' : '' }}">
                      <label for="purchase_cost" class="col-md-3 control-label">Новая
                          остаточная
                          стоимость</label>
                      <div class="col-md-9">
                          <div class="input-group col-md-4" style="padding-left: 0px;">
                              <input class="form-control float" type="text"
                                     name="new_depreciable_cost" aria-label="depreciable_cost"
                                     id="new_depreciable_cost"
                                     value="{{ Request::old('new_depreciable_cost', \App\Helpers\Helper::formatCurrencyOutput($asset->new_depreciable_cost)) }}"/>
                              <span class="input-group-addon">
                            @if (isset($currency_type))
                                      {{ $currency_type }}
                                  @else
                                      {{ $snipeSettings->default_currency }}
                                  @endif
                            </span>
                          </div>

                          <div class="col-md-9" style="padding-left: 0px;">
                              {{--                                    {!! $errors->first('new_depreciable_cost', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}--}}
                          </div>
                      </div>
                  </div>



                    </div> <!--/.box-body-->
                </div> <!--/.box-body-->

                <x-redirect_submit_options
                        index_route="hardware.index"
                        :button_label="trans('general.checkin')"
                        :disabled_select="!$asset->model"
                        :options="[
                                'index' => trans('admin/hardware/form.redirect_to_all', ['type' => trans('general.assets')]),
                                'item' => trans('admin/hardware/form.redirect_to_type', ['type' => trans('general.asset')]),
                                'target' => $target_option,
                               ]"
                />
                </form>

            </div>
        </div>
    </div>

@stop



@section('moar_scripts')
    <script nonce="{{ csrf_token() }}">
        $(function () {
            var starRatingControl = new StarRating('.star-rating', {
                maxStars: 5,
                tooltip: 'Оцените состояние',
                clearable: false,
            });
            calculeteCoast();

            function calculeteCoast() {

                $buyVal = parseFloat($("#purchase_cost").val().replace(",",""));
                $quality = parseInt($("#quality").val());

                if ($buyVal > 0 && $quality > 0) {
                    //quality count
                    $quality_divider = 1;
                    switch ($quality) {
                        case 4:
                            $quality_divider = 0.8
                            break;
                        case 3:
                            $quality_divider = 0.50
                            break;
                        case 2:
                            $quality_divider = 0.3
                            break;
                        case 1:
                            $quality_divider = 0
                            break;
                    }


                    @if (isset($asset->model) && isset($asset->model->depreciation) && isset($asset->model->depreciation->months))
                        $lifetime = {{$asset->model->depreciation->months}};
                    @else
                        $lifetime = 36;
                    @endif

                            @if (isset($asset->purchase_date))
                        $buydate = "{{$asset->purchase_date}}";
                    $buydate = new Date($buydate.substr(0, 10));
                    $usetime = monthDiff($buydate);
                    if ($usetime <= 12) {
                        $time_divider = 0;
                    } else {
                        $time_divider = ($lifetime - $usetime) / $lifetime;
                        if ($time_divider < 0) {
                            $time_divider = 0;
                        }
                    }
                    @else
                        $time_divider = 1 / 3;
                    @endif

                    // console.log(`HelcalculeteCoastlo $usetime ${$usetime}  $lifetime${$lifetime}`)
                    $newVal = (($buyVal - ($buyVal/$lifetime) * $usetime) * $quality_divider).toFixed(2);
                    if ($newVal<0){
                        $newVal = 0;
                    }
                    $("#new_depreciable_cost").val($newVal);
                }
            }

            $("#quality").change(function () {
                calculeteCoast();
            });

            $("#purchase_cost").change(function () {
                calculeteCoast();
            });

            function monthDiff(dateFrom) {
                var dateTo = new Date();
                return dateTo.getMonth() - dateFrom.getMonth() +
                    (12 * (dateTo.getFullYear() - dateFrom.getFullYear()))
            }
        });
    </script>
@stop