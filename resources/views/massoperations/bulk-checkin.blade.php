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
        <div class="col-md-6">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title"> Массовый возврат активов </h2>
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
                        <div class="form-group {{ $errors->has('checkout_at') ? 'error' : '' }}">
                            {{ Form::label('checkout_at', trans('admin/hardware/form.checkout_date'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <div class="input-group date col-md-8" data-provide="datepicker" data-date-format="yyyy-mm-dd" data-date-end-date="0d" data-date-clear-btn="true">
                                    <input type="text" class="form-control" placeholder="{{ trans('general.select_date') }}" name="checkout_at" id="checkout_at" value="{{ old('checkout_at') }}">
                                    <span class="input-group-addon"><i class="fas fa-calendar" aria-hidden="true"></i></span>
                                </div>
                                {!! $errors->first('checkout_at', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>

                        <!-- Note -->
                        <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
                            {{ Form::label('note', trans('admin/hardware/form.notes'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <textarea class="col-md-6 form-control" id="note" name="note">{{ old('note') }}</textarea>
                                {!! $errors->first('note', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>
                        @include ('partials.forms.custom.bitrix_id')


                        @include ('partials.forms.edit.asset-select', [
                          'translated_name' => trans('general.assets'),
                          'fieldname' => 'selected_assets[]',
                          'multiple' => true,
                          'asset_status_type' => 'RETURN',
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
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h2 class="box-title">Активы</h2>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table table-responsive">
                                <table
                                        data-advanced-search="true"
                                        data-click-to-select="true"
                                        data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayoutBulk() }}"
                                        data-cookie-id-table="assetsBulkTable"
                                        data-pagination="false"
                                        data-id-table="assetsBulkTable"
                                        data-search="false"
                                        @isset($ids)
                                            data-query-params="queryParams"
                                        @endif
                                        data-height="385"
                                        data-side-pagination="server"
                                        data-show-columns="true"
                                        data-show-footer="false"
                                        data-show-refresh="false"
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
    @include('partials.bootstrap-table')

    <script nonce="{{ csrf_token() }}">
        // Initialize with options
        onScan.attachTo(document, {
            suffixKeyCodes: [13], // enter-key expected at the end of a scan
            reactToPaste: true, // Compatibility to built-in scanners in paste-mode (as opposed to keyboard-mode)
            onScan: function(sCode, iQty) { // Alternative to document.addEventListener('scan')
                console.log('Scanned: ' + sCode);
                $.ajax({
                    type: 'GET',
                    url:  "/api/v1/hardware/bytag/"+sCode,
                    headers: {
                        "X-Requested-With": 'XMLHttpRequest',
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    success: function (data) {
                        console.log(data);
                        if (data != null && "id" in data) {
                            if( data["user_can_checkout"] == true){
                                var add = true
                                var selected_ass = $('#assigned_assets_select').select2('data');
                                selected_ass.forEach((element) => {
                                    if (element.id == data.id){
                                        add = false
                                    }
                                });
                                if (add){
                                    var model_name = "";
                                    if ("model" in data){
                                        model_name = data["model"]["name"]
                                    }
                                    var option = new Option("("+data["asset_tag"]+") "+model_name, data.id, true, true);
                                    $('#assigned_assets_select').append(option).trigger('change');
                                    // manually trigger the `select2:select` event
                                    $('#assigned_assets_select').trigger({
                                        type: 'select2:select',
                                        params: {
                                            data: data
                                        }
                                    });
                                }

                            }else{
                                Swal.fire({
                                    icon: "error",
                                    title: "Актив "+ sCode + " недоступен к выдаче",
                                    timer: 1200
                                });
                            }
                            // window.location.href = "/hardware/"+data["id"];
                        }else{
                            Swal.fire({
                                icon: "error",
                                title: "Нет актива с меткой "+ sCode,
                                timer: 1200
                            });
                            console.log("No tag ");
                        }
                    },
                });
            },
        });

        var selected = [];
        var assets_select = $('#assigned_assets_select');
        var assets_table = $("#assetsBulkTable");

        function queryParams(params) {
            params.data = JSON.stringify(selected);
            return params
        }
        window.operateEventsBulk = {
            'click .bulk-clear':function (e, value, row, index) {
                var selected_id = String(row.id);

                if (selected.includes(selected_id)) {
                    selected = selected.filter(item => item !== selected_id)
                }
                assets_select.val(selected).trigger('change');
            }
        };

        $(function () {

            assets_select.on('change', function (e) {
                var selected_ass = assets_select.select2('data');
                selected = []
                selected_ass.forEach((element) => {
                    selected.push(element.id)
                });
                assets_table.bootstrapTable("refresh", {
                    "query": {
                        "data": JSON.stringify(selected),
                    },
                    "silent": true,
                });
            });
        });

    </script>
@stop