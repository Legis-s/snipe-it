@extends('layouts/default')

@php
    $parseCoordinates = static function (mixed $value): ?array {
        $parts = array_map('trim', explode(',', (string) $value));
        if (count($parts) !== 2 || !is_numeric($parts[0]) || !is_numeric($parts[1])) {
            return null;
        }
        $parts = array_map('floatval', $parts);
        return abs($parts[0]) <= 90 && abs($parts[1]) <= 180 && $parts !== [0.0, 0.0] ? $parts : null;
    };
    $deviceCoordinates = $parseCoordinates($device->coordinates);
    $locationCoordinates = $parseCoordinates($asset?->location?->coordinates);
    $battery = is_numeric($device->batteryLevel) ? max(0, min(100, (int) $device->batteryLevel)) : null;
    $mdmColor = ['green' => 'success', 'yellow' => 'warning', 'red' => 'danger'][$device->statusCode] ?? 'default';
@endphp

{{-- Page title --}}
@section('title')
    {{ trans('general.device_summary.title') }} - {{ $device->number }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <style>
        .device-page { padding: 20px; background: var(--main-box-bg, #fff); }
        .device-heading { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; margin-bottom: 20px; }
        .device-heading h2 { font-size: 22px; margin: 0; overflow-wrap: anywhere; }
        .device-overview { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 28px; padding-bottom: 24px; }
        .device-facts { display: grid; grid-template-columns: minmax(100px, 140px) minmax(0, 1fr); gap: 12px 16px; margin: 0; }
        .device-facts dt { font-size: 12px; font-weight: 400; opacity: .75; }
        .device-facts dd { margin: 0; overflow-wrap: anywhere; }
        .device-page .label { white-space: normal; line-height: 1.4; }
        .device-page #map { width: 100%; height: 340px; }
        .device-map-caption { font-size: 12px; margin-top: 8px; opacity: .75; overflow-wrap: anywhere; }
        .device-detail { clear: both; }
        .device-page > .nav-tabs-custom { box-shadow: none; margin-bottom: 0; }
        .device-page > .nav-tabs-custom > .tab-content { padding: 20px 0 0; }
        .device-detail .box { margin: 12px 0 0; border: 0; box-shadow: none; }
        .device-detail > .col-md-12 { float: none; width: 100%; padding: 0; }
        .device-detail .box-body .row { display: grid; grid-template-columns: minmax(110px, 160px) minmax(0, 1fr); gap: 12px; margin: 0; padding: 9px 0; border-bottom: 1px solid #eee; }
        .device-detail .box-body .row > div { width: auto; float: none; padding: 0; min-width: 0; overflow-wrap: anywhere; }
        .device-detail .box-body .row strong { font-weight: 400; opacity: .75; font-size: 12px; }
        .device-asset-details .box-body { display: grid; grid-template-columns: minmax(0, 2fr) minmax(0, 1fr); gap: 24px; }
        /* Bootstrap clearfix pseudo-elements must not occupy grid cells. */
        .device-detail .box-body .row::before,
        .device-detail .box-body .row::after,
        .device-asset-details .box-body::before,
        .device-asset-details .box-body::after { content: none; display: none; }
        .device-asset-details .box-body > .col-md-6 { width: 100%; float: none; padding: 0; }
        .device-detail .assetimg { max-height: 320px; object-fit: contain; margin: 0 auto; }
        @media (max-width: 767px) {
            .device-page { padding: 12px; }
            .device-overview, .device-asset-details .box-body { grid-template-columns: minmax(0, 1fr); }
            .device-detail .box-body .row { grid-template-columns: minmax(0, 1fr); gap: 4px; }
            .device-page #map { height: 260px; }
        }
    </style>
    <x-container>
        <div class="device-page">
            <x-tabs>
                <x-slot:tabnav>
                    <x-tabs.nav-item name="details" icon="fas fa-tablet-alt" label="{{ trans('general.device_summary.title') }}" class="active" />
                    @isset($asset)
                        <x-tabs.nav-item name="asset" icon="fas fa-barcode" label="{{ trans('general.asset') }}" />
                    @endisset
                    @isset($asset_sim)
                        <x-tabs.nav-item name="sim" icon="fas fa-sim-card" label="{{ trans('general.device_summary.sim') }}" />
                    @endisset
                </x-slot:tabnav>
                <x-slot:tabpanes>
                    <x-tabs.pane name="details" class="active in">
            <header class="device-heading">
                <h2>{{ $device->model ?: trans('general.device_summary.title') }} <small>#{{ $device->number }}</small></h2>
                <span class="label label-{{ $mdmColor }}">MDM: {{ $device->statusCode ?: trans('general.device_summary.unknown') }}</span>
                @if ($battery !== null)
                    <span><i class="fas fa-battery-half" aria-hidden="true"></i> {{ $battery }}%</span>
                @endif
            </header>
            <section class="device-overview" aria-label="{{ trans('general.information') }}">
                <dl class="device-facts">
                    @foreach ([
                        'IMEI' => $device->imei ?: $device->info_imei,
                        trans('admin/hardware/form.serial') => $device->serial,
                        'Android' => $device->androidVersion,
                        trans('general.device_summary.launcher') => $device->launcherVersion,
                        trans('general.device_summary.application') => $device->biometrikaVersion,
                        'IP' => $device->publicIp,
                        'AnyDesk' => $device->anyDesk,
                        trans('general.updated_at') => Helper::getFormattedDateObject($device->lastUpdate, 'datetime', false),
                        trans('general.notes') => $device->description,
                    ] as $label => $value)
                        @if ($value !== null && $value !== '')
                            <dt>{{ $label }}</dt><dd>{{ $value }}</dd>
                        @endif
                    @endforeach
                    @if ($asset)
                        <dt>{{ trans('general.asset') }}</dt><dd><a href="{{ route('hardware.show', $asset->id) }}">{{ $asset->asset_tag }}</a></dd>
                        @if ($asset->location)
                            <dt>{{ trans('general.location') }}</dt><dd><a href="{{ route('locations.show', $asset->location->id) }}">{{ $asset->location->name }}</a></dd>
                        @endif
                    @endif
                    @if ($asset_sim)
                        <dt>{{ trans('general.device_summary.sim') }}</dt><dd><a href="{{ route('hardware.show', $asset_sim->id) }}">{{ $asset_sim->asset_tag }}</a></dd>
                    @endif
                </dl>
                <div>
                    @if ($deviceCoordinates)
                        <div id="map"></div>
                        @if ($device->locationUpdate)<div class="device-map-caption">{{ trans('general.device_summary.location_updated') }}: {{ $device->locationUpdate }}</div>@endif
                    @else
                        <p class="text-muted">{{ trans('general.device_summary.no_coordinates') }}</p>
                    @endif
                </div>
            </section>
                    </x-tabs.pane>
        @isset($asset)
            <x-tabs.pane name="asset">
            <div class="device-detail device-asset-details">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <div class="box-heading">
                            <h2 class="box-title">Актив <a href="{{ route('hardware.show', $asset->id) }}">{{ $asset->asset_tag }}</a></h2>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="col-md-6">
                            @if ($asset->deleted_at!='')
                                <div class="row">
                                    <div class="col-md-2">
                                        <span class="text-danger"><strong>{{ trans('general.deleted') }}</strong></span>
                                    </div>
                                    <div class="col-md-6">
                                        {{ \App\Helpers\Helper::getFormattedDateObject($asset->deleted_at, 'date', false) }}

                                    </div>
                                </div>
                            @endif



                            @if ($asset->assetstatus)

                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>{{ trans('general.status') }}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        @if (($asset->assignedTo) && ($asset->deleted_at==''))
                                            <i class="fas fa-circle text-blue"></i>
                                            {{ $asset->assetstatus->name }}
                                            <label class="label label-default">{{ trans('general.deployed') }}</label>

                                            <i class="fas fa-long-arrow-alt-right" aria-hidden="true"></i>
                                            {!!  $asset->assignedTo->present()->glyph()  !!}
                                            {!!  $asset->assignedTo->present()->nameUrl() !!}
                                        @else
                                            @if (($asset->assetstatus) && ($asset->assetstatus->deployable=='1'))
                                                <i class="fas fa-circle text-green"></i>
                                            @elseif (($asset->assetstatus) && ($asset->assetstatus->pending=='1'))
                                                <i class="fas fa-circle text-orange"></i>
                                            @elseif (($asset->assetstatus) && ($asset->assetstatus->archived=='1'))
                                                <i class="fas fa-times text-red"></i>
                                            @endif
                                            <a href="{{ route('statuslabels.show', $asset->assetstatus->id) }}">
                                                {{ $asset->assetstatus->name }}</a>
                                            <label class="label label-default">{{ $asset->present()->statusMeta }}</label>

                                        @endif
                                        @if ($asset->contract_id && $asset->assigned_type != 'App\Models\Contract')
                                            (по договору <a
                                                    href='/contracts/{{$asset->contract_id}}'>{{\App\Models\Contract::find($asset->contract_id)->name}}</a>
                                            )
                                        @endif

                                        @if($asset->assigned_type == 'App\Models\Asset' && $asset->assignedTo && $asset->assignedTo->location)
                                            (местоположение <a
                                                    href='/locations/{{$asset->assignedTo->location->id}}'> {{ $asset->assignedTo->location->name }} </a>
                                            )
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if ($asset->company)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>{{ trans('general.company') }}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ url('/companies/' . $asset->company->id) }}">{{ $asset->company->name }}</a>
                                    </div>
                                </div>
                            @endif

                            @if ($asset->name)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>{{ trans('admin/hardware/form.name') }}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        {{ $asset->name }}
                                    </div>
                                </div>
                            @endif

                            @if ($asset->serial)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>{{ trans('admin/hardware/form.serial') }}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        {{ $asset->serial  }}
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
                                        {{ \App\Helpers\Helper::getFormattedDateObject($audit_log->created_at, 'date', false) }}
                                        @if ($audit_log->user)
                                            (by {{ link_to_route('users.show', $audit_log->user->display_name, [$audit_log->user->id]) }}
                                            )
                                        @endif

                                    </div>
                                </div>
                            @endif

                            @if ($asset->next_audit_date)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('general.next_audit_date') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        {{ Helper::getFormattedDateObject($asset->next_audit_date, 'date', false) }}
                                    </div>
                                </div>
                            @endif

                            @if (($asset->model) && ($asset->model->manufacturer))
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('admin/hardware/form.manufacturer') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="list-unstyled">
                                            @can('view', \App\Models\Manufacturer::class)

                                                <li>
                                                    <a href="{{ route('manufacturers.show', $asset->model->manufacturer->id) }}">
                                                        {{ $asset->model->manufacturer->name }}
                                                    </a>
                                                </li>

                                            @else
                                                <li> {{ $asset->model->manufacturer->name }}</li>
                                            @endcan

                                            @if (($asset->model) && ($asset->model->manufacturer->url))
                                                <li>
                                                    <i class="fas fa-globe-americas" aria-hidden="true"></i>
                                                    <a href="{{ $asset->model->manufacturer->url }}">
                                                        {{ $asset->model->manufacturer->url }}
                                                    </a>
                                                </li>
                                            @endif

                                            @if (($asset->model) && ($asset->model->manufacturer->support_url))
                                                <li>
                                                    <i class="far fa-life-ring" aria-hidden="true"></i>
                                                    <a href="{{ $asset->model->manufacturer->support_url }}">
                                                        {{ $asset->model->manufacturer->support_url }}
                                                    </a>
                                                </li>
                                            @endif

                                            @if (($asset->model) && ($asset->model->manufacturer->support_phone))
                                                <li>
                                                    <i class="fas fa-phone" aria-hidden="true"></i>
                                                    <a href="tel:{{ $asset->model->manufacturer->support_phone }}">
                                                        {{ $asset->model->manufacturer->support_phone }}
                                                    </a>
                                                </li>
                                            @endif

                                            @if (($asset->model) && ($asset->model->manufacturer->support_email))
                                                <li>
                                                    <i class="far fa-envelope" aria-hidden="true"></i>
                                                    <a href="mailto:{{ $asset->model->manufacturer->support_email }}">
                                                        {{ $asset->model->manufacturer->support_email }}
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
                                    @if (($asset->model) && ($asset->model->category))

                                        @can('view', \App\Models\Category::class)

                                            <a href="{{ route('categories.show', $asset->model->category->id) }}">
                                                {{ $asset->model->category->name }}
                                            </a>
                                        @else
                                            {{ $asset->model->category->name }}
                                        @endcan
                                    @else
                                        Invalid category
                                    @endif
                                </div>
                            </div>

                            @if ($asset->model)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('admin/hardware/form.model') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        @if ($asset->model)

                                            @can('view', \App\Models\AssetModel::class)
                                                <a href="{{ route('models.show', $asset->model->id) }}">
                                                    {{ $asset->model->name }}
                                                </a>
                                            @else
                                                {{ $asset->model->name }}
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
                                    {{ ($asset->model) ? $asset->model->model_number : ''}}
                                </div>
                            </div>

                            @if (($asset->model) && ($asset->model->fieldset))
                                @foreach($asset->model->fieldset->fields as $field)
                                    <div class="row">
                                        <div class="col-md-2">
                                            <strong>
                                                {{ $field->name }}
                                            </strong>
                                        </div>
                                        <div class="col-md-6">
                                            @if ($field->field_encrypted=='1')
                                                <i class="fas fa-lock" data-toggle="tooltip" data-placement="top"
                                                   title="{{ trans('admin/custom_fields/general.value_encrypted') }}"></i>
                                            @endif

                                            @if ($field->isFieldDecryptable($asset->{$field->db_column_name()} ))
                                                @can('superuser')
                                                    @if (($field->format=='URL') && ($asset->{$field->db_column_name()}!=''))
                                                        <a href="{{ Helper::gracefulDecrypt($field, $asset->{$field->db_column_name()}) }}"
                                                           target="_new">{{ Helper::gracefulDecrypt($field, $asset->{$field->db_column_name()}) }}</a>
                                                    @elseif (($field->format=='DATE') && ($asset->{$field->db_column_name()}!=''))
                                                        {{ \App\Helpers\Helper::gracefulDecrypt($field, \App\Helpers\Helper::getFormattedDateObject($asset->{$field->db_column_name()}, 'date', false)) }}
                                                    @else
                                                        {{ Helper::gracefulDecrypt($field, $asset->{$field->db_column_name()}) }}
                                                    @endif
                                                @else
                                                    {{ strtoupper(trans('admin/custom_fields/general.encrypted')) }}
                                                @endcan

                                            @else
                                                @if (($field->format=='BOOLEAN') && ($asset->{$field->db_column_name()}!=''))
                                                    {!! ($asset->{$field->db_column_name()} == 1) ? "<span class='fas fa-check-circle' style='color:green' />" : "<span class='fas fa-times-circle' style='color:red' />" !!}
                                                @elseif (($field->format=='URL') && ($asset->{$field->db_column_name()}!=''))
                                                    <a href="{{ $asset->{$field->db_column_name()} }}"
                                                       target="_new">{{ $asset->{$field->db_column_name()} }}</a>
                                                @elseif (($field->format=='DATE') && ($asset->{$field->db_column_name()}!=''))
                                                    {{ \App\Helpers\Helper::getFormattedDateObject($asset->{$field->db_column_name()}, 'date', false) }}
                                                @else
                                                    {!! nl2br(e($asset->{$field->db_column_name()})) !!}
                                                @endif

                                            @endif

                                            @if ($asset->{$field->db_column_name()}=='')
                                                &nbsp;
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            @endif

                            @if ($asset->purchase)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            Закупка
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ route('purchases.show', $asset->purchase->id) }}">
                                            {{ $asset->purchase->invoice_number }}
                                        </a>
                                    </div>
                                </div>
                            @endif
                            @if ($asset->purchase_date)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('admin/hardware/form.date') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        {{ Helper::getFormattedDateObject($asset->purchase_date, 'date', false) }}
                                    </div>
                                </div>
                            @endif

                            @if ($asset->purchase_cost)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('admin/hardware/form.cost') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        @if (($asset->id) && ($asset->location))
                                            {{ $asset->location->currency }}
                                        @elseif (($asset->id) && ($asset->location))
                                            {{ $asset->location->currency }}
                                        @else
                                            {{ $snipeSettings->default_currency }}
                                        @endif
                                        {{ Helper::formatCurrencyOutput($asset->purchase_cost)}}

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
                                    @if ($asset->quality == 5)
                                        <i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star"
                                                                                         aria-hidden="true"></i>
                                        <i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star"
                                                                                         aria-hidden="true"></i>
                                        <i class="fas fa-star" aria-hidden="true"></i>
                                    @elseif  ($asset->quality == 4)
                                        <i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star"
                                                                                         aria-hidden="true"></i>
                                        <i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star"
                                                                                         aria-hidden="true"></i>
                                        <i class="fas fa-star-o" aria-hidden="true"></i>
                                    @elseif  ($asset->quality == 3)
                                        <i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star"
                                                                                         aria-hidden="true"></i>
                                        <i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star-o"
                                                                                         aria-hidden="true"></i>
                                        <i class="fas fa-star-o" aria-hidden="true"></i>
                                    @elseif  ($asset->quality == 2)
                                        <i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star"
                                                                                         aria-hidden="true"></i>
                                        <i class="fas fa-star-o" aria-hidden="true"></i><i class="fas fa-star-o"
                                                                                           aria-hidden="true"></i>
                                        <i class="fas fa-star-o" aria-hidden="true"></i>
                                    @elseif  ($asset->quality == 1)
                                        <i class="fas fa-star" aria-hidden="true"></i><i class="fas fa-star-o"
                                                                                         aria-hidden="true"></i>
                                        <i class="fas fa-star-o" aria-hidden="true"></i><i class="fas fa-star-o"
                                                                                           aria-hidden="true"></i>
                                        <i class="fas fa-star-o" aria-hidden="true"></i>
                                    @else
                                        <i class="fas fa-star-o" aria-hidden="true"></i><i class="fas fa-star-o"
                                                                                           aria-hidden="true"></i>
                                        <i class="fas fa-star-o" aria-hidden="true"></i><i class="fas fa-star-o"
                                                                                           aria-hidden="true"></i>
                                        <i class="fas fa-star-o" aria-hidden="true"></i>
                                    @endif

                                </div>
                            </div>
                            @if ($asset->depreciable_cost)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            Остаточная стоимость
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        @if (($asset->id) && ($asset->location))
                                            {{ $asset->location->currency }}
                                        @elseif (($asset->id) && ($asset->location))
                                            {{ $asset->location->currency }}
                                        @else
                                            {{ $snipeSettings->default_currency }}
                                        @endif
                                        {{ Helper::formatCurrencyOutput($asset->depreciable_cost)}}
                                    </div>
                                </div>
                            @endif
                            @if (($asset->model) && ($asset->depreciation) && ($asset->purchase_date))
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('admin/hardware/table.current_value') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        @if (($asset->id) && ($asset->location))
                                            {{ $asset->location->currency }}
                                        @elseif (($asset->id) && ($asset->location))
                                            {{ $asset->location->currency }}
                                        @else
                                            {{ $snipeSettings->default_currency }}
                                        @endif
                                        {{ Helper::formatCurrencyOutput($asset->getDepreciatedValue() )}}
                                    </div>
                                </div>
                            @endif



                            @if ($asset->order_number)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('general.order_number') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ route('hardware.index', ['order_number' => $asset->order_number]) }}">#{{ $asset->order_number }}</a>
                                    </div>
                                </div>
                            @endif

                            @if ($asset->supplier)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('general.supplier') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        @can ('superuser')
                                            <a href="{{ route('suppliers.show', $asset->supplier_id) }}">
                                                {{ $asset->supplier->name }}
                                            </a>
                                        @else
                                            {{ $asset->supplier->name }}
                                        @endcan
                                    </div>
                                </div>
                            @endif


                            @if ($asset->warranty_months)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('admin/hardware/form.warranty') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        {{ $asset->warranty_months }}
                                        {{ trans('admin/hardware/form.months') }}

                                        @if (($asset->serial && $asset->model->manufacturer) && $asset->model->manufacturer->name == 'Apple')
                                            <a href="https://checkcoverage.apple.com/us/{{ \App\Models\Setting::getSettings()->locale  }}/?sn={{ $asset->serial }}"
                                               target="_blank">
                                                <i class="fa-brands fa-apple" aria-hidden="true"><span class="sr-only">Applecare Statys Lookup</span></i>
                                            </a>
                                        @endif
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('admin/hardware/form.warranty_expires') }}
                                            @if ($asset->purchase_date)
                                                {!! $asset->present()->warranty_expires() < date("Y-m-d") ? '<i class="fas fa-exclamation-triangle text-orange" aria-hidden="true"></i>' : '' !!}
                                            @endif

                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        @if ($asset->purchase_date)
                                            {{ Helper::getFormattedDateObject($asset->present()->warranty_expires(), 'date', false) }}
                                            -
                                            {{ Carbon::parse($asset->present()->warranty_expires())->diffForHumans(['parts' => 2]) }}
                                        @else
                                            {{ trans('general.na_no_purchase_date') }}
                                        @endif
                                    </div>
                                </div>

                            @endif

                            @if (($asset->model) && ($asset->depreciation))
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('general.depreciation') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        {{ $asset->depreciation->name }}
                                        ({{ $asset->depreciation->months }}
                                        {{ trans('admin/hardware/form.months') }})
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('admin/hardware/form.fully_depreciated') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        @if ($asset->purchase_date)
                                            {{ Helper::getFormattedDateObject($asset->depreciated_date()->format('Y-m-d'), 'date', false) }}
                                            -
                                            {{ Carbon::parse($asset->depreciated_date())->diffForHumans(['parts' => 2]) }}
                                        @else
                                            {{ trans('general.na_no_purchase_date') }}
                                        @endif

                                    </div>
                                </div>
                            @endif

                            @if (($asset->model) && ($asset->model->eol))
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('admin/hardware/form.eol_rate') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        {{ $asset->model->eol }}
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
                                        @if ($asset->purchase_date)
                                            {{ Helper::getFormattedDateObject($asset->present()->eol_date(), 'date', false) }}
                                            -
                                            {{ Carbon::parse($asset->present()->eol_date())->diffForHumans(['parts' => 2]) }}
                                        @else
                                            {{ trans('general.na_no_purchase_date') }}
                                        @endif
                                    </div>
                                </div>
                            @endif

                            @if ($asset->expected_checkin!='')
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('admin/hardware/form.expected_checkin') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        {{ Helper::getFormattedDateObject($asset->expected_checkin, 'date', false) }}
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
                                    {!! nl2br(e($asset->notes)) !!}
                                </div>
                            </div>

                            @if ($asset->location)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('general.location') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        @can('superuser')
                                            <a href="{{ route('locations.show', ['location' => $asset->location->id]) }}">
                                                {{ $asset->location->name }}
                                            </a>
                                        @else
                                            {{ $asset->location->name }}
                                        @endcan
                                    </div>
                                </div>
                            @endif

                            @if ($asset->created_at!='')
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('general.created_at') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        {{ Helper::getFormattedDateObject($asset->created_at, 'datetime', false) }}
                                    </div>
                                </div>
                            @endif

                            @if ($asset->updated_at!='')
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('general.updated_at') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        {{ Helper::getFormattedDateObject($asset->updated_at, 'datetime', false) }}
                                    </div>
                                </div>
                            @endif
                            @if ($asset->last_checkout!='')
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('admin/hardware/table.checkout_date') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        {{ Helper::getFormattedDateObject($asset->last_checkout, 'datetime', false) }}
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
                                    {{ ($asset->checkouts) ? (int) $asset->checkouts->count() : '0' }}
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-2">
                                    <strong>
                                        {{ trans('general.checkins_count') }}
                                    </strong>
                                </div>
                                <div class="col-md-6">
                                    {{ ($asset->checkins) ? (int) $asset->checkins->count() : '0' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            @if (($asset->image) || (($asset->model) && ($asset->model->image!='')))


                                <div class="text-center col-md-12" style="padding-bottom: 15px;">
                                    <a href="{{ ($asset->getImageUrl()) ? $asset->getImageUrl() : null }}" data-toggle="lightbox">
                                        <img src="{{ ($asset->getImageUrl()) ? $asset->getImageUrl() : null }}" class="assetimg img-responsive" alt="{{ $asset->getDisplayNameAttribute() }}">
                                    </a>
                                </div>
                            @endif

                            @if ($asset->inventory_items->count() > 0)

                                @foreach ($asset->inventory_items->reverse() as $inventory_item)
                                    @if ($inventory_item->photo)
                                        <div class="text-center col-md-12" style="padding-bottom: 15px;">
                                            <a href="{{$inventory_item->photo_url() }}" data-toggle="lightbox">
                                                <img src="{{ $inventory_item->photo_url()}}" class="assetimg img-responsive" alt="{{ $asset->asset_tag }}" onerror="this.parentElement.hidden = true;">
                                            </a>
                                        </div>
                                        @break
                                    @endif
                                @endforeach
                            @endif


                        </div>
                    </div>
                </div>
            </div>
            </div>
            </x-tabs.pane>
        @endisset
        @isset($asset_sim)
            <x-tabs.pane name="sim">
            <div class="device-detail">
                <div class="col-md-12">
                    <div class="box box-default">
                        <div class="box-header with-border">
                            <div class="box-heading">
                                <h2 class="box-title">Сим-карта  <a href="{{ route('hardware.show', $asset_sim->id) }}">{{ $asset_sim->asset_tag }}</a></h2>
                            </div>
                        </div>
                        <div class="box-body">

                            @if ($asset_sim->company)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>{{ trans('general.company') }}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ url('/companies/' . $asset_sim->company->id) }}">{{ $asset_sim->company->name }}</a>
                                    </div>
                                </div>
                            @endif

                            @if ($asset_sim->name)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>{{ trans('admin/hardware/form.name') }}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        {{ $asset_sim->name }}
                                    </div>
                                </div>
                            @endif

                            @if ($asset_sim->serial)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>{{ trans('admin/hardware/form.serial') }}</strong>
                                    </div>
                                    <div class="col-md-6">
                                        {{ $asset_sim->serial  }}
                                    </div>
                                </div>
                            @endif


                            @if (($asset_sim->model) && ($asset_sim->model->manufacturer))
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('admin/hardware/form.manufacturer') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        <ul class="list-unstyled">
                                            @can('view', \App\Models\Manufacturer::class)

                                                <li>
                                                    <a href="{{ route('manufacturers.show', $asset_sim->model->manufacturer->id) }}">
                                                        {{ $asset_sim->model->manufacturer->name }}
                                                    </a>
                                                </li>

                                            @else
                                                <li> {{ $asset_sim->model->manufacturer->name }}</li>
                                            @endcan

                                            @if (($asset_sim->model) && ($asset_sim->model->manufacturer->url))
                                                <li>
                                                    <i class="fas fa-globe-americas" aria-hidden="true"></i>
                                                    <a href="{{ $asset_sim->model->manufacturer->url }}">
                                                        {{ $asset_sim->model->manufacturer->url }}
                                                    </a>
                                                </li>
                                            @endif

                                            @if (($asset_sim->model) && ($asset_sim->model->manufacturer->support_url))
                                                <li>
                                                    <i class="far fa-life-ring" aria-hidden="true"></i>
                                                    <a href="{{ $asset_sim->model->manufacturer->support_url }}">
                                                        {{ $asset_sim->model->manufacturer->support_url }}
                                                    </a>
                                                </li>
                                            @endif

                                            @if (($asset_sim->model) && ($asset_sim->model->manufacturer->support_phone))
                                                <li>
                                                    <i class="fas fa-phone" aria-hidden="true"></i>
                                                    <a href="tel:{{ $asset_sim->model->manufacturer->support_phone }}">
                                                        {{ $asset_sim->model->manufacturer->support_phone }}
                                                    </a>
                                                </li>
                                            @endif

                                            @if (($asset_sim->model) && ($asset_sim->model->manufacturer->support_email))
                                                <li>
                                                    <i class="far fa-envelope" aria-hidden="true"></i>
                                                    <a href="mailto:{{ $asset_sim->model->manufacturer->support_email }}">
                                                        {{ $asset_sim->model->manufacturer->support_email }}
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
                                    @if (($asset_sim->model) && ($asset_sim->model->category))

                                        @can('view', \App\Models\Category::class)

                                            <a href="{{ route('categories.show', $asset_sim->model->category->id) }}">
                                                {{ $asset_sim->model->category->name }}
                                            </a>
                                        @else
                                            {{ $asset_sim->model->category->name }}
                                        @endcan
                                    @else
                                        Invalid category
                                    @endif
                                </div>
                            </div>

                            @if ($asset_sim->model)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('admin/hardware/form.model') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        @if ($asset_sim->model)

                                            @can('view', \App\Models\AssetModel::class)
                                                <a href="{{ route('models.show', $asset_sim->model->id) }}">
                                                    {{ $asset_sim->model->name }}
                                                </a>
                                            @else
                                                {{ $asset_sim->model->name }}
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
                                    {{ ($asset_sim->model) ? $asset_sim->model->model_number : ''}}
                                </div>
                            </div>


                            <div class="row">
                                <div class="col-md-2">
                                    <strong>
                                        {{ trans('admin/hardware/form.notes') }}
                                    </strong>
                                </div>
                                <div class="col-md-6">
                                    {!! nl2br(e($asset_sim->notes)) !!}
                                </div>
                            </div>

                            @if ($asset_sim->location)
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('general.location') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        @can('superuser')
                                            <a href="{{ route('locations.show', ['location' => $asset_sim->location->id]) }}">
                                                {{ $asset_sim->location->name }}
                                            </a>
                                        @else
                                            {{ $asset_sim->location->name }}
                                        @endcan
                                    </div>
                                </div>
                            @endif

                            @if ($asset_sim->created_at!='')
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('general.created_at') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        {{ Helper::getFormattedDateObject($asset_sim->created_at, 'datetime', false) }}
                                    </div>
                                </div>
                            @endif

                            @if ($asset_sim->updated_at!='')
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('general.updated_at') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        {{ Helper::getFormattedDateObject($asset_sim->updated_at, 'datetime', false) }}
                                    </div>
                                </div>
                            @endif
                            @if ($asset_sim->last_checkout!='')
                                <div class="row">
                                    <div class="col-md-2">
                                        <strong>
                                            {{ trans('admin/hardware/table.checkout_date') }}
                                        </strong>
                                    </div>
                                    <div class="col-md-6">
                                        {{ Helper::getFormattedDateObject($asset_sim->last_checkout, 'datetime', false) }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            </x-tabs.pane>
        @endisset
                </x-slot:tabpanes>
            </x-tabs>
        </div>
    </x-container>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', [
        'exportFile' => 'locations-export',
        'search' => true
     ])

    @if ($deviceCoordinates)
    <script src="https://api-maps.yandex.ru/2.1/?apikey=9aff6103-40f7-49e4-ad79-aa2a69d421d6&lang=ru_RU"
            type="text/javascript">
    </script>
    <script type="text/javascript">
        ymaps.ready(init);
        function init() {
            // Создание карты.
            var myMap = new ymaps.Map("map", {
                center: {{ Illuminate\Support\Js::from($deviceCoordinates) }},
                zoom: 15,
                controls: ['zoomControl']
            });
            $('a[data-toggle="tab"][href="#details"]').on('shown.bs.tab', function () {
                myMap.container.fitToViewport();
            });

            myMap.geoObjects.add(new ymaps.Placemark({{ Illuminate\Support\Js::from($deviceCoordinates) }}, {
                iconCaption: {{ Illuminate\Support\Js::from($device->locationUpdate) }},
                balloonContent: {{ Illuminate\Support\Js::from($device->model) }},
            }, {
                preset: 'islands#greenDotIconWithCaption'
            }));
            @if ($locationCoordinates)
                myMap.geoObjects.add(new ymaps.Placemark({{ Illuminate\Support\Js::from($locationCoordinates) }}, {
                    iconCaption: {{ Illuminate\Support\Js::from($asset->location->name) }}
                }, {
                    preset: 'islands#redDotIconWithCaption'
                }));
            @endif

        }
    </script>
    @endif
@stop
