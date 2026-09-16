@extends('layouts/default')

@section('title')
    {{ trans('general.map') }}
    @parent
@stop

@section('content')
    <x-container>
        <x-page-column class="col-md-12">
            <x-box>
                <div class="box-header with-border">
                    <div class="row">
                        <div class="col-sm-8" aria-live="polite">
                            <span>{{ trans('general.map_total') }}: <strong id="all_count">0</strong></span>
                            <span class="text-success" style="margin-left: 15px">{{ trans('general.map_complete') }}: <strong id="ok_count">0</strong></span>
                            <span style="margin-left: 15px">{{ trans('general.map_without_assets') }}: <strong id="null_count">0</strong></span>
                        </div>
                        <div class="col-sm-4">
                            <label class="checkbox-inline">
                                <input type="checkbox" id="map-show-empty" checked disabled>
                                {{ trans('general.map_show_empty') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    <div id="map-status" role="status" aria-live="polite">{{ trans('general.loading') }}</div>
                    <button type="button" id="map-retry" class="btn btn-default btn-sm" hidden>
                        <i class="fa fa-refresh" aria-hidden="true"></i> {{ trans('general.map_retry') }}
                    </button>
                    <div id="map" aria-label="{{ trans('general.map') }}" aria-busy="true"
                         style="width: 100%; height: calc(100vh - 200px); min-height: 160px"></div>
                </div>
            </x-box>
        </x-page-column>
    </x-container>
@stop

@section('moar_scripts')
    <script>
        (function () {
            'use strict';

            var status = $('#map-status');
            var retry = $('#map-retry');
            var showEmpty = $('#map-show-empty');
            var mapElement = document.getElementById('map');
            var map;

            function resizeMap() {
                var top = mapElement.getBoundingClientRect().top + window.scrollY;
                mapElement.style.height = Math.max(160, window.innerHeight - top - 12) + 'px';
                if (map) {
                    map.container.fitToViewport();
                }
            }

            resizeMap();
            window.addEventListener('resize', resizeMap);
            if (window.ResizeObserver) {
                var headerObserver = new ResizeObserver(resizeMap);
                headerObserver.observe(mapElement.closest('.box').querySelector('.box-header'));
            }
            var errorMessage = @json(trans('general.map_load_error'));
            var emptyMessage = @json(trans('general.no_results'));
            var apiReady = $.Deferred();
            var script = document.createElement('script');
            var apiTimeout = window.setTimeout(function () {
                apiReady.reject();
            }, 20000);

            retry.on('click', function () {
                window.location.reload();
            });

            // Fetch locations while the map SDK loads.
            var locationsRequest = $.ajax({
                url: @json(route('api.map.index')),
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
                timeout: 30000
            });

            script.async = true;
            script.src = 'https://api-maps.yandex.ru/2.1/?apikey=9aff6103-40f7-49e4-ad79-aa2a69d421d6&lang=ru_RU';
            script.onload = function () {
                if (!window.ymaps) {
                    apiReady.reject();
                    return;
                }
                window.ymaps.ready(function () {
                    apiReady.resolve();
                });
            };
            script.onerror = function () {
                apiReady.reject();
            };
            apiReady.always(function () {
                window.clearTimeout(apiTimeout);
            });
            document.head.appendChild(script);

            function showError() {
                status.text(errorMessage).removeClass('text-muted').addClass('text-danger').show();
                retry.prop('hidden', false);
                $('#map').attr('aria-busy', 'false');
                resizeMap();
            }

            $.when(apiReady, locationsRequest).done(function (_, response) {
                var data = response[0];
                if (!data || data.type !== 'FeatureCollection' || !Array.isArray(data.features)) {
                    showError();
                    return;
                }

                try {
                    map = new window.ymaps.Map('map', {
                        center: [55.76, 37.64],
                        zoom: 11,
                        controls: ['zoomControl']
                    });
                    var objectManager = new window.ymaps.ObjectManager({
                        clusterize: false
                    });
                    objectManager.objects.options.set('preset', 'islands#circleDotIcon');
                    map.geoObjects.add(objectManager);

                    var completeCount = 0;
                    var emptyCount = 0;
                    data.features.forEach(function (feature) {
                        if (feature.assets_count === 0) {
                            emptyCount++;
                        } else if (feature.assets_count === feature.checked_assets_count) {
                            completeCount++;
                        }
                    });

                    objectManager.add(data);
                    $('#all_count').text(data.features.length);
                    $('#ok_count').text(completeCount);
                    $('#null_count').text(emptyCount);
                    showEmpty.prop('disabled', false).on('change', function () {
                        var includeEmpty = this.checked;
                        objectManager.setFilter(function (feature) {
                            return includeEmpty || feature.assets_count > 0;
                        });
                    });

                    var bounds = objectManager.getBounds();
                    if (bounds) {
                        map.setBounds(bounds, {checkZoomRange: true, zoomMargin: 30});
                    }
                    status.text(data.features.length ? '' : emptyMessage).toggle(!data.features.length);
                    resizeMap();
                    $('#map').attr('aria-busy', 'false');
                } catch (error) {
                    showError();
                }
            }).fail(showError);
        }());
    </script>
@stop
