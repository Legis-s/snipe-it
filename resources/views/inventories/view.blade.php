@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.inventory') }}: {{ $inventory->name }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container columns="2">
        <x-page-column class="col-md-9">
            <x-box.container>
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
              "fileName": "export-inventory-items-{{ date('Y-m-d') }}",
              "ignoreColumn": ["actions","image","change","checkbox","checkincheckout","icon"]
              }'>
                    </table>
                </div><!-- /.table-responsive -->
            </x-box.container>
        </x-page-column>
        <x-page-column class="col-md-3">
            <x-box.container>
                @if (($inventory->status))
                    <div class="col-md-12" style="padding-bottom: 5px;">
                        @if ($inventory->status == "START")
                            <span class="label label-info">Начата</span>
                        @elseif ($inventory->status == "FINISH_OK")
                            <span class="label label-success">Завершена успешно</span>
                        @elseif ($inventory->status == "FINISH_BAD")
                            <span class="label label-danger">Завершена не полностью</span>
                        @else
                            Ошибка
                        @endif
                    </div>
                @endif

                <div class="col-md-12" style="padding-bottom: 2px;">
                    Всего активов: {{ $inventory->inventory_items_count() }}
                </div>
                <div class="col-md-12" style="padding-bottom: 2px;">
                    Найдено активов: {{ $inventory->inventory_items_checked_count() }}
                </div>
                <div class="col-md-12" style="padding-bottom: 10px;">
                    Успешно найдено активов: {{ $inventory->inventory_items_checked_success_count() }}
                </div>
                @if (($inventory->location))
                    <div class="col-md-12" style="padding-bottom: 10px;">
                        Объект: <a
                                href="{{ route('locations.show', ['location' => $inventory->location->id]) }}">{{ $inventory->location->name }}</a>
                    </div>
                @endif

                @if (($inventory->device))
                    <div class="col-md-12" style="padding-bottom: 10px;">
                        Устройство: {{ $inventory->device }}
                    </div>
                @endif

                @if (($inventory->comment))
                    <div class="col-md-12" style="padding-bottom: 10px;">
                        Комменатрий: {{ $inventory->comment }}
                    </div>
                @endif

                @if (($inventory->created_at))
                    <div class="col-md-12" style="padding-bottom: 2px;">
                        Созданно: {{ $inventory->created_at }}
                    </div>
                @endif

                @if (($inventory->updated_at))
                    <div class="col-md-12" style="padding-bottom: 10px;">
                        Обновленно: {{ $inventory->updated_at }}
                    </div>
                @endif

                @if (($inventory->responsible))
                    <div class="col-md-12" style="padding-bottom: 10px;">
                        Ответственный: {{ $inventory->responsible }}
                    </div>
                @endif


                @if ($inventory->responsible_photo)
                    <div class="col-md-12">
                        <a href="{{$inventory->responsible_photo_url()}}" data-toggle="lightbox">
                            <img src="{{$inventory->responsible_photo_url()}}" class="img-responsive img-thumbnail img-fluid">
                        </a>
                    </div>
                @endif

                @if ($inventory->coords!='' && $inventory->coords!='0.0, 0.0')
                    <div class="col-md-12" style="padding-top: 20px;">
                        <div id="map" style="width: 100%; height: 300px"></div>
                    </div>
                @endif
            </x-box.container>
        </x-page-column>

    </x-container>

@stop

@section('moar_scripts')
    @include ('partials.bootstrap-table', [
    'exportFile' => 'locations-export',
    'search' => true
 ])


    @if ($inventory->coords!='')
        <script src="https://api-maps.yandex.ru/2.1/?apikey=9aff6103-40f7-49e4-ad79-aa2a69d421d6&lang=ru_RU"
                type="text/javascript">
        </script>
        <script type="text/javascript">
            ymaps.ready(init);

            function init() {
                // Создание карты.
                const myMap = new ymaps.Map("map", {
                    center: [{{$inventory->coords}}],
                    zoom: 15,
                    controls: ['zoomControl']
                });
                myMap.geoObjects.add(new ymaps.Placemark([{{$inventory->coords}}], {
                    // balloonContent: 'цвет <strong>воды пляжа бонди</strong>'
                }, {
                    preset: 'islands#blueCircleDotIconWithCaption',
                }));
            }
        </script>
    @endif

@stop


