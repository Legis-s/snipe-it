@extends('layouts/default')

{{-- Page title --}}
@section('title')
  {{ $consumable->name }}
  {{ trans('general.consumable') }} -
  ({{ trans('general.remaining_var', ['count' => $consumable->numRemaining()])  }})
  @parent
@endsection

@section('header_right')
    <i class="fa-regular fa-2x fa-square-caret-right pull-right" id="expand-info-panel-button" data-tooltip="true" title="{{ trans('button.show_hide_info') }}"></i>
@endsection

{{-- Page content --}}
@section('content')

    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-tabs>
                <x-slot:tabnav>

                    <x-tabs.nav-item
                            name="assigned"
                            class="active"
                            icon_type="checkedout"
                            label="{{ trans('general.assigned') }}"
                            count="{{ $consumable->numCheckedOut() }}"
                    />

                    <x-tabs.files-tab count="{{ $consumable->uploads()->count() }}" />

                    <x-tabs.history-tab model="\App\Models\Consumable::class"/>

                    @can('update', $consumable)
                        <x-tabs.nav-item-upload />
                    @endcan

                </x-slot:tabnav>

                <x-slot:tabpanes>

                    <x-tabs.pane name="assigned" class="in active">

                        <x-slot:content>
                            <x-table
                                    :presenter="\App\Presenters\ConsumablePresenter::checkedOut()"
                                    :api_url="route('api.consumables.show.users', $consumable->id)"
                            />
                        </x-slot:content>

                    </x-tabs.pane>

                  <div class="row row-new-striped">
                    <!-- name -->
                    <div class="col-md-3 col-sm-2">
                      {{ trans('admin/users/table.name') }}
                    </div>
                    <div class="col-md-9 col-sm-2">
                      {{ $consumable->name }}
                    </div>
                  </div>

                  <!-- company -->
                  @if ($consumable->company)
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.company') }}
                      </div>
                      <div class="col-md-9">
                          {!!  $consumable->company->present()->formattedNameLink !!}
                      </div>
                    </div>
                  @endif

                  <!-- category -->
                  @if ($consumable->category)
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.category') }}
                      </div>
                      <div class="col-md-9">
                          {!!  $consumable->category->present()->formattedNameLink !!}
                      </div>
                    </div>
                  @endif

                  <!-- total -->
                  @if ($consumable->qty)
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('admin/components/general.total') }}
                      </div>
                      <div class="col-md-9">
                        {{ $consumable->qty }}
                      </div>
                    </div>
                  @endif

                  <!-- remaining -->
                  @if ($consumable->numRemaining())
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.remaining') }}
                      </div>
                      <div class="col-md-9">
                        @if ($consumable->numRemaining() < (int) $consumable->min_amt)
                          <i class="fas fa-exclamation-triangle text-orange"
                             aria-hidden="true"
                             data-tooltip="true"
                             data-placement="top"
                             title="{{ trans('admin/consumables/general.inventory_warning', ['min_count' => (int) $consumable->min_amt]) }}">
                          </i>
                        @endif
                        {{ $consumable->numRemaining() }}
                      </div>
                    </div>
                  @endif

                  <!-- min amt -->
                  @if ($consumable->min_amt)
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.min_amt') }}
                      </div>
                      <div class="col-md-9">
                        {{ $consumable->min_amt }}
                      </div>
                    </div>
                  @endif

                  <!-- locationm -->
                  @if ($consumable->location)
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.location') }}
                      </div>
                      <div class="col-md-9">
                          {!!  $consumable->location->present()->formattedNameLink !!}
                      </div>
                    </div>
                  @endif

                  <!-- supplier -->
                  @if ($consumable->supplier)
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.supplier') }}
                      </div>
                      <div class="col-md-9">
                          {!!  $consumable->supplier->present()->formattedNameLink !!}
                      </div>
                    </div>
                  @endif

                  <!-- supplier -->
                  @if ($consumable->manufacturer)
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.manufacturer') }}
                      </div>
                      <div class="col-md-9">
                          {!!  $consumable->manufacturer->present()->formattedNameLink !!}
                      </div>
                    </div>
                  @endif

                  @if ($consumable->purchase_cost)
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.unit_cost') }}
                      </div>
                      <div class="col-md-9">
                        {{ $snipeSettings->default_currency }}
                        {{ Helper::formatCurrencyOutput($consumable->purchase_cost) }}
                      </div>
                    </div>
                  @endif

                  @if ($consumable->purchase_cost)
                        <div class="row">
                            <div class="col-md-3">
                                {{ trans('general.total_cost') }}
                            </div>
                            <div class="col-md-9">
                                {{ $snipeSettings->default_currency }}
                                {{ Helper::formatCurrencyOutput($consumable->totalCostSum()) }}
                            </div>
                        </div>
                  @endif

                  @if ($consumable->order_number)
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.order_number') }}
                      </div>
                      <div class="col-md-9">
                        <span class="js-copy">{{ $consumable->order_number  }}</span>
                        <i class="fa-regular fa-clipboard js-copy-link" data-clipboard-target=".js-copy" aria-hidden="true" data-tooltip="true" data-placement="top" title="{{ trans('general.copy_to_clipboard') }}">
                          <span class="sr-only">{{ trans('general.copy_to_clipboard') }}</span>
                        </i>

                      </div>
                    </div>
                  @endif

                  @if ($consumable->item_no)
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('admin/consumables/general.item_no') }}
                      </div>
                      <div class="col-md-9">

                        <span class="js-copy-item_no">{{ $consumable->item_no  }}</span>
                        <i class="fa-regular fa-clipboard js-copy-link" data-clipboard-target=".js-copy-item_no"
                           aria-hidden="true" data-tooltip="true" data-placement="top"
                           title="{{ trans('general.copy_to_clipboard') }}">
                          <span class="sr-only">{{ trans('general.copy_to_clipboard') }}</span>
                        </i>

                      </div>
                    </div>
                  @endif

                  @if ($consumable->model_number)
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.model_no') }}
                      </div>
                      <div class="col-md-9">

                        <span class="js-copy-model_no">{{ $consumable->model_number  }}</span>
                        <i class="fa-regular fa-clipboard js-copy-link" data-clipboard-target=".js-copy-model_no"
                           aria-hidden="true" data-tooltip="true" data-placement="top"
                           title="{{ trans('general.copy_to_clipboard') }}">
                          <span class="sr-only">{{ trans('general.copy_to_clipboard') }}</span>
                        </i>

                      </div>
                    </div>
                  @endif

                  <!-- purchase date -->
                  @if ($consumable->purchase_date)
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.purchase_date') }}
                      </div>
                      <div class="col-md-9">
                        {{ \App\Helpers\Helper::getFormattedDateObject($consumable->purchase_date, 'datetime', false) }}
                      </div>
                    </div>
                  @endif

                  @if ($consumable->adminuser)
                    <!-- created at -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.created_by') }}
                      </div>
                      <div class="col-md-9">
                        @if ($consumable->adminuser->deleted_at == '')
                          <a href="{{ route('users.show', ['user' => $consumable->adminuser]) }}">{{ $consumable->adminuser->present()->fullName }}</a>
                        @else
                          <del>{{ $consumable->adminuser->present()->fullName }}</del>
                        @endif
                      </div>
                    </div>
                  @endif

                  @if ($consumable->created_at)
                    <!-- created at -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.created_at') }}
                      </div>
                      <div class="col-md-9">
                        {{ \App\Helpers\Helper::getFormattedDateObject($consumable->created_at, 'datetime')['formatted']}}
                      </div>
                    </div>
                  @endif

                  @if ($consumable->updated_at)
                    <!-- created at -->
                    <div class="row">
                      <div class="col-md-3">
                        {{ trans('general.updated_at') }}
                      </div>
                      <div class="col-md-9">
                        {{ \App\Helpers\Helper::getFormattedDateObject($consumable->updated_at, 'datetime')['formatted']}}
                      </div>
                    </div>
                  @endif

                  @if ($consumable->notes)
                    <!-- empty -->
                    <div class="row">

                      <div class="col-md-3">
                        {{ trans('admin/users/table.notes') }}
                      </div>
                      <div class="col-md-9">
                        {!! nl2br(Helper::parseEscapedMarkedownInline($consumable->notes)) !!}
                      </div>

                    </div>
                  @endif
                </div> <!--/end striped container-->
              </div> <!-- end col-md-9 -->
              </div><!-- end info-stack-container -->
            </div> <!--/.row-->
          </div><!-- /.tab-pane -->

          <div class="tab-pane" id="checkedout">

                <table
                        data-columns="{{ \App\Presenters\ConsumableAssignmentPresenter::dataTableLayout() }}"
                        data-cookie-id-table="consumablesCheckedoutTable"
                        data-id-table="consumablesCheckedoutTable"
                        data-search="true"
                        data-side-pagination="server"
                        data-show-footer="true"
                        data-sort-order="asc"
                        data-sort-name="name"
                        id="consumablesCheckedoutTable"
                        class="table table-striped snipe-table"
                        data-url="{{route('api.consumableassignments.index',['consumable_id' => $consumable->id])}}"
                        data-export-options='{
                "fileName": "export-consumables-{{ str_slug($consumable->name) }}-checkedout-{{ date('Y-m-d') }}",
                "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
                }'>
                </table>

          </div><!-- /checkedout -->


          <div class="tab-pane" id="files">

            <div class="row">
              <div class="col-md-12">
                <x-filestable object_type="consumables" :object="$consumable" />
              </div>
            </div>

            </x-tabs>
        </x-page-column>

        <x-page-column class="col-md-3">
            <x-box>
                <x-box.info-panel :infoPanelObj="$consumable" img_path="{{ app('consumables_upload_url') }}">

                    <x-slot:before_list>

                        <x-button.wide-checkout :item="$consumable" :route="route('consumables.checkout.show', $consumable->id)" />
                        <x-button.wide-edit :item="$consumable" :route="route('consumables.edit', $consumable->id)" />
                        <x-button.wide-clone :item="$consumable" :route="route('consumables.clone.create', $consumable->id)" />
                        <x-button.wide-delete :item="$consumable" />

                    </x-slot:before_list>

                </x-box.info-panel>
            </x-box>
        </x-page-column>
    </x-container>

  @can('update', \App\Models\User::class)
    @include ('modals.upload-file', ['item_type' => 'consumable', 'item_id' => $consumable->id])
  @endcan



@stop

@section('moar_scripts')
  @include ('partials.bootstrap-table', ['simple_view' => true])
@endsection