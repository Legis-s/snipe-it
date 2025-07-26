@extends('layouts/default')

{{-- Page title --}}
@section('title')

    {{ trans('general.contract') }}: {{ $contract->name }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-9">

            <div class="nav-tabs-custom">
                <ul class="nav nav-tabs hidden-print">

                    <li class="active">
                        <a href="#assets" data-toggle="tab">
                        <span class="hidden-lg hidden-md">
                            <i class="fas fa-users fa-2x"></i>
                        </span>
                            <span class="hidden-xs hidden-sm">
                          {{ trans('general.assets') }}
                      </span>
                        </a>
                    </li>

                    <li>
                        <a href="#consumables" data-toggle="tab">
                    <span class="hidden-lg hidden-md">
                        <i class="fas fa-barcode fa-2x" aria-hidden="true"></i>
                    </span>
                            <span class="hidden-xs hidden-sm">
                          {{ trans('general.consumables') }}
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
                                    data-url="{{route('api.assets.index', ['contract_id' => $contract->id]) }}">
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
                                    data-url="{{route('api.consumableassignments.index',['contract_id'=> $contract->id])}}">
                            </table>
                        </div><!-- /.table-responsive -->
                    </div><!-- /.tab-pane -->
                </div><!--/.col-md-9-->
            </div><!--/.col-md-9-->
        </div><!--/.col-md-9-->

        <div class="col-md-3">
            @if (($contract->bitrix_id))
                <div class="col-md-12" style="padding-bottom: 20px;">
                    <a href="https://bitrix.legis-s.ru/crm/contract/details/{{ $contract->bitrix_id}}/" style="width: 100%;" class="btn btn-sm btn-info pull-left">{{ trans('general.bitrix_open') }} </a>
                </div>
            @endif

            <div class="col-md-12">
                <ul class="list-unstyled" style="line-height: 25px; padding-bottom: 20px;">
                    @if ($contract->number!='')
                        <li>Номер договора: {{ $contract->number }}</li>
                    @endif
                </ul>
            </div>
            @can('checkout', \App\Models\Asset::class)
                @if (($contract->assets_no_docs && count($contract->assets_no_docs) >0) or ($contract->consumable_no_docs_count && count($contract->consumable_no_docs_count>0)))
                <div class="col-md-12" id="closeing_docs_div">
                    <div id="closeing_docs" style="margin-bottom:10px; width:100%" class="btn btn-primary pull-right">
                        Есть закрывающие документы
                    </div>
                </div>
                @endif
            @endcan
        </div>
    </div>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', [
        'exportFile' => 'contracts-export',
        'search' => true
     ])
    <script nonce="{{ csrf_token() }}">
        $(function () {
            $( "#closeing_docs" ).click(function() {
                $( "#closeing_docs" ).addClass('disabled');
                $.ajax({
                    type: 'POST',
                    url:"/api/v1/contracts/{{ $contract->id }}/closesell",
                    headers: {
                        "X-Requested-With": 'XMLHttpRequest',
                        "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                    },
                    dataType: 'json',
                    success: function (data) {
                        $( "#closeing_docs_div" ).html("");
                        $( "#assetsTable" ).bootstrapTable('refresh');
                        $( "#consumablesCheckedoutTable" ).bootstrapTable('refresh');
                    },
                });
            });
        });
    </script>
@stop
