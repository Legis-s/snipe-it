@extends('layouts/default')

{{-- Page title --}}
@section('title')
    Массовая выдача
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
                    {{--                    <h2 class="box-title"> {{ trans('admin/hardware/form.tag') }} </h2>--}}
                </div>
                <div class="box-body">
                    <form class="form-horizontal" method="post" action="" autocomplete="off">
                        {{ csrf_field() }}

                        <!-- Checkout selector -->
                        @include ('partials.forms.checkout-selector', ['user_select' => 'true','asset_select' => 'true', 'location_select' => 'true'])

                        @include ('partials.forms.edit.location-select', ['translated_name' => trans('general.location'), 'fieldname' => 'assigned_location', 'required'=>'true','hide_new'=>true])
                        @include ('partials.forms.edit.user-select', ['translated_name' => trans('general.user'), 'fieldname' => 'assigned_user', 'unselect' => 'true', 'style' => 'display:none;', 'required'=>'true','hide_new'=>true])
                        @include ('partials.forms.edit.asset-select', ['translated_name' => trans('general.asset'), 'fieldname' => 'assigned_asset', 'unselect' => 'true', 'style' => 'display:none;', 'required'=>'true','hide_new'=>true])

                        <!-- Checkout/Checkin Date -->
                        <div class="form-group {{ $errors->has('checkout_at') ? 'error' : '' }}">
                            {{ Form::label('checkout_at', trans('admin/hardware/form.checkout_date'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-8">
                                <div class="input-group date col-md-5" data-provide="datepicker"
                                     data-date-format="yyyy-mm-dd" data-date-end-date="0d" data-date-clear-btn="true">
                                    <input type="text" class="form-control"
                                           placeholder="{{ trans('general.select_date') }}" name="checkout_at"
                                           id="checkout_at" value="{{ old('checkout_at') }}">
                                    <span class="input-group-addon"><i class="fas fa-calendar"
                                                                       aria-hidden="true"></i></span>
                                </div>
                                {!! $errors->first('checkout_at', '<span class="alert-msg" aria-hidden="true"><i class="fas fa-times" aria-hidden="true"></i> :message</span>') !!}
                            </div>
                        </div>
                        <!-- Note -->
                        <div class="form-group {{ $errors->has('note') ? 'error' : '' }}">
                            {{ Form::label('note', trans('admin/hardware/form.notes'), array('class' => 'col-md-3 control-label')) }}
                            <div class="col-md-7">
                                <textarea class="col-md-6 form-control" id="note"
                                          name="note">{{ old('note') }}</textarea>
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
{{--                          'asset_status_type' => 'RTD',--}}
{{--                          'select_id' => 'assigned_assets_select',--}}
{{--                          'required' => false,--}}
{{--                        ])--}}
                        <select id="assigned_assets_select" name="selected_assets[]" multiple hidden>
                            @foreach ($ids as $a_id)
                                <option value = '{{ $a_id }}' selected="selected">{{ $a_id }}</option>
                            @endforeach
                        </select>
                        <select id="assigned_consumables_select" name="selected_consumables[]" multiple hidden></select>

                </div> <!--./box-body-->
                <div class="box-footer">
                    <a class="btn btn-link" href="{{ URL::previous() }}"> {{ trans('button.cancel') }}</a>
                    <button type="submit" class="btn btn-primary pull-right"><i class="fas fa-check icon-white"
                                                                                aria-hidden="true"></i> {{ trans('general.checkout') }}
                    </button>
                </div>
                </form>
            </div>
        </div> <!--/.col-md-7-->
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
        <div class="col-md-9">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h2 class="box-title">Расходники</h2>
                    <button type="button" class="btn btn-primary btn-sm pull-right" data-toggle="modal"
                            data-target="#modal_consumables">
                        Добавить расходник
                    </button>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table table-responsive">
{{--                                <p class="activ text-center text-bold text-danger hidden">Добавьте хотя бы один--}}
{{--                                    расходник</p>--}}
                                <table id="table_consumables" class="table table-striped snipe-table">
                                    <thead>
                                    <th>#</th>
                                    <th>Модель</th>
                                    <th>Количество</th>
                                    <th>Удалить</th>
                                    </thead>
                                </table>
                            </div><!-- /.table-responsive -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

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


    <!-- Modal Расходник -->
    <div class="modal fade" id="modal_consumables" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title" id="myModalLabel">Добавить</h4>
                </div>
                <div class="modal-body2">
                    <div class="row">
                        <div class="col-md-12">
                            <form class="form-horizontal">
                                @include ('partials.forms.custom.consumables-select', ['translated_name' => 'Название', 'fieldname' => 'consumable_id', 'required' => 'true','hide_new' => 'true','asset_status_type' => 'notnull'])
                                <p class="duble text-center text-bold text-danger hidden">Такая модель уже есть</p>
                                @include ('partials.forms.purchases.quantity')
                            </form>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Закрыть</button>
                    <button type="button" class="btn btn-primary" id="addСonsumablesButton">Добавить</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('moar_scripts')
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
    @include('partials/assets-assigned')
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



            var baseUrl = $('meta[name="baseUrl"]').attr('content');
            var table_consumables = $('#table_consumables');
            table_consumables.bootstrapTable('destroy').bootstrapTable({
                locale: 'ru',
                data: [],
                search: true,
                toolbar: '#toolbar_consumables',
                columns: [{
                    field: 'id',
                    name: '#',
                    align: 'left',
                    valign: 'middle'
                }, {
                    field: 'consumable',
                    name: 'Модель',
                    align: 'left',
                    valign: 'middle'
                }, {
                    field: 'quantity',
                    name: 'Количество',
                    align: 'center',
                    valign: 'middle'
                }
                    , {
                        align: 'center',
                        valign: 'middle',
                        events: {
                            'click .remove': function (e, value, row, index) {
                                table_consumables.bootstrapTable('remove', {
                                    field: 'id',
                                    values: [row.id]
                                });
                                var data = table_consumables.bootstrapTable('getData');
                                var newData = [];
                                var count = 0;
                                data.forEach(function callback(currentValue, index, array) {
                                    count++;
                                    currentValue.id = count;
                                    newData.push(currentValue);
                                });
                                table_consumables.bootstrapTable('load', newData);
                            }
                        },
                        formatter: function (value, row, index) {
                            return [
                                '<a class="remove text-danger"  href="javascript:void(0)" title="Убрать">',
                                '<i class="remove fa fa-times fa-lg"></i>',
                                '</a>'
                            ].join('')
                        }
                    }
                ]
            });
            if ($('#consumables').val()) {
                table_consumables.bootstrapTable('load', JSON.parse($('#consumables').val()));
            }

            $('#modal_consumables').on("show.bs.modal", function (event) {
                var modal = $(this);

                modal.find("#consumable_select_id").val('');
                modal.find('#consumable_select_id').trigger('change');

                modal.find('#quantity').val(1);
                modal.find('.duble').addClass('hidden');

                modal.find('select').each(function (i, item) {
                    var link = $(item);
                    var endpoint = link.data("endpoint");
                    var select = link.data("select");
                    link.select2({
                        placeholder: '',
                        allowClear: true,
                        ajax: {

                            // the baseUrl includes a trailing slash
                            url: baseUrl + 'api/v1/' + endpoint + '/selectlist',
                            dataType: 'json',
                            delay: 250,
                            headers: {
                                "X-Requested-With": 'XMLHttpRequest',
                                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                            },
                            data: function (params) {
                                var data = {
                                    search: params.term,
                                    page: params.page || 1,
                                    assetStatusType: link.data("asset-status-type"),
                                };
                                return data;
                            },
                            cache: true
                        },
                        templateResult: formatDatalistSafe,
                    });
                });
            });
            $('#addСonsumablesButton').click(function (e) {
                e.preventDefault();
                var modal = $('#modal_consumables');
                var consumable_id = modal.find('select[name=consumable_id] option').filter(':selected').val();
                var consumable_name = modal.find('select[name=consumable_id] option').filter(':selected').text();
                var quantity = modal.find('#quantity').val();

                var tabele_data = table_consumables.bootstrapTable('getData');
                if (consumable_id > 0) {
                    var data = {
                        id: tabele_data.length + 1,
                        consumable_id: consumable_id,
                        consumable: consumable_name,
                        quantity: quantity,
                    };
                    $('#assigned_consumables_select').append($('<option>', {
                        value: consumable_id + ":" + quantity,
                        text: consumable_id + ":" + quantity,
                        selected: "selected"
                    }));
                    table_consumables.bootstrapTable('append', data);
                    // console.log( Array.from(table_consumables.bootstrapTable('getData'))[0]);
                    // Array.from(table_consumables.bootstrapTable('getData')).forEach(function(item) {
                    //     selected.push(item['consumable_id']);
                    // })

                    $('#modal_consumables').modal('hide');
                } else {
                    modal.find("#category_id").addClass("has-error");
                    modal.find("#name").addClass("has-error");
                }
            });

            function formatDatalistSafe(datalist) {
                // console.warn("What in the hell is going on with Select2?!?!!?!?");
                // console.warn($.select2);
                if (datalist.loading) {
                    return $('<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Loading...');
                }

                var root_div = $("<div class='clearfix'>") ;
                var left_pull = $("<div class='pull-left' style='padding-right: 10px;'>");
                if (datalist.image) {
                    var inner_div = $("<div style='width: 30px;'>");
                    /******************************************************************
                     *
                     * We are specifically chosing empty alt-text below, because this
                     * image conveys no additional information, relative to the text
                     * that will *always* be there in any select2 list that is in use
                     * in Snipe-IT. If that changes, we would probably want to change
                     * some signatures of some functions, but right now, we don't want
                     * screen readers to say "HP SuperJet 5000, .... picture of HP
                     * SuperJet 5000..." and so on, for every single row in a list of
                     * assets or models or whatever.
                     *
                     *******************************************************************/
                    var img = $("<img src='' style='max-height: 20px; max-width: 30px;' alt=''>");
                    // console.warn("Img is: ");
                    // console.dir(img);
                    // console.warn("Strigularly, that's: ");
                    // console.log(img);
                    img.attr("src", datalist.image );
                    inner_div.append(img)
                } else {
                    var inner_div=$("<div style='height: 20px; width: 30px;'></div>");
                }
                left_pull.append(inner_div);
                root_div.append(left_pull);
                var name_div = $("<div>");
                name_div.text(datalist.text);
                root_div.append(name_div)
                var safe_html = root_div.get(0).outerHTML;
                var old_html = formatDatalist(datalist);
                if(safe_html != old_html) {
                    //console.log("HTML MISMATCH: ");
                    //console.log("FormatDatalistSafe: ");
                    // console.dir(root_div.get(0));
                    //console.log(safe_html);
                    //console.log("FormatDataList: ");
                    //console.log(old_html);
                }
                return root_div;

            }
            function formatDatalist (datalist) {
                var loading_markup = '<i class="fas fa-spinner fa-spin" aria-hidden="true"></i> Loading...';
                if (datalist.loading) {
                    return loading_markup;
                }

                var markup = '<div class="clearfix">' ;
                markup += '<div class="pull-left" style="padding-right: 10px;">';
                if (datalist.image) {
                    markup += "<div style='width: 30px;'><img src='" + datalist.image + "' style='max-height: 20px; max-width: 30px;' alt='" +  datalist.text + "'></div>";
                } else {
                    markup += '<div style="height: 20px; width: 30px;"></div>';
                }

                markup += "</div><div>" + datalist.text + "</div>";
                markup += "</div>";
                return markup;
            }


        });
    </script>

@stop