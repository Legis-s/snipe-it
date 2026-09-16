@php
    $mapCoordinates = array_map('trim', explode(',', (string) $location->coordinates));
    $hasMapCoordinates = count($mapCoordinates) === 2
        && is_numeric($mapCoordinates[0]) && is_numeric($mapCoordinates[1]);
    if ($hasMapCoordinates) {
        $mapCoordinates = array_map('floatval', $mapCoordinates);
        $hasMapCoordinates = is_finite($mapCoordinates[0]) && is_finite($mapCoordinates[1])
            && abs($mapCoordinates[0]) <= 90 && abs($mapCoordinates[1]) <= 180;
    }
@endphp

@if ($hasMapCoordinates)
    <div id="location-map" aria-label="{{ trans('general.map') }}"
         style="width: 100%; height: 260px; margin-bottom: 15px"></div>
    <div id="location-map-error" class="text-danger" role="status" hidden>
        {{ trans('general.map_load_error') }}
    </div>
@endif

@if ($hasMapCoordinates)
    @push('js')
        <script src="https://api-maps.yandex.ru/2.1/?apikey=9aff6103-40f7-49e4-ad79-aa2a69d421d6&lang=ru_RU"
                type="text/javascript"></script>
        <script type="text/javascript">
            (function () {
                if (!window.ymaps) {
                    document.getElementById('location-map-error').hidden = false;
                    return;
                }
                window.ymaps.ready(function () {
                    var coordinates = @json($mapCoordinates);
                    var map = new window.ymaps.Map('location-map', {
                        center: coordinates,
                        zoom: 15,
                        controls: ['zoomControl']
                    });
                    map.geoObjects.add(new window.ymaps.Placemark(coordinates, {}, {
                        preset: 'islands#blueCircleDotIconWithCaption'
                    }));
                    if (window.ResizeObserver) {
                        var observer = new ResizeObserver(function () {
                            map.container.fitToViewport();
                        });
                        observer.observe(document.getElementById('location-map'));
                    }
                });
            }());
        </script>
    @endpush
@endif
