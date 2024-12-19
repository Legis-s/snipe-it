@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.inventory') }}: {{ $inventory->name }}
    @parent
@stop

{{-- Page content --}}
@section('content')

    <div class="row">
        <div class="col-md-9">
            <div class="box">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="table table-responsive">
                                <table
                                        data-columns="{{ \App\Presenters\InventoryItemPresenter::dataTableLayout() }}"
                                        data-cookie-id-table="inventoryItemsTable"
                                        data-pagination="true"
                                        data-id-table="inventoryItems"
                                        data-search="true"
                                        data-side-pagination="server"
                                        data-show-columns="true"
                                        data-show-export="true"
                                        data-show-refresh="true"
                                        data-sort-order="asc"
                                        id="inventoryItemsTable"
                                        class="table table-striped snipe-table"
                                        data-url="{{route('api.inventory_items.index', ['inventory_id' => $inventory->id])}}">
                                </table>
                            </div><!-- /.table-responsive -->
                        </div>
                    </div>
                </div><!-- /.box-body -->
            </div> <!--/.box-->
        </div><!--/.col-md-9-->
        <div class="col-md-3">

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
                    <a href="{{$inventory->responsible_photo_url()}}" data-lightbox="inv">
                        <img src="{{$inventory->responsible_photo_url()}}" class="img-responsive img-thumbnail">
                    </a>
                </div>
            @endif

            @if ($inventory->coords!='' && $inventory->coords!='0.0, 0.0')
                <div class="col-md-12" style="padding-top: 20px;">
                    <div id="map" style="width: 100%; height: 300px"></div>
                </div>
            @endif

        </div><!--/.col-md-3-->
    </div>

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
                var myMap = new ymaps.Map("map", {
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


