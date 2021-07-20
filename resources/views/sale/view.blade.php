@extends('layouts/default')

{{-- Page title --}}
@section('title')
{{ trans('admin/hardware/general.view') }} на продажу  {{ $sale->asset_tag }}
@parent
@stop

{{-- Right header --}}
@section('header_right')

@can('manage', \App\Models\Sale::class)
<div class="dropdown pull-right">
  <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">{{ trans('button.actions') }}
    <span class="caret"></span>
  </button>
  <ul class="dropdown-menu pull-right" role="menu">
      @can('update', \App\Models\Sale::class)
        <li role="menuitem">
          <a href="{{ route('sales.edit', $sale->id) }}">
            {{ trans('admin/hardware/general.edit') }}
          </a>
        </li>
      @endcan
        <li role="menuitem">
          <a href="#" id="print_tag">
            Напечатать этикетку
          </a>
        </li>

{{--      @can('create', \App\Models\Sale::class)--}}
{{--          <li role="menuitem">--}}
{{--            <a href="{{ route('clone/hardware', $sale->id) }}">--}}
{{--              {{ trans('admin/hardware/general.clone') }}--}}
{{--            </a>--}}
{{--          </li>--}}
{{--      @endcan--}}

{{--      @can('audit', \App\Models\Asset::class)--}}
{{--          <li role="menuitem">--}}
{{--            <a href="{{ route('asset.audit.create', $sale->id)  }}">--}}
{{--              {{ trans('general.audit') }}--}}
{{--            </a>--}}
{{--          </li>--}}
{{--     @endcan--}}
  </ul>
</div>
@endcan
@stop

{{-- Page content --}}
@section('content')

<div class="row">

  @if (!$sale->model)
    <div class="col-md-12">
      <div class="callout callout-danger">
        <h2>NO MODEL ASSOCIATED</h4>
        <p>This will break things in weird and horrible ways. Edit this asset now to assign it a model. </p>
      </div>
    </div>
  @endif

  @if ($sale->deleted_at!='')
    <div class="col-md-12">
      <div class="alert alert-danger">
        <i class="fa fa-exclamation-circle faa-pulse animated" aria-hidden="true"></i>
        <strong>WARNING: </strong>
        This asset has been deleted.
        You must <a href="{{ route('restore/hardware', $sale->id) }}">restore it</a> before you can assign it to someone.
      </div>
    </div>
  @endif

  <div class="col-md-12">




    <!-- Custom Tabs -->
    <div class="nav-tabs-custom">
      <ul class="nav nav-tabs">
        <li class="active">
          <a href="#details" data-toggle="tab">
            <span class="hidden-lg hidden-md">
              <i class="fa fa-info-circle" aria-hidden="true"></i>
            </span>
            <span class="hidden-xs hidden-sm">
              {{ trans('general.details') }}
            </span>
          </a>
        </li>
        <li>
          <a href="#history" data-toggle="tab">
            <span class="hidden-lg hidden-md">
              <i class="fa fa-history" aria-hidden="true"></i>
            </span>
            <span class="hidden-xs hidden-sm">
              {{ trans('general.history') }}
            </span>
          </a>
        </li>
        @can('update', \App\Models\Asset::class)
        <li class="pull-right">
          <a href="#" data-toggle="modal" data-target="#uploadFileModal">
            <i class="fa fa-paperclip" aria-hidden="true"></i>
            {{ trans('button.upload') }}
          </a>
        </li>
        @endcan
      </ul>
      <div class="tab-content">
        <div class="tab-pane fade in active" id="details">
          <div class="row">
            <div class="col-md-8">


              <!-- start striped rows -->
              <div class="container row-striped">

                @if ($sale->assetstatus)

                  <div class="row">
                      <div class="col-md-2">
                          <strong>{{ trans('general.status') }}</strong>
                      </div>
                      <div class="col-md-6">
                        @if (($sale->assignedTo) && ($sale->deleted_at==''))
                          <i class="fa fa-circle text-blue"></i>
                          {{ $sale->assetstatus->name }}
                          <label class="label label-default">{{ trans('general.deployed') }}</label>

                          <i class="fa fa-long-arrow-right" aria-hidden="true"></i>
                          {!!  $sale->assignedTo->present()->glyph()  !!}
                          {!!  $sale->assignedTo->present()->nameUrl() !!}
                        @else
                          @if (($sale->assetstatus) && ($sale->assetstatus->deployable=='1'))
                            <i class="fa fa-circle text-green"></i>
                          @elseif (($sale->assetstatus) && ($sale->assetstatus->pending=='1'))
                            <i class="fa fa-circle text-orange"></i>
                          @elseif (($sale->assetstatus) && ($sale->assetstatus->archived=='1'))
                            <i class="fa fa-times text-red"></i>
                          @endif
                          <a href="{{ route('statuslabels.show', $sale->assetstatus->id) }}">
                            {{ $sale->assetstatus->name }}</a>
                          <label class="label label-default">{{ $sale->present()->statusMeta }}</label>

                        @endif
                      </div>
                  </div>
                @endif

                @if ($sale->company)
                <div class="row">
                  <div class="col-md-2">
                    <strong>{{ trans('general.company') }}</strong>
                  </div>
                  <div class="col-md-6">
                    <a href="{{ url('/companies/' . $sale->company->id) }}">{{ $sale->company->name }}</a>
                  </div>
                </div>
                @endif

                  @if ($sale->name)
                  <div class="row">
                    <div class="col-md-2">
                      <strong>{{ trans('admin/hardware/form.name') }}</strong>
                    </div>
                    <div class="col-md-6">
                      {{ $sale->name }}
                    </div>
                  </div>
                 @endif

                  @if ($sale->serial)
                  <div class="row">
                    <div class="col-md-2">
                      <strong>{{ trans('admin/hardware/form.serial') }}</strong>
                    </div>
                    <div class="col-md-6">
                      {{ $sale->serial  }}
                    </div>
                  </div>
                  @endif


                  @if ((isset($audit_log)) && ($audit_log->created_at))
                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('general.last_audit') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      {{ \App\Helpers\Helper::getFormattedDateObject($audit_log->created_at, 'date', false) }} (by {{ link_to_route('users.show', $audit_log->user->present()->fullname(), [$audit_log->user->id]) }})
                    </div>
                </div>
                  @endif

                  @if ($sale->next_audit_date)
                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('general.next_audit_date') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      {{ \App\Helpers\Helper::getFormattedDateObject($sale->next_audit_date, 'date', false) }}
                    </div>
                  </div>
                  @endif

                  @if (($sale->model) && ($sale->model->manufacturer))
                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('admin/hardware/form.manufacturer') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      <ul class="list-unstyled" style="line-height: 25px;">
                        @can('view', \App\Models\Manufacturer::class)

                          <li>
                            <a href="{{ route('manufacturers.show', $sale->model->manufacturer->id) }}">
                              {{ $sale->model->manufacturer->name }}
                            </a>
                          </li>

                        @else
                          <li> {{ $sale->model->manufacturer->name }}</li>
                        @endcan

                        @if (($sale->model) && ($sale->model->manufacturer->url))
                          <li>
                            <i class="fa fa-globe" aria-hidden="true"></i>
                            <a href="{{ $sale->model->manufacturer->url }}">
                              {{ $sale->model->manufacturer->url }}
                            </a>
                          </li>
                        @endif

                        @if (($sale->model) && ($sale->model->manufacturer->support_url))
                          <li>
                            <i class="fa fa-life-ring" aria-hidden="true"></i>
                            <a href="{{ $sale->model->manufacturer->support_url }}">
                              {{ $sale->model->manufacturer->support_url }}
                            </a>
                          </li>
                        @endif

                        @if (($sale->model) && ($sale->model->manufacturer->support_phone))
                          <li>
                            <i class="fa fa-phone" aria-hidden="true"></i>
                            <a href="tel:{{ $sale->model->manufacturer->support_phone }}">
                              {{ $sale->model->manufacturer->support_phone }}
                            </a>
                          </li>
                        @endif

                        @if (($sale->model) && ($sale->model->manufacturer->support_email))
                          <li><i class="fa fa-envelope" aria-hidden="true"></i>
                            <a href="mailto:{{ $sale->model->manufacturer->support_email }}">
                              {{ $sale->model->manufacturer->support_email }}
                            </a>
                          </li>
                        @endif
                      </ul>
                    </div>
                  </div>
                  @endif

                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('general.category') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      @if (($sale->model) && ($sale->model->category))

                        @can('view', \App\Models\Category::class)

                          <a href="{{ route('categories.show', $sale->model->category->id) }}">
                            {{ $sale->model->category->name }}
                          </a>
                        @else
                          {{ $sale->model->category->name }}
                        @endcan
                      @else
                        Invalid category
                      @endif
                    </div>
                  </div>

                  @if ($sale->model)
                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('admin/hardware/form.model') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      @if ($sale->model)

                        @can('view', \App\Models\AssetModel::class)
                          <a href="{{ route('models.show', $sale->model->id) }}">
                            {{ $sale->model->name }}
                          </a>
                        @else
                          {{ $sale->model->name }}
                        @endcan

                      @endif
                    </div>
                  </div>
                  @endif

                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('admin/models/table.modelnumber') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      {{ ($sale->model) ? $sale->model->model_number : ''}}
                    </div>
                  </div>

                  @if (($sale->model) && ($sale->model->fieldset))
                    @foreach($sale->model->fieldset->fields as $field)
                      <div class="row">
                        <div class="col-md-2">
                          <strong>
                            {{ $field->name }}
                          </strong>
                        </div>
                        <div class="col-md-6">
                          @if ($field->field_encrypted=='1')
                            <i class="fa fa-lock" data-toggle="tooltip" data-placement="top" title="{{ trans('admin/custom_fields/general.value_encrypted') }}"></i>
                          @endif

                          @if ($field->isFieldDecryptable($sale->{$field->db_column_name()} ))
                            @can('superuser')
                              @if (($field->format=='URL') && ($sale->{$field->db_column_name()}!=''))
                                <a href="{{ \App\Helpers\Helper::gracefulDecrypt($field, $sale->{$field->db_column_name()}) }}" target="_new">{{ \App\Helpers\Helper::gracefulDecrypt($field, $sale->{$field->db_column_name()}) }}</a>
                              @else
                                {{ \App\Helpers\Helper::gracefulDecrypt($field, $sale->{$field->db_column_name()}) }}
                              @endif
                            @else
                              {{ strtoupper(trans('admin/custom_fields/general.encrypted')) }}
                            @endcan

                          @else
                            @if (($field->format=='URL') && ($sale->{$field->db_column_name()}!=''))
                              <a href="{{ $sale->{$field->db_column_name()} }}" target="_new">{{ $sale->{$field->db_column_name()} }}</a>
                            @else
                              {!! nl2br(e($sale->{$field->db_column_name()})) !!}
                            @endif
                          @endif
                        </div>
                      </div>
                    @endforeach
                  @endif


                  @if ($sale->purchase_date)
                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('admin/hardware/form.date') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      {{ \App\Helpers\Helper::getFormattedDateObject($sale->purchase_date, 'date', false) }}
                    </div>
                  </div>
                  @endif

                  @if ($sale->purchase_cost)
                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('admin/hardware/form.cost') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      @if (($sale->id) && ($sale->location))
                        {{ $sale->location->currency }}
                      @elseif (($sale->id) && ($sale->location))
                        {{ $sale->location->currency }}
                      @else
                        {{ $snipeSettings->default_currency }}
                      @endif
                      {{ \App\Helpers\Helper::formatCurrencyOutput($sale->purchase_cost)}}

                    </div>
                  </div>
                  @endif


                  @if ($sale->depreciable_cost)
                    <div class="row">
                      <div class="col-md-2">
                        <strong>
                          Остаточная стоимость
                        </strong>
                      </div>
                      <div class="col-md-6">
                        @if (($sale->id) && ($sale->location))
                          {{ $sale->location->currency }}
                        @elseif (($sale->id) && ($sale->location))
                          {{ $sale->location->currency }}
                        @else
                          {{ $snipeSettings->default_currency }}
                        @endif
                        {{ \App\Helpers\Helper::formatCurrencyOutput($sale->depreciable_cost)}}
                      </div>
                    </div>
                  @endif

                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        Состояние
                      </strong>
                    </div>
                    <div class="col-md-6">
                      @if ($sale->quality == 5)
                        <i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i>
                      @elseif  ($sale->quality == 4)
                        <i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i>
                      @elseif  ($sale->quality == 3)
                        <i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i>
                      @elseif  ($sale->quality == 2)
                        <i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i>
                      @elseif  ($sale->quality == 1)
                        <i class="fa fa-star" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i>
                      @else
                        <i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i><i class="fa fa-star-o" aria-hidden="true"></i>
                      @endif

                    </div>
                  </div>

                  @if ($sale->order_number)
                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('general.order_number') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      #{{ $sale->order_number }}
                    </div>
                  </div>
                  @endif

                  @if ($sale->supplier)
                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('general.supplier') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      @can ('superuser')
                        <a href="{{ route('suppliers.show', $sale->supplier_id) }}">
                          {{ $sale->supplier->name }}
                        </a>
                      @else
                        {{ $sale->supplier->name }}
                      @endcan
                    </div>
                  </div>
                  @endif


                  @if ($sale->warranty_months)
                  <div class="row{!! $sale->present()->warrantee_expires() < date("Y-m-d") ? ' warning' : '' !!}">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('admin/hardware/form.warranty') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      {{ $sale->warranty_months }}
                      {{ trans('admin/hardware/form.months') }}

                      ({{ trans('admin/hardware/form.expires') }}
                      {{ $sale->present()->warrantee_expires() }})
                    </div>
                  </div>
                  @endif

                  @if (($sale->model) && ($sale->depreciation))
                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('general.depreciation') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      {{ $sale->depreciation->name }}
                      ({{ $sale->depreciation->months }}
                      {{ trans('admin/hardware/form.months') }}
                      )
                    </div>
                  </div>
                    <div class="row">
                      <div class="col-md-2">
                        <strong>
                          {{ trans('admin/hardware/form.fully_depreciated') }}
                        </strong>
                      </div>
                      <div class="col-md-6">
                        @if ($sale->time_until_depreciated()->y > 0)
                          {{ $sale->time_until_depreciated()->y }}
                          {{ trans('admin/hardware/form.years') }},
                        @endif
                        {{ $sale->time_until_depreciated()->m }}
                        {{ trans('admin/hardware/form.months') }}
                        ({{ $sale->depreciated_date()->format('Y-m-d') }})
                      </div>
                    </div>
                  @endif

                  @if (($sale->model) && ($sale->model->eol))
                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('admin/hardware/form.eol_rate') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      {{ $sale->model->eol }}
                      {{ trans('admin/hardware/form.months') }}

                    </div>
                  </div>

                    <div class="row">
                      <div class="col-md-2">
                        <strong>
                          {{ trans('admin/hardware/form.eol_date') }}
                        </strong>
                      </div>
                      <div class="col-md-6">
                        {{ $sale->present()->eol_date() }}


                        @if ($sale->present()->months_until_eol())
                          -
                          @if ($sale->present()->months_until_eol()->y > 0)
                            {{ $sale->present()->months_until_eol()->y }}
                            {{ trans('general.years') }},
                          @endif

                          {{ $sale->present()->months_until_eol()->m }}
                          {{ trans('general.months') }}

                        @endif

                      </div>
                    </div>
                  @endif

                  @if ($sale->expected_checkin!='')
                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('admin/hardware/form.expected_checkin') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      {{ \App\Helpers\Helper::getFormattedDateObject($sale->expected_checkin, 'date', false) }}
                    </div>
                  </div>
                  @endif

                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('admin/hardware/form.notes') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      {!! nl2br(e($sale->notes)) !!}
                    </div>
                  </div>

                  @if ($sale->location)
                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('general.location') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      @can('superuser')
                        <a href="{{ route('locations.show', ['location' => $sale->location->id]) }}">
                          {{ $sale->location->name }}
                        </a>
                      @else
                        {{ $sale->location->name }}
                      @endcan
                    </div>
                  </div>
                  @endif

                  @if ($sale->defaultLoc)
                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('admin/hardware/form.default_location') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      @can('superuser')
                        <a href="{{ route('locations.show', ['location' => $sale->defaultLoc->id]) }}">
                          {{ $sale->defaultLoc->name }}
                        </a>
                      @else
                        {{ $sale->defaultLoc->name }}
                      @endcan
                    </div>
                  </div>
                  @endif

                  @if ($sale->created_at!='')
                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('general.created_at') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      {{ \App\Helpers\Helper::getFormattedDateObject($sale->created_at, 'datetime', false) }}
                    </div>
                  </div>
                  @endif

                  @if ($sale->updated_at!='')
                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('general.updated_at') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      {{ \App\Helpers\Helper::getFormattedDateObject($sale->updated_at, 'datetime', false) }}
                    </div>
                  </div>
                  @endif


                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('general.checkouts_count') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      {{ ($sale->checkouts) ? (int) $sale->checkouts->count() : '0' }}
                    </div>
                  </div>


                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('general.checkins_count') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      {{ ($sale->checkins) ? (int) $sale->checkins->count() : '0' }}
                    </div>
                  </div>


                  <div class="row">
                    <div class="col-md-2">
                      <strong>
                        {{ trans('general.user_requests_count') }}
                      </strong>
                    </div>
                    <div class="col-md-6">
                      {{ ($sale->userRequests) ? (int) $sale->userRequests->count() : '0' }}
                    </div>
                  </div>

              </div> <!-- end row-striped -->

            </div><!-- /col-md-8 -->

            <div class="col-md-4">

              @if ($sale->image)
                <div class="col-md-12 text-center" style="padding-bottom: 15px;">
                  <a href="{{ url('/') }}/uploads/assets/{{ $sale->image }}" data-toggle="lightbox">
                    <img src="{{ url('/') }}/uploads/assets/{{{ $sale->image }}}" class="assetimg img-responsive" alt="{{ $sale->getDisplayNameAttribute() }}">
                  </a>
                </div>
              @elseif (($sale->model) && ($sale->model->image!=''))
                <div class="col-md-12 text-center" style="padding-bottom: 15px;">
                  <a href="{{ url('/') }}/uploads/models/{{ $sale->model->image }}" data-toggle="lightbox">
                    <img src="{{ url('/') }}/uploads/models/{{ $sale->model->image }}" class="assetimg img-responsive" alt="{{ $sale->getDisplayNameAttribute() }}">
                  </a>
                </div>
              @endif

              @if  ($snipeSettings->qr_code=='1')
                 <img src="{{ config('app.url') }}/hardware/{{ $sale->id }}/qr_code" class="img-thumbnail pull-right" style="height: 100px; width: 100px; margin-right: 10px;" alt="QR code for {{ $sale->getDisplayNameAttribute() }}">
              @endif

              @if (($sale->assignedTo) && ($sale->deleted_at==''))
                <h2>{{ trans('admin/hardware/form.checkedout_to') }}</h4>
                <p>
                  @if($sale->checkedOutToUser()) <!-- Only users have avatars currently-->
                  <img src="{{ $sale->assignedTo->present()->gravatar() }}" class="user-image-inline" alt="{{ $sale->assignedTo->present()->fullName() }}">
                  @endif
                  {!! $sale->assignedTo->present()->glyph() . ' ' .$sale->assignedTo->present()->nameUrl() !!}
                </p>

                  <ul class="list-unstyled" style="line-height: 25px;">
                  @if ((isset($sale->assignedTo->email)) && ($sale->assignedTo->email!=''))
                    <li>
                      <i class="fa fa-envelope-o" aria-hidden="true"></i>
                      <a href="mailto:{{ $sale->assignedTo->email }}">{{ $sale->assignedTo->email }}</a>
                    </li>
                  @endif

                  @if ((isset($sale->assignedTo)) && ($sale->assignedTo->phone!=''))
                    <li>
                      <i class="fa fa-phone" aria-hidden="true"></i>
                      <a href="tel:{{ $sale->assignedTo->phone }}">{{ $sale->assignedTo->phone }}</a>
                    </li>
                  @endif

                  @if (isset($sale->location))
                    <li>{{ $sale->location->name }}</li>
                    <li>{{ $sale->location->address }}
                      @if ($sale->location->address2!='')
                      {{ $sale->location->address2 }}
                      @endif
                    </li>

                    <li>{{ $sale->location->city }}
                      @if (($sale->location->city!='') && ($sale->location->state!=''))
                          ,
                      @endif
                      {{ $sale->location->state }} {{ $sale->location->zip }}
                    </li>
                    @endif
                </ul>

	          @endif
            </div> <!-- div.col-md-4 -->
          </div><!-- /row -->
        </div><!-- /.tab-pane asset details -->

        <div class="tab-pane fade" id="assets">
          <div class="row">
            <div class="col-md-12">


            </div><!-- /col -->
          </div> <!-- row -->
        </div> <!-- /.tab-pane software -->


        <div class="tab-pane fade" id="maintenances">
          <div class="row">
            <div class="col-md-12">
                @can('update', \App\Models\Asset::class)
                <div id="maintenance-toolbar">
                  <a href="{{ route('maintenances.create', ['asset_id' => $sale->id]) }}" class="btn btn-primary">Add Maintenance</a>
                </div>
                @endcan

              <!-- Asset Maintenance table -->
                <table
                        data-columns="{{ \App\Presenters\AssetMaintenancesPresenter::dataTableLayout() }}"
                        class="table table-striped snipe-table"
                        id="assetMaintenancesTable"
                        data-pagination="true"
                        data-id-table="assetMaintenancesTable"
                        data-search="true"
                        data-side-pagination="server"
                        data-toolbar="#maintenance-toolbar"
                        data-show-columns="true"
                        data-show-refresh="true"
                        data-show-export="true"
                        data-export-options='{
                           "fileName": "export-{{ $sale->asset_tag }}-maintenances",
                           "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                         }'
                        data-url="{{ route('api.maintenances.index', array('asset_id' => $sale->id)) }}"
                        data-cookie-id-table="assetMaintenancesTable">
                </table>
            </div> <!-- /.col-md-12 -->
          </div> <!-- /.row -->
        </div> <!-- /.tab-pane maintenances -->

        <div class="tab-pane fade" id="history">
          <!-- checked out assets table -->
          <div class="row">
            <div class="col-md-12">
              <table
                      class="table table-striped snipe-table"
                      id="assetHistory"
                      data-pagination="true"
                      data-id-table="assetHistory"
                      data-search="true"
                      data-side-pagination="server"
                      data-show-columns="true"
                      data-show-refresh="true"
                      data-sort-order="desc"
                      data-sort-name="created_at"
                      data-show-export="true"
                      data-export-options='{
                         "fileName": "export-asset-{{  $sale->id }}-history",
                         "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                       }'

                      data-url="{{ route('api.activity.index', ['item_id' => $sale->id, 'item_type' => 'asset']) }}"
                      data-cookie-id-table="assetHistory">
                <thead>
                <tr>
                  <th data-field="icon" data-visible="true" style="width: 40px;" class="hidden-xs" data-formatter="iconFormatter"><span class="sr-only">Icon</span></th>
                  <th class="col-sm-2" data-visible="true" data-field="created_at" data-formatter="dateDisplayFormatter">{{ trans('general.date') }}</th>
                  <th class="col-sm-1" data-visible="true" data-field="admin" data-formatter="usersLinkObjFormatter">{{ trans('general.admin') }}</th>
                  <th class="col-sm-1" data-visible="true" data-field="action_type">{{ trans('general.action') }}</th>
                  <th class="col-sm-2" data-visible="true" data-field="item" data-formatter="polymorphicItemFormatter">{{ trans('general.item') }}</th>
                  <th class="col-sm-2" data-visible="true" data-field="target" data-formatter="polymorphicItemFormatter">{{ trans('general.target') }}</th>
                  <th class="col-sm-2" data-field="note">{{ trans('general.notes') }}</th>
                  @if  ($snipeSettings->require_accept_signature=='1')
                    <th class="col-md-3" data-field="signature_file" data-visible="false"  data-formatter="imageFormatter">{{ trans('general.signature') }}</th>
                  @endif
                  <th class="col-md-3" data-visible="false" data-field="file" data-visible="false"  data-formatter="fileUploadFormatter">{{ trans('general.download') }}</th>
                  <th class="col-sm-2" data-field="log_meta" data-visible="true" data-formatter="changeLogFormatter">Изменения</th>
                  <th class="col-sm-2" data-field="photos" data-visible="true" data-formatter="photosFormatter">Фото</th>
                </tr>
                </thead>
              </table>

            </div>
          </div> <!-- /.row -->
        </div> <!-- /.tab-pane history -->
      </div> <!-- /. tab-content -->
    </div> <!-- /.nav-tabs-custom -->
  </div> <!-- /. col-md-12 -->
</div> <!-- /. row -->

@can('update', \App\Models\Asset::class)
  @include ('modals.upload-file', ['item_type' => 'asset', 'item_id' => $sale->id])
@endcan

@stop

@section('moar_scripts')
  @include ('partials.bootstrap-table')
  <script src="{{ url(asset('js/bootstrap-notify/bootstrap-notify.js')) }}" nonce="{{ csrf_token() }}"></script>
  <script>
    $(function() {

      $('#print_tag').click(function() {
        console.log("test");
        var dataToSend = {
          text: "{{ $sale->asset_tag }}"
        };
        // $.ajax('http://localhost:8001/termal_print', {
        //   success: function (data, textStatus, xhr) {
        //     console.log(xhr.status);
        //     if (xhr.status === 200) {
        //       console.log(data);
        //     } else {
        //       console.log(data);
        //     }
        //   },
        //   error: function () {
        //     console.log("error");
        //   }
        // });
        $.ajax({
          type: "POST",
          url: "http://localhost:8001/termal_print",
          // dataType: 'json',
          // contentType: 'application/json',
          data: JSON.stringify(dataToSend),
          // crossDomain: true,
          headers: {
            'Access-Control-Allow-Origin': '*',
          },
          success: function(data, textStatus, xhr){
            if (xhr.status == 200){
              $.notify({
                // options
                title: "Успешно",
                // message: 'Ошибка'
              },{
                // settings
                type: 'info',
                placement: {
                  from: "bottom",
                  align: "right"
                },
              });
            }else{
              $.notify({
                // options
                title: "Ошибка",
                // message: 'Ошибка'
              },{
                // settings
                type: 'danger',
                placement: {
                  from: "bottom",
                  align: "right"
                },
              });
            }
          },
          error: function(xhr, textStatus){
            $.notify({
              // options
              title: "Ошибка",
              message: textStatus
            },{
              // settings
              type: 'danger',
              placement: {
                from: "bottom",
                align: "right"
              },
            });
          }
        });

      });
      $('#assetHistory').on('post-body.bs.table', function (e, data) {
        // console.log("assetHistory");
        // $('.aniimated-thumbnials').lightGallery({
        //   // thumbnail:true,
        // });
        lightbox.option({
          'resizeDuration': 200,
          'wrapAround': true
        })
      });
    });
  </script>

@stop
