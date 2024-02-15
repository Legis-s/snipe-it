@extends('layouts/default')

{{-- Page title --}}
@section('title')

    {{ $massoperation->name }}

    @parent
@stop


{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-9">
            <div class="box box-default">
                <div class="box-header with-border">
                    <div class="box-heading">
                        <h2 class="box-title">Активы</h2>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table">
                        <table
                                data-advanced-search="true"
                                data-click-to-select="true"
                                data-columns="{{ \App\Presenters\AssetPresenter::dataTableLayout() }}"
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
                                data-url="{{ route('api.assets.index',array('massoperation_id' => $massoperation->id ))}}">
                        </table>
                    </div><!-- /.table-responsive -->
                </div><!-- /.box-body -->
            </div> <!--/.box-->
        </div><!--/.col-md-9-->
        <div class="col-md-3">
            <div class="box box-default">
                <div class="box-header with-border">
                    <div class="box-heading">
                        <h2 class="box-title">Информация</h2>
                    </div>
                </div>
                <div class="box-body">
                    @if ($massoperation->user)
                        <div class="row">
                            <div class="col-md-6">
                                <strong>
                                    Пользователь
                                </strong>
                            </div>
                            <div class="col-md-6">
                                {{ $massoperation->user->getFullNameAttribute() }}
                            </div>
                        </div>
                    @endif
                    @if ($massoperation->assets)
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>
                                        Кол-во активов
                                    </strong>
                                </div>
                                <div class="col-md-6">
                                    {{ count($massoperation->assets) }}
                                </div>
                            </div>
                        @endif
                        @if ($massoperation->consumables)
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>
                                        Кол-во расходников
                                    </strong>
                                </div>
                                <div class="col-md-6">
                                    {{ count($massoperation->consumables) }}
                                </div>
                            </div>
                        @endif
                        @if ($massoperation->bitrix_task_id)
                            <div class="row">
                                <div class="col-md-6">
                                    <strong>
                                        Bitrix задача
                                    </strong>
                                </div>
                                <div class="col-md-6">
                                    <a href="https://bitrix.legis-s.ru/company/personal/user/290/tasks/task/view/{{ $massoperation->bitrix_task_id }}/">ссылка</a>
                                </div>
                            </div>
                        @endif
                    @if ($massoperation->operation_type)
                        <div class="row">
                            <div class="col-md-6">
                                <strong>
                                    Операция
                                </strong>
                            </div>
                            <div class="col-md-6">
                                @if ($massoperation->operation_type == 'checkout')
                                выдача
                                @elseif($massoperation->operation_type == 'checkin')
                                возврат
                                @elseif($massoperation->operation_type == 'sell')
                                продажа
                                @endif
                            </div>
                        </div>
                    @endif
                    @if ($massoperation->created_at)
                        <div class="row">
                            <div class="col-md-6">
                                <strong>
                                    Дата
                                </strong>
                            </div>
                            <div class="col-md-6">
                                {{ date('Y.m.d', strtotime((string) $massoperation->created_at))}}
                            </div>
                        </div>
                    @endif
                    @if ($massoperation->contract_id)
                        <div class="row">
                            <div class="col-md-6">
                                <strong>
                                    Договор
                                </strong>
                            </div>
                            <div class="col-md-6">
                                {{ $massoperation->contract_id}}
                            </div>
                        </div>
                    @endif
                        @if ($massoperation->note)
                        <div class="row">
                            <div class="col-md-6">
                                <strong>
                                    Комментарий
                                </strong>
                            </div>
                            <div class="col-md-6">
                                {{ $massoperation->note }}
                            </div>
                        </div>
                    @endif


                </div><!-- /.box-body -->
            </div> <!--/.box-->
        </div>
        @if ($massoperation->consumables->count() >0)
        <div class="col-md-9">
            <div class="box box-default">
                <div class="box-header with-border">
                    <div class="box-heading">
                        <h2 class="box-title">Расходные материалы</h2>
                    </div>
                </div>
                <div class="box-body">
                    <div class="table">
                        <table
                                data-advanced-search="true"
                                data-click-to-select="true"
                                data-columns="{{ \App\Presenters\ConsumableAssignmentPresenter::dataTableLayout() }}"
                                data-cookie-id-table="consumablesBulkTable"
                                data-pagination="true"
                                data-id-table="consumablesBulkTable"
                                data-search="true"
                                data-side-pagination="server"
                                data-show-columns="true"
                                data-show-footer="true"
                                data-show-refresh="true"
                                data-sort-order="asc"
                                data-sort-name="name"
                                data-toolbar="#toolbar"
                                data-queryParams="#toolbar"
                                id="consumablesBulkTable"
                                class="table table-striped snipe-table"
                                data-url="{{ route('api.consumableassignments.index',array('massoperation_id' => $massoperation->id ))}}">
                        </table>
                    </div><!-- /.table-responsive -->
                </div><!-- /.box-body -->
            </div> <!--/.box-->
        </div><!--/.col-md-9-->
        @endif
    </div>

@stop

@section('moar_scripts')
    @include('partials.bootstrap-table')
@stop