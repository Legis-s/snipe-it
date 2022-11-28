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

                        @include ('partials.forms.custom.asset-select-bulk-form', [
                            'translated_name' => trans('general.asset'),
                            'fieldname' => 'asset_select',
                            'unselect' => 'true',
                            'asset_status_type' => 'RTD',
                            'select_id' => 'asset_select',
                        ])

{{--                        @include ('partials.forms.edit.asset-select', [--}}
{{--                          'translated_name' => trans('general.assets'),--}}
{{--                          'fieldname' => 'selected_assets[]',--}}
{{--                          'multiple' => true,--}}
{{--                          'asset_status_type' => 'Deployed',--}}
{{--                          'select_id' => 'assigned_assets_select',--}}
{{--                        ])--}}

                        <select id="assigned_assets_select" name="selected_assets[]" multiple hidden>
                            @foreach ($ids as $a_id)
                                <option value = '{{ $a_id }}' selected="selected">{{ $a_id }}</option>
                            @endforeach
                        </select>

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
        <div class="col-md-9">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">Активы</h2>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table table-responsive">
                                {{--                                <p class="activ text-center text-bold text-danger hidden">Добавьте хотя бы один--}}
                                {{--                                    расходник</p>--}}
                                <table
                                        data-advanced-search="true"
                                        data-click-to-select="true"
                                        data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayoutBulk() }}"
                                        data-cookie-id-table="assetsBulkTable"
                                        data-pagination="true"
                                        data-id-table="assetsBulkTable"
                                        data-search="false"
                                        {{--                                        {% if ids is defined %}--}}
                                        {{--                                        data-query-params="queryParams"--}}
                                        {{--                                        {% endif %}--}}
                                        data-side-pagination="server"
                                        data-show-columns="true"
                                        data-show-footer="true"
                                        data-show-refresh="true"
                                        data-sort-order="asc"
                                        data-sort-name="name"
                                        data-toolbar="#toolbar"
                                        data-queryParams="#toolbar"
                                        id="assetsBulkTable"
                                        class="table table-striped snipe-table"
                                        data-url="{{ route('api.assets.index',array('bulk' => true ))}}">
                                </table>
                            </div><!-- /.table-responsive -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('moar_scripts')
    <script src="{{ asset('js/onscan.js') }}"></script>
    <script nonce="{{ csrf_token() }}">
        var selected = [];
        window.operateEventsBulk = {
            'click .bulk-clear':function (e, value, row, index) {
                var selected_id =String(row.id);
                console.log(selected_id);
                console.log(selected);
                if (selected.includes(selected_id)) {
                    selected = selected.filter(item => item !== selected_id)
                    // selected.remove(selected_id);
                    updData();
                }
            }
        };
        // function queryParams(params) {
        //     params.data = JSON.stringify(selected);
        //     return params
        // }
        function updData() {
            console.log("upd");
            console.log(selected);
            $("#assetsBulkTable").bootstrapTable("refresh", {
                "query": {
                    "data": JSON.stringify(selected),
                },
                "silent": true,
            });
            $('#assigned_assets_select').empty();
            $.each(selected, function (i, item) {

                $('#assigned_assets_select').append($('<option>', {
                    value: item,
                    text: item,
                    selected: true
                }));
            });

        }

    </script>
    @include('partials.bootstrap-table')
    <script nonce="{{ csrf_token() }}">
        $(function () {


            var select = $('#asset_select');
            var add_asset = $('#add_asset');

            // Array.from(document.getElementById("assigned_assets_select")).forEach(function(item) {
            //     selected.push(item.innerHTML);
            // });

            // updData();
            add_asset.prop('disabled', true);
            add_asset.click(function () {
                var selected_id = select.val();
                if (!selected.includes(selected_id)) {
                    selected.push(selected_id);
                }
                updData();
                select.val(null).trigger('change');
                add_asset.prop('disabled', true);
            });
            select.on('select2:select', function (e) {
                add_asset.prop('disabled', false);
            });
            select.on('select2:unselect', function (e) {
                add_asset.prop('disabled', true);
            });
        });
    </script>
@stop