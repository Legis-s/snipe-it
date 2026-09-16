@extends('layouts/default')

@php
    $mapCoordinates = array_map('trim', explode(',', (string) $inventory->coords));
    $hasMapCoordinates = count($mapCoordinates) === 2
        && is_numeric($mapCoordinates[0]) && is_numeric($mapCoordinates[1])
        && abs((float) $mapCoordinates[0]) <= 90 && abs((float) $mapCoordinates[1]) <= 180
        && ((float) $mapCoordinates[0] !== 0.0 || (float) $mapCoordinates[1] !== 0.0);
    $mapCoordinates = $hasMapCoordinates ? array_map('floatval', $mapCoordinates) : [];
    $totalItems = $inventory->inventory_items_count();
    $checkedItems = $inventory->inventory_items_checked_count();
    $successfulItems = $inventory->inventory_items_checked_success_count();
    $progress = $totalItems > 0 ? min(100, round($checkedItems / $totalItems * 100)) : 0;
    $statusClass = match ($inventory->status) {
        'FINISH_OK' => 'success', 'FINISH_BAD' => 'warning', default => 'info',
    };
@endphp

{{-- Page title --}}
@section('title')
    {{ trans('general.inventory') }}: {{ $inventory->name }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <style>
        .inventory-summary { padding: 4px 5px; font-size: 14px; }
        .inventory-summary-heading { margin: 0 0 12px; font-size: 16px; font-weight: 600; }
        .inventory-summary .label { display: inline-block; white-space: normal; text-align: left; line-height: 1.4; }
        .inventory-summary-progress { display: flex; justify-content: space-between; gap: 12px; margin: 16px 0 6px; }
        .inventory-summary .progress { height: 6px; margin-bottom: 16px; box-shadow: none; }
        .inventory-summary-stats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 8px; padding-bottom: 16px; border-bottom: 1px solid #ddd; }
        .inventory-summary-stats strong { display: block; font-size: 22px; line-height: 1.3; }
        .inventory-summary-stats span { display: block; font-size: 12px; overflow-wrap: anywhere; }
        .inventory-summary-details { margin: 0; }
        .inventory-summary-details dt { font-size: 12px; font-weight: 400; margin-top: 14px; opacity: .75; }
        .inventory-summary-details dd { margin-top: 3px; overflow-wrap: anywhere; }
        .inventory-summary-person { display: flex; align-items: center; gap: 10px; min-width: 0; }
        .inventory-summary-person span { min-width: 0; overflow-wrap: anywhere; }
        .inventory-summary-photo { flex: 0 0 48px; }
        .inventory-summary-photo img { width: 48px; height: 48px; object-fit: cover; border-radius: 4px; }
        .inventory-summary-map { margin-top: 18px; border-top: 1px solid #ddd; padding-top: 14px; }
        .inventory-summary-map #map { width: 100%; height: 240px; }
    </style>
    <x-container columns="2">
        <x-page-column class="col-md-9 main-panel">
            <x-box>
                <div class="table table-responsive">
                    <table
                            data-columns="{{ \App\Presenters\InventoryItemPresenter::dataTableLayout() }}"
                            data-cookie-id-table="inventoryItemsTable"
                            data-id-table="inventoryItems"
                            data-side-pagination="server"
                            data-sort-order="asc"
                            id="inventoryItemsTable"
                            class="table table-striped snipe-table"
                            data-url="{{route('api.inventory_items.index', ['inventory_id' => $inventory->id])}}"
                            data-export-options='{
              "fileName": "export-inventories-items-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
                    </table>
                </div><!-- /.table-responsive -->
            </x-box>
        </x-page-column>
        <x-page-column class="col-md-3">
            <x-box>
                <section class="inventory-summary" aria-label="{{ trans('general.information') }}">
                    <h2 class="inventory-summary-heading">{{ trans('general.information') }}</h2>
                    @if ($inventory->status)
                        <span class="label label-{{ $statusClass }}">{{ $inventory->present()->statusText() ?: $inventory->status }}</span>
                    @endif
                    <div class="inventory-summary-progress">
                        <span>{{ trans('general.inventory_summary.progress') }}</span>
                        <strong>{{ $progress }}%</strong>
                    </div>
                    <div class="progress">
                        <div class="progress-bar progress-bar-info" role="progressbar" aria-label="{{ trans('general.inventory_summary.progress') }}" aria-valuenow="{{ $progress }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $progress }}%"></div>
                    </div>
                    <div class="inventory-summary-stats">
                        <div><strong>{{ $totalItems }}</strong><span>{{ trans('general.inventory_summary.total') }}</span></div>
                        <div><strong>{{ $checkedItems }}</strong><span>{{ trans('general.inventory_summary.checked') }}</span></div>
                        <div><strong class="text-success">{{ $successfulItems }}</strong><span>{{ trans('general.inventory_summary.successful') }}</span></div>
                    </div>
                    <dl class="inventory-summary-details">
                        @if ($inventory->location)
                            <dt>{{ trans('general.location') }}</dt>
                            <dd><a href="{{ route('locations.show', $inventory->location->id) }}">{{ $inventory->location->name }}</a></dd>
                        @endif
                        @if ($inventory->responsible || $inventory->responsible_photo)
                            <dt>{{ trans('general.inventory_summary.responsible') }}</dt>
                            <dd class="inventory-summary-person">
                                @if ($inventory->responsible_photo)
                                    <a class="inventory-summary-photo" href="{{ $inventory->responsible_photo_url() }}" data-toggle="lightbox">
                                        <img src="{{ $inventory->responsible_photo_url() }}" alt="{{ $inventory->responsible }}" onerror="this.parentElement.hidden = true;">
                                    </a>
                                @endif
                                <span>{{ $inventory->responsible }}</span>
                            </dd>
                        @endif
                        @if ($inventory->device)
                            <dt>{{ trans('general.inventory_summary.device') }}</dt><dd>{{ $inventory->device }}</dd>
                        @endif
                        @if ($inventory->comment)
                            <dt>{{ trans('general.notes') }}</dt><dd>{{ $inventory->comment }}</dd>
                        @endif
                        @if ($inventory->created_at)
                            <dt>{{ trans('general.created_at') }}</dt><dd>{{ $inventory->created_at->format('d.m.Y H:i') }}</dd>
                        @endif
                        @if ($inventory->updated_at)
                            <dt>{{ trans('general.updated_at') }}</dt><dd>{{ $inventory->updated_at->format('d.m.Y H:i') }}</dd>
                        @endif
                    </dl>
                    @if ($hasMapCoordinates)
                        <div class="inventory-summary-map"><div id="map"></div></div>
                    @endif
                </section>
            </x-box>
        </x-page-column>
    </x-container>
@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', [
    'exportFile' => 'inventories-export',
    'search' => true
 ])


    @if ($hasMapCoordinates)
        <script src="https://api-maps.yandex.ru/2.1/?apikey=9aff6103-40f7-49e4-ad79-aa2a69d421d6&lang=ru_RU"
                type="text/javascript">
        </script>
        <script type="text/javascript">
            ymaps.ready(init);

            function init() {
                // Создание карты.
                const myMap = new ymaps.Map("map", {
                    center: {{ Illuminate\Support\Js::from($mapCoordinates) }},
                    zoom: 15,
                    controls: ['zoomControl']
                });
                myMap.geoObjects.add(new ymaps.Placemark({{ Illuminate\Support\Js::from($mapCoordinates) }}, {
                    // balloonContent: 'цвет <strong>воды пляжа бонди</strong>'
                }, {
                    preset: 'islands#blueCircleDotIconWithCaption',
                }));
            }
        </script>
    @endif

@stop
