@extends('layouts/default')

{{-- Page title --}}
@section('title')
    {{ trans('general.map') }}
    @parent
@stop

{{-- Page content --}}
@section('content')
    <x-container>
        <x-box.container>
                <div class="box-header with-border">
                    <div class="box-heading">
                        <h2 class="box-title">
                            Всего: <span id="all_count" style="font-weight: bold"></span>
                            Готово: <span id="ok_count" style="font-weight: bold"></span>
                            Без имущества: <span id="null_count" style="font-weight: bold"></span>
                        </h2>
                    </div>
                </div>
                <div class="box-body">
                    <div id="map" style=" width: 100%; height: 900px"></div>
                </div>
        </x-box.container>
    </x-container>
@stop

@section('moar_scripts')
    {{--    @include ('partials.bootstrap-table', ['exportFile' => 'locations-export', 'search' => true])--}}
    <script src="https://api-maps.yandex.ru/2.1/?apikey=9aff6103-40f7-49e4-ad79-aa2a69d421d6&lang=ru_RU"
            type="text/javascript">
    </script>
    <script type="text/javascript">
        // Функция ymaps.ready() будет вызвана, когда
        // загрузятся все компоненты API, а также когда будет готово DOM-дерево.
        ymaps.ready(init);

        function init() {
            // Создание карты.
            var myMap = new ymaps.Map("map", {
                center: [55.76, 37.64],
                // Уровень масштабирования. Допустимые значения:
                // от 0 (весь мир) до 19.
                zoom: 11,
                controls: ['zoomControl']
            });

            CustomControlClass = function (options) {
                CustomControlClass.superclass.constructor.call(this, options);
                this._$content = null;
                this._geocoderDeferred = null;
            };
            // И наследуем его от collection.Item.
            ymaps.util.augment(CustomControlClass, ymaps.collection.Item, {
                onAddToMap: function (map) {
                    CustomControlClass.superclass.onAddToMap.call(this, map);
                    this._lastCenter = null;
                    this.getParent().getChildElement(this).then(this._onGetChildElement, this);
                },
                onRemoveFromMap: function (oldMap) {
                    this._lastCenter = null;
                    if (this._$content) {
                        this._$content.remove();
                        this._mapEventGroup.removeAll();
                    }
                    CustomControlClass.superclass.onRemoveFromMap.call(this, oldMap);
                },
                _onGetChildElement: function (parentDomContainer) {
                    // Создаем HTML-элемент с текстом.
                    this._$content = $('<div class="customControl">' +
                        '<div class="form-check">' +
                        '<input class="form-check-input" type="checkbox" value="" id="flexCheckDefault">' +
                        '<label class="form-check-label" for="flexCheckDefault">' +
                        ' Показать без имущества' +
                        '</label>' +
                        '</div>' +
                        '</div>').appendTo(parentDomContainer);
                    this._mapEventGroup = this.getMap().events.group();
                    // Запрашиваем данные после изменения положения карты.
                    this._mapEventGroup.add('boundschange', this._createRequest, this);
                    // Сразу же запрашиваем название места.
                    this._createRequest();
                },
                _createRequest: function () {
                },
                _onServerResponse: function (result) {
                    // Данные от сервера были получены и теперь их необходимо отобразить.
                    // Описание ответа в формате JSON.
                    var members = result.GeoObjectCollection.featureMember,
                        geoObjectData = (members && members.length) ? members[0].GeoObject : null;
                    if (geoObjectData) {
                        this._$content.text(geoObjectData.metaDataProperty.GeocoderMetaData.text);
                    }
                }
            });

            var customControl = new CustomControlClass();
            myMap.controls.add(customControl, {
                float: 'none',
                position: {
                    top: 20,
                    left: 20
                }
            });
            var objectManager = new ymaps.ObjectManager({
                // Чтобы метки начали кластеризоваться, выставляем опцию.
                clusterize: false,
                // ObjectManager принимает те же опции, что и кластеризатор.
                gridSize: 32,
                clusterDisableClickZoom: true
            });
            objectManager.objects.options.set({
                preset: 'islands#circleDotIcon',
            });
            // objectManager.objects.options.set('preset', 'islands#greenDotIcon');
            myMap.geoObjects.add(objectManager);

            $.ajax({
                type: 'GET',
                url: '{{  route('api.map.index') }}',
                headers: {
                    "X-Requested-With": 'XMLHttpRequest',
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr('content')
                },
                dataType: 'json',
            }).done(function (data) {
                console.log(data);
                $ok_count = 0;
                $null_count = 0;
                data.features.forEach((element) => {
                    console.log(element)
                    if (element.assets_count == 0) {
                        $null_count++;
                    } else {
                        if (element.assets_count == element.checked_assets_count) {
                            $ok_count++;
                        }
                    }

                });
                $("#all_count").html(data.features.length);
                $("#ok_count").html($ok_count);
                $("#null_count").html($null_count);
                objectManager.add(data);
            });
        }
    </script>
@stop
