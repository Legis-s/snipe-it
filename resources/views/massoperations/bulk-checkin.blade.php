@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Массовый возврат активов
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
        <div class="col-md-9">
            <div class="box box-default">
                <div class="box-header with-border">
{{--                    <h2 class="box-title"> Массовый возврат активов </h2>--}}
                </div>
                <form class="form-horizontal" method="post" action="" autocomplete="off">
                    <div class="box-body">
                        {{ csrf_field() }}

                        @if(Auth::user()->favoriteLocation)
                            @include ('partials.forms.edit.location-select-checkin', ['translated_name' => trans('general.location'), 'fieldname' => 'location_id','required'=>true])
                        @else
                            @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'location_id','required'=>true ])
                        @endif

                        <!-- Checkout/Checkin Date -->
                        <div class="form-group{{ $errors->has('checkin_at') ? ' has-error' : '' }}">
                            {{ Form::label('checkin_at', trans('admin/hardware/form.checkin_date'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <div class="input-group col-md-5">
                                    <div class="input-group date" data-provide="datepicker"
                                         data-date-format="yyyy-mm-dd" data-autoclose="true">
                                        <input type="text" class="form-control"
                                               placeholder="{{ trans('general.select_date') }}" name="checkin_at"
                                               id="checkin_at" value="{{ old('checkin_at', date('Y-m-d')) }}">
                                        <span class="input-group-addon"><i class="fas fa-calendar"
                                                                           aria-hidden="true"></i></span>
                                    </div>
                                    {!! $errors->first('checkin_at', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                                </div>
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">

                            {{ Form::label('note', trans('admin/hardware/form.notes'), array('class' => 'col-md-3 control-label')) }}

                            <div class="col-md-8">
                                <textarea class="col-md-6 form-control" id="note"
                                          name="note"></textarea>
                                {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        @include ('partials.forms.custom.bitrix_id')

                        @include ('partials.forms.edit.asset-select', [
                          'translated_name' => trans('general.assets'),
                          'fieldname' => 'selected_assets[]',
                          'multiple' => true,
                          'asset_status_type' => 'Deployed',
                          'select_id' => 'assigned_assets_select',
                        ])

                    </div>
                    <div class="box-footer">
                        <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
                        <button type="submit" class="btn btn-primary pull-right"><i class="fas fa-check icon-white"
                                                                                    aria-hidden="true"></i> {{ trans('general.checkin') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('moar_scripts')
    <script src="{{ asset('js/onscan.js') }}"></script>
@stop