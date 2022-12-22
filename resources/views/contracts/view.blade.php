@extends('layouts/default')

{{-- Page title --}}
@section('title')

    {{ trans('general.contract') }}:{{ $contract->name }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <div class="row">

        <div class="col-md-9">
            <div class="box box-default">
                <div class="box-header with-border">
                    <div class="box-heading">
                        <h2 class="box-title">{{ trans('general.assets') }}</h2>
                    </div>
                </div>
                <div class="box-body">
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
                                id="assetsTable"
                                class="table table-striped snipe-table"
                                data-url="{{route('api.assets.index', ['contract_id' => $contract->id]) }}">
                        </table>
                    </div><!-- /.table-responsive -->
                </div><!-- /.box-body -->
            </div> <!--/.box-->
            <div class="box box-default">
                <div class="box-header with-border">
                    <div class="box-heading">
                        <h2 class="box-title">Расходники</h2>
                    </div>
                </div>
                <div class="box-body">
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
                </div><!-- /.box-body -->
            </div> <!--/.box-->
        </div><!--/.col-md-9-->

        <div class="col-md-3">
            <div class="col-md-12">
                <ul class="list-unstyled" style="line-height: 25px; padding-bottom: 20px;">
                    @if ($contract->number!='')
                        <li>Номер договора: {{ $contract->number }}</li>
                    @endif
                    @if (($contract->bitrix_id))
                        <li><a href="https://bitrix.legis-s.ru/crm/contract/details/{{ $contract->bitrix_id}}/"
                               target="_blank">Открыть в Bitrix</a></li>
                    @endif
                </ul>
            </div>
            @can('checkout', \App\Models\Asset::class)
                @if ( count($contract->assets_no_docs) >0 or count($contract->consumable_no_docs_count)>0)
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
