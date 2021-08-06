@extends('layouts/default')

{{-- Page title --}}
@section('title')
 Расходник:   {{ $consumable->name }}
@parent
@stop

@section('header_right')
<a href="{{ URL::previous() }}" class="btn btn-primary pull-right">
  {{ trans('general.back') }}</a>
@stop


{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-9">
    <div class="box box-default">
      <div class="box-body">
        <div class="row">
          <div class="col-md-12">
            <div class="table table-responsive">
              <table
                      data-columns="{{ \App\Presenters\ConsumableAssignmentPresenter::dataTableLayout() }}"
                      data-cookie-id-table="consumablesCheckedoutTable"
                      data-toolbar="#toolbar"
                      data-pagination="true"
                      data-id-table="consumablesCheckedoutTable"
                      data-search="true"
                      data-side-pagination="server"
                      data-show-columns="true"
                      data-show-export="true"
                      data-show-footer="true"
                      data-show-refresh="true"
                      data-sort-order="asc"
                      data-sort-name="name"
                      id="consumablesCheckedoutTable"
                      class="table table-striped snipe-table"
                      data-url="{{route('api.consumableassignments.index',['consumable_id' => $consumable->id])}}">
              </table>
            </div>
          </div> <!-- /.col-md-12-->
        </div>
      </div>

    </div> <!-- /.box.box-default-->
  </div> <!-- /.col-md-9-->
  <div class="col-md-3">

    @if ($consumable->image!='')
      <div class="col-md-12 text-center" style="padding-bottom: 15px;">
        <a href="{{ app('consumables_upload_url') }}/{{ $consumable->image }}" data-toggle="lightbox"><img src="{{ app('consumables_upload_url') }}/{{ $consumable->image }}" class="img-responsive img-thumbnail" alt="{{ $consumable->name }}"></a>
      </div>
    @endif

    @if ($consumable->purchase_date)
      <div class="col-md-12" style="padding-bottom: 5px;">
        <strong>{{ trans('general.purchase_date') }}: </strong>
        {{ $consumable->purchase_date }}
      </div>
    @endif

    @if ($consumable->purchase_cost)
      <div class="col-md-12" style="padding-bottom: 5px;">
        <strong>{{ trans('general.purchase_cost') }}:</strong>
        {{ $snipeSettings->default_currency }}
        {{ \App\Helpers\Helper::formatCurrencyOutput($consumable->purchase_cost) }}
      </div>
    @endif

    @if ($consumable->item_no)
      <div class="col-md-12" style="padding-bottom: 5px;">
        <strong>{{ trans('admin/consumables/general.item_no') }}:</strong>
        {{ $consumable->item_no }}
      </div>
    @endif

    @if ($consumable->model_number)
      <div class="col-md-12" style="padding-bottom: 5px;">
        <strong>{{ trans('general.model_no') }}:</strong>
        {{ $consumable->model_number }}
      </div>
    @endif

    @if ($consumable->manufacturer)
      <div class="col-md-12" style="padding-bottom: 5px;">
        <strong>{{ trans('general.manufacturer') }}:</strong>
        {{ $consumable->manufacturer->name }}
      </div>
    @endif

    @if ($consumable->order_number)
      <div class="col-md-12" style="padding-bottom: 5px;">
        <strong>{{ trans('general.order_number') }}:</strong>
        {{ $consumable->order_number }}
      </div>
    @endif
      <div class="col-md-12" style="padding-bottom: 5px;">
        <h2>{{ trans('admin/consumables/general.about_consumables_title') }}</h4>
        <p>{{ trans('admin/consumables/general.about_consumables_text') }} </p>
      </div>
  </div> <!-- /.col-md-3-->
</div> <!-- /.row-->

@stop

@section('moar_scripts')
@include ('partials.bootstrap-table', ['exportFile' => 'consumable' . $consumable->name . '-export', 'search' => false])
@stop
