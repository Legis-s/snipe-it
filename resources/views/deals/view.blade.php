@extends('layouts/default')

{{-- Page title --}}
@section('title')

    {{ trans('general.deal') }}: {{ $deal->number }} {{ $deal->name }}
    @parent
@stop

@section('header_right')
    <a href="{{ route('deals.index') }}" class="btn btn-primary" style="margin-right: 10px;">
        {{ trans('general.back') }}</a>
@endsection

{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-9">

            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs hidden-print">

                    <li class="active">
                        <a href="#assets" data-toggle="tab" data-tooltip="true"
                           title="{{ trans('admin/locations/message.sold_assets') }}">
                            <i class="fas fa-rub" style="font-size: 17px" aria-hidden="true"></i>
{{--                            <span class="badge">--}}
{{--                          {{ number_format($deal->assignedAssets()->AssetsForShow()->count()) }}--}}
{{--                      </span>--}}
                            <span class="sr-only">
                          {{ trans('admin/locations/message.assigned_assets') }}
                      </span>
                        </a>
                    </li>


                    <li>
                        <a href="#consumables" data-toggle="tab" data-tooltip="true"
                           title="{{ trans('general.consumables') }}">
                            <i class="fas fa-tint" style="font-size: 17px" aria-hidden="true"></i>
{{--                            <span class="badge">--}}
{{--                                                  {{ number_format($deal->consumable->count()) }}--}}
{{--                                              </span>--}}
                            <span class="sr-only">
                                                  {{ trans('general.consumables') }}
                                              </span>
                        </a>
                    </li>

                    <li>
                        <a href="#history" data-toggle="tab" data-toggle="tab" data-tooltip="true"
                           title="{{ trans('general.history') }}">
                            <i class="fa-solid fa-clock-rotate-left" style="font-size: 17px" aria-hidden="true"></i>
                            <span class="sr-only">
                          {{ trans('general.history') }}
                    </span>
                        </a>
                    </li>
                </ul>

            <div class="tab-content">
                <div class="tab-pane active" id="assets">
                    <h2 class="box-title">{{ trans('general.assets') }}</h2>
                    @include('partials.asset-bulk-actions')
                    <div class="table table-responsive">
                        <table
                                data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
                                data-cookie-id-table="assetsTable"
                                data-pagination="true"
                                data-id-table="assetsTable"
                                data-search="true"
                                data-side-pagination="server"
                                data-show-columns="true"
                                data-show-export="true"
                                data-show-refresh="true"
                                data-sort-order="asc"
                                data-toolbar="#assetsBulkEditToolbar"
                                id="assetsTable"
                                class="table table-striped snipe-table"
                                data-url="{{route('api.assets.index', ['deal_id' => $deal->id]) }}">
                        </table>
                    </div><!-- /.table-responsive -->
                </div><!-- /.tab-pane -->

                <div class="tab-pane" id="consumables">
                    <h2 class="box-title">{{ trans('general.consumables') }}</h2>
                    <div class="table table-responsive">
                        <table
                                data-columns="{{ \App\Presenters\ConsumableAssignmentPresenter::dataTableLayoutIn() }}"
                                data-cookie-id-table="consumablesCheckedoutTable"
                                data-pagination="true"
                                data-id-table="consumablesCheckedoutTable"
                                data-search="false"
                                data-side-pagination="server"
                                data-show-columns="true"
                                data-show-export="true"
                                data-show-footer="true"
                                data-show-refresh="true"
                                data-sort-order="asc"
                                data-sort-name="name"
                                id="consumablesCheckedoutTable"
                                class="table table-striped snipe-table"
                                data-url="{{route('api.consumableassignments.index',['deal_id'=> $deal->id])}}">
                        </table>
                    </div><!-- /.table-responsive -->
                </div><!-- /.tab-pane -->

                <div class="tab-pane" id="history">
                    <h2 class="box-title">{{ trans('general.history') }}</h2>
                    <!-- checked out assets table -->
                    <div class="row">
                        <div class="col-md-12">
                            <table
                                    class="table table-striped snipe-table"
                                    id="assetHistory"
                                    data-id-table="assetHistory"
                                    data-side-pagination="server"
                                    data-sort-order="desc"
                                    data-sort-name="created_at"
                                    data-export-options='{
                        "fileName": "export-deal-asset-{{  $deal->id }}-history",
                        "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                    }'

                                    data-url="{{ route('api.activity.index', ['target_id' => $deal->id, 'target_type' => 'deal']) }}"
                                    data-cookie-id-table="assetHistory"
                                    data-cookie="true">
                                <thead>
                                <tr>
                                    <th data-visible="true" data-field="icon" style="width: 40px;"
                                        class="hidden-xs"
                                        data-formatter="iconFormatter">{{ trans('admin/hardware/table.icon') }}</th>
                                    <th class="col-sm-2" data-visible="true" data-field="action_date"
                                        data-formatter="dateDisplayFormatter">{{ trans('general.date') }}</th>
                                    <th class="col-sm-1" data-visible="true" data-field="admin"
                                        data-formatter="usersLinkObjFormatter">{{ trans('general.created_by') }}</th>
                                    <th class="col-sm-1" data-visible="true"
                                        data-field="action_type">{{ trans('general.action') }}</th>
                                    <th class="col-sm-2" data-visible="true" data-field="item"
                                        data-formatter="polymorphicItemFormatter">{{ trans('general.item') }}</th>
                                    <th class="col-sm-2" data-visible="true" data-field="target"
                                        data-formatter="polymorphicItemFormatter">{{ trans('general.target') }}</th>
                                    <th class="col-sm-2" data-field="note">{{ trans('general.notes') }}</th>
                                    <th class="col-md-3" data-field="signature_file" data-visible="false"
                                        data-formatter="imageFormatter">{{ trans('general.signature') }}</th>
                                    <th class="col-md-3" data-visible="false" data-field="file"
                                        data-visible="false"
                                        data-formatter="fileUploadFormatter">{{ trans('general.download') }}</th>
                                    <th class="col-sm-2" data-field="log_meta" data-visible="true"
                                        data-formatter="changeLogFormatter">{{ trans('admin/hardware/table.changed')}}</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div> <!-- /.row -->
                </div> <!-- /.tab-pane history -->
            </div><!--/.col-md-9-->
        </div><!--/.col-md-9-->
    </div><!--/.col-md-9-->

    <div class="col-md-3">
        @if (($deal->bitrix_id))
            <div class="col-md-12" style="padding-bottom: 20px;">
                <a href="https://bitrix.legis-s.ru/crm/deal/details/{{ $deal->bitrix_id}}/" style="width: 100%;"
                   class="btn btn-sm btn-info pull-left">{{ trans('general.bitrix_open') }} </a>
            </div>
        @endif

        <div class="col-md-12">
            <ul class="list-unstyled" style="line-height: 25px; padding-bottom: 20px;">
                @if ($deal->number!='')
                    <li>Номер договора: {{ $deal->number }}</li>
                @endif
            </ul>
        </div>
    </div>
    </div>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', [
        'exportFile' => 'contracts-export',
        'search' => true
     ])
@stop
