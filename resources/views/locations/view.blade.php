@extends('layouts/default')

{{-- Page title --}}
@section('title')

 {{ trans('general.location') }}:
 {{ $location->name }}
 
@parent
@stop

@section('header_right')
<a href="{{ route('locations.edit', ['location' => $location->id]) }}" class="btn btn-sm btn-primary pull-right">{{ trans('admin/locations/table.update') }} </a>
@stop

{{-- Page content --}}
@section('content')

<div class="row">
  <div class="col-md-9">
      <div class="box box-default">
          <div class="box-header with-border">
              <div class="box-heading">
                  <h2 class="box-title">{{ trans('general.users') }}</h2>
              </div>
          </div>
          <div class="box-body">
              <div class="table table-responsive">
                  <table
                        data-columns="{{ \App\Presenters\UserPresenter::dataTableLayout() }}"
                        data-cookie-id-table="usersTable"
                        data-pagination="true"
                        data-id-table="usersTable"
                        data-search="true"
                        data-side-pagination="server"
                        data-show-columns="true"
                        data-show-export="true"
                        data-show-refresh="true"
                        data-sort-order="asc"
                        id="usersTable"
                        class="table table-striped snipe-table"
                        data-url="{{route('api.users.index', ['location_id' => $location->id])}}"
                        data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-users-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>

                </table>
              </div><!-- /.table-responsive -->
          </div><!-- /.box-body -->
      </div> <!--/.box-->
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
                          data-url="{{route('api.assets.index', ['location_id' => $location->id]) }}"
                          data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-assets-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>
                  </table>
              </div><!-- /.table-responsive -->
          </div><!-- /.box-body -->
      </div> <!--/.box-->
      <div class="box box-default">
          <div class="box-header with-border">
              <div class="box-heading">
                  <h2 class="box-title">{{ trans('general.components') }}</h2>
              </div>
          </div>
          <div class="box-body">
              <div class="table table-responsive">
                  <table
                          data-columns="{{ \App\Presenters\ComponentPresenter::dataTableLayout() }}"
                          data-cookie-id-table="componentsTable"
                          data-pagination="true"
                          data-id-table="componentsTable"
                          data-search="true"
                          data-side-pagination="server"
                          data-show-columns="true"
                          data-show-export="true"
                          data-show-refresh="true"
                          data-sort-order="asc"
                          id="componentsTable"
                          class="table table-striped snipe-table"
                          data-url="{{route('api.components.index', ['location_id' => $location->id])}}"
                          data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-components-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>

                  </table>
              </div><!-- /.table-responsive -->
          </div><!-- /.box-body -->
      </div> <!--/.box-->
      <div class="box box-default">
          <div class="box-header with-border">
              <div class="box-heading">
                  <h2 class="box-title">Инвентаризации</h2>
              </div>
          </div>
          <div class="box-body">
              <div class="table table-responsive">
                  <table
                          data-columns="{{ \App\Presenters\InventoryPresenter::dataTableLayout() }}"
                          data-cookie-id-table="inventoriesTable"
                          data-pagination="true"
                          data-id-table="inventoriesTable"
                          data-search="true"
                          data-side-pagination="server"
                          data-show-columns="true"
                          data-show-export="true"
                          data-show-refresh="true"
                          data-sort-order="asc"
                          id="inventoriesTable"
                          class="table table-striped snipe-table"
                          data-url="{{route('api.inventories.index', ['location_id' => $location->id])}}"
                          data-export-options='{
                              "fileName": "export-locations-{{ str_slug($location->name) }}-components-{{ date('Y-m-d') }}",
                              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                              }'>

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
{{--          <div class="box-body">--}}
{{--              <div class="table table-responsive">--}}
{{--                  <table--}}
{{--                          data-columns="{{ \App\Presenters\ConsumableAssignmentPresenter::dataTableLayout() }}"--}}
{{--                          data-cookie-id-table="inventoriesTable"--}}
{{--                          data-pagination="true"--}}
{{--                          data-id-table="сonsumableAssignmentTable"--}}
{{--                          data-search="true"--}}
{{--                          data-side-pagination="server"--}}
{{--                          data-show-columns="true"--}}
{{--                          data-show-export="true"--}}
{{--                          data-show-refresh="true"--}}
{{--                          data-sort-order="asc"--}}
{{--                          id="сonsumableAssignmentTable"--}}
{{--                          class="table table-striped snipe-table"--}}
{{--                          data-url="{{route('api.inventories.index', ['location_id' => $location->id])}}"--}}
{{--                          >--}}

{{--                  </table>--}}
{{--              </div><!-- /.table-responsive -->--}}
{{--          </div><!-- /.box-body -->--}}
          <div class="box-body">
              <div class="row">
                  <div class="col-md-12">
                      <div class="table table-responsive">

                          <table
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
                                  data-url="{{route('api.consumables.showLocation', $location->id)}}">
                              <thead>
                              <tr>
                                  <th data-searchable="false" data-sortable="false" data-field="name">Выдано на объект</th>
                                  <th data-searchable="false" data-sortable="false" data-field="quantity">Количество</th>
                                  <th data-searchable="false" data-sortable="false" data-field="created_at" data-formatter="dateDisplayFormatter">{{ trans('general.date') }}</th>
                                  <th data-searchable="false" data-sortable="false" data-field="admin">Выдал</th>
                              </tr>
                              </thead>
                          </table>
                      </div>
                  </div> <!-- /.col-md-12-->

              </div>
          </div>
      </div> <!--/.box-->
  </div><!--/.col-md-9-->

  <div class="col-md-3">

    @if ($location->image!='')
      <div class="col-md-12 text-center" style="padding-bottom: 20px;">
        <img src="{{ app('locations_upload_url') }}/{{ $location->image }}" class="img-responsive img-thumbnail" alt="{{ $location->name }}">
      </div>
    @endif
      <div class="col-md-12">
        <ul class="list-unstyled" style="line-height: 25px; padding-bottom: 20px;">
          @if ($location->address!='')
            <li>{{ $location->address }}</li>
           @endif
            @if ($location->address2!='')
              <li>{{ $location->address2 }}</li>
            @endif
            @if (($location->city!='') || ($location->state!='') || ($location->zip!=''))
              <li>{{ $location->city }} {{ $location->state }} {{ $location->zip }}</li>
            @endif
            @if (($location->manager))
              <li>{{ trans('admin/users/table.manager') }}: {!! $location->manager->present()->nameUrl() !!}</li>
            @endif
            @if (($location->parent))
              <li>{{ trans('admin/locations/table.parent') }}: {!! $location->parent->present()->nameUrl() !!}</li>
            @endif
              @if (($location->bitrix_id))
                  <li><a href="https://bitrix.legis-s.ru/crm/object/details/{{ $location->bitrix_id}}/"   target="_blank" >Открыть в Bitrix</a></li>
              @endif
        </ul>

        @if (($location->state!='') && ($location->country!='') && (config('services.google.maps_api_key')))
          <div class="col-md-12 text-center">
            <img src="https://maps.googleapis.com/maps/api/staticmap?center={{ urlencode($location->city.','.$location->city.' '.$location->state.' '.$location->country.' '.$location->zip) }}&size=500x300&maptype=roadmap&key={{ config('services.google.maps_api_key') }}" class="img-responsive img-thumbnail" alt="Map">
          </div>
        @endif


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
