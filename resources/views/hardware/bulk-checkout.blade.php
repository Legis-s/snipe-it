@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Массовая выдача активов
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
                    <h2 class="box-title"> Массовая выдача активов </h2>
                </div>
                <div class="box-body">
                    <form class="form-horizontal" method="post" action="" autocomplete="off">
                    {{ csrf_field() }}

                    <!-- Checkout selector -->
                    @include ('partials.forms.checkout-selector', ['user_select' => 'true','asset_select' => 'true', 'location_select' => 'true'])

                    @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.user'), 'fieldname' => 'assigned_user', 'required'=>'true'])
                    @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.asset'), 'fieldname' => 'assigned_asset', 'unselect' => 'true', 'style' => 'display:none;', 'required'=>'true'])
                    @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'assigned_location', 'style' => 'display:none;', 'required'=>'true'])

                    <!-- Checkout/Checkin Date -->
                        <div class="form-group {{ $errors->has('checkout_at') ? 'error' : '' }}">
                            {{ Form::label('checkout_at', trans('admin/hardware/form.checkout_date'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <div class="input-group date col-md-5" data-provide="datepicker"
                                     data-date-format="yyyy-mm-dd" data-date-end-date="0d">
                                    <input type="text" class="form-control"
                                           placeholder="{{ trans('general.select_date') }}" name="checkout_at"
                                           id="checkout_at" value="{{ Input::old('checkout_at') }}">
                                    <span class="input-group-addon"><i class="fa fa-calendar"
                                                                       aria-hidden="true"></i></span>
                                </div>
                                {!! $errors->first('checkout_at', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        <!-- Expected Checkin Date -->
                        <div class="form-group {{ $errors->has('expected_checkin') ? 'error' : '' }}">
                            {{ Form::label('expected_checkin', trans('admin/hardware/form.expected_checkin'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <div class="input-group date col-md-5" data-provide="datepicker"
                                     data-date-format="yyyy-mm-dd" data-date-start-date="0d">
                                    <input type="text" class="form-control"
                                           placeholder="{{ trans('general.select_date') }}" name="expected_checkin"
                                           id="expected_checkin" value="{{ Input::old('expected_checkin') }}">
                                    <span class="input-group-addon"><i class="fa fa-calendar"
                                                                       aria-hidden="true"></i></span>
                                </div>
                                {!! $errors->first('expected_checkin', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>


                        <!-- Note -->
                        <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
                            {{ Form::label('note', trans('admin/hardware/form.notes'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <textarea class="col-md-6 form-control" id="note"
                                          name="note">{{ Input::old('note') }}</textarea>
                                {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fa fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        @include ('partials.forms.edit.asset-select', [
                          'translated_name' => trans('general.assets'),
                          'fieldname' => 'selected_assets[]',
                          'multiple' => true,
                          'asset_status_type' => 'RTD',
                          'select_id' => 'assigned_assets_select',
                        ])


                        <table
                                data-advanced-search="true"
                                data-click-to-select="true"
                                data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayoutBulk() }}"
                                data-cookie-id-table="assetsBulkTable"
                                data-pagination="true"
                                data-id-table="assetsBulkTable"
                                data-search="true"
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
                                data-url="{{ route('api.assets.index',
                    array('bulk' => true ))}}">
                        </table>


                </div> <!--./box-body-->
                <div class="box-footer">
                    <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
                    <button type="submit" class="btn btn-primary pull-right"><i class="fa fa-check icon-white"
                                                                                aria-hidden="true"></i> {{ trans('general.checkout') }}
                    </button>
                </div>
            </div>
            </form>
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
    @include('partials.bootstrap-table')
    @include('partials/assets-assigned')
    <script src="{{ asset('js/onscan.js') }}"></script>
    <script nonce="{{ csrf_token() }}">
        var select = $('#assigned_assets_select');
        var selected = [];
        // Enable scan events for the entire document
        onScan.attachTo(document, {
            suffixKeyCodes: [13], // enter-key expected at the end of a scan
            // reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
            onScan: function (sCode, iQty) { // Alternative to document.addEventListener('scan')
                scanCode(auto_layout_keyboard(sCode));
            },
            // onKeyDetect: function(iKeyCode){ // output all potentially relevant key events - great for debugging!
            //     console.log('Pressed: ' + iKeyCode);
            // }
        });
        function select2_search ($el, term) {
            $el.select2('open');

            // Get the search box within the dropdown or the selection
            // Dropdown = single, Selection = multiple
            var $search = $el.data('select2').dropdown.$search || $el.data('select2').selection.$search;
            // This is undocumented and may change in the future

            $search.val(term);
            $search.trigger('input');
        }

        function scanCode(code){
            console.log("scan: "+code);

            // var $select = $($(this).data('target'));
            // select2_search(select, code);


            select.select2("trigger", "select", {
                data: { asset_tag: code }
            });

            // select.val(code).trigger("change");
            // select.trigger({
            //     type: 'select2:select',
            //     params: {
            //         data: {
            //             asset_tag:code
            //         }
            //     }
            // });
        }




        select.on('select2:select', function (e) {
            var data = e.params.data;
            selected.push(data.id)
            updData();
        });
        select.on('select2:unselect', function (e) {
            var data = e.params.data;
            console.log("select2:unselect");
            console.log(data);
            selected = selected.filter(item => item !== parseInt(data.id))
            updData();
        });
        select.on('select2:clear', function (e) {
            // var data = e.params.data;
            // console.log("select2:clear");
            // console.log(data);
            selected = [];
            updData();
        });
        function auto_layout_keyboard( str ) {
            var replacer = {
                "й":"q", "ц":"w", "у":"e", "к":"r", "е":"t", "н":"y", "г":"u",
                "ш":"i", "щ":"o", "з":"p", "х":"[", "ъ":"]", "ф":"a", "ы":"s",
                "в":"d", "а":"f", "п":"g", "р":"h", "о":"j", "л":"k", "д":"l",
                "ж":";", "э":"'", "я":"z", "ч":"x", "с":"c", "м":"v", "и":"b",
                "т":"n", "ь":"m", "б":",", "ю":".", ".":"/"
            };

            return str.replace(/[А-я/,.;\'\]\[]/g, function ( x ){
                return x == x.toLowerCase() ? replacer[ x ] : replacer[ x.toLowerCase() ].toUpperCase();
            });
        }


        function updData() {
            console.log(selected);
            $("#assetsBulkTable").bootstrapTable("refresh", {
                "query": {
                    "data": JSON.stringify(selected),
                },
                "silent": true,
            })
        }
    </script>

@stop
