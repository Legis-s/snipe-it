<?php

namespace App\Presenters;

/**
 * Class LocationPresenter
 */
class DevicePresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableLayout()
    {
        $layout = [

            [
                'field' => 'id',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.id'),
                'visible' => false,
            ],
            [
                'field' => 'statusCode',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => "Статус",
                'visible' => true,
                'formatter' => 'mdmStatusCodeFormatter',
            ],
            [
                'field' => 'number',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => "IMEI",
                'visible' => true,
                'formatter' => 'devicesLinkFormatter',
            ],
            [
                'field' => 'asset',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => "Актив",
                'visible' => true,
                'formatter' => 'hardwareLinkObjFormatter',
            ],
            [
                'field' => 'asset_sim',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => "Сим карта",
                'visible' => true,
                'formatter' => 'hardwareLinkObjFormatter',
            ],
            [
                'field' => 'location',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' =>  trans('admin/hardware/table.location'),
                'visible' => true,
                'formatter' => 'deployedLocationFormatter',
            ],
            [
                'field' => 'deviceId',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => "DeviceId",
                'visible' => true,
            ],
            [
                'field' => 'launcherVersion',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => "Лаунчер",
                'visible' => true,
            ],
            [
                'field' => 'androidVersion',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => "Android",
                'visible' => true,
            ],
            [
                'field' => 'biometrikaVersion',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => "Биометрика",
                'visible' => true,
            ],
            [
                'field' => 'serial',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => "Серийный номер",
                'visible' => true,
            ],
            [
                'field' => 'model',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => "Модель",
                'visible' => true,
            ],
            [
                'field' => 'description',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => "Описание",
                'visible' => true,
            ],
            [
                'field' => 'batteryLevel',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' => "Заряд",
                'visible' => true,
            ],
            [
                'field' => 'distance',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' =>"Разн. раст.",
                'visible' => false,
                'formatter' => 'mdmDistanceFormatter',
            ],
            [
                'field' => 'coordinates',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' =>"Координаты",
                'visible' => false,
                'formatter' => 'yandexMapLinkFormatter',
            ],
            [
                'field' => 'locationUpdate',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' =>"Время обн. коорд.",
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'lastUpdate',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' =>"Посл. связь",
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'updated_at',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.updated_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ],
            [
                'field' => 'created_at',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.created_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ]

        ];

        return json_encode($layout);
    }

    /**
     * Link to this locations name
     * @return string
     */
    public function nameUrl()
    {
        return (string) link_to_route('locations.show', $this->name, $this->id);
    }

    /**
     * Getter for Polymorphism.
     * @return mixed
     */
    public function name()
    {
        return $this->model->name;
    }

    /**
     * Url to view this item.
     * @return string
     */
    public function viewUrl()
    {
        return route('locations.show', $this->id);
    }

    public function glyph()
    {
        return '<i class="fas fa-map-marker-alt" aria-hidden="true"></i>';
    }

    public function fullName()
    {
        return $this->name;
    }
}
