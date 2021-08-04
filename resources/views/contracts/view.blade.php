@extends('layouts/default')

{{-- Page title --}}
@section('title')

    Договор:
    {{ $contract->name }}

    @parent
@stop

{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-9">
            <div class="box box-default">
                <div class="box-header with-border">
                    <div class="box-heading">
                        <h2 class="box-title">Активы на продажу</h2>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table table-responsive">
                        <table
                                data-columns="{{ \App\Presenters\SalesPresenter::dataTableLayout() }}"
                                data-cookie-id-table="assetsListingTable"
                                data-pagination="true"
                                data-id-table="assetsListingTable"
                                data-search="true"
                                data-side-pagination="server"
                                data-show-columns="true"
                                data-show-export="true"
                                data-show-refresh="true"
                                data-sort-order="asc"
                                id="assetsListingTable"
                                class="table table-striped snipe-table"
                                data-url="{{route('api.sales.index', ['contract_id' => $contract->id]) }}">
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
        </div>
    </div>
    </div>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', [
        'exportFile' => 'locations-export',
        'search' => true
     ])

@stop
