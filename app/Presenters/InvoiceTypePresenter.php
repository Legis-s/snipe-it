<?php

namespace App\Presenters;

/**
 * Class InvoiceTypePresenter
 */
class InvoiceTypePresenter extends Presenter
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
            ],[
                "field" => "bitrix_id",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => "Bitrix id",
                "visible" => false
            ],[
                "field" => "active",
                "searchable" => true,
                "sortable" => true,
                "title" => "Активность",
                "visible" => true,
                "formatter" => "trueFalseFormatter"
            ],[
                'field' => 'name',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/locations/table.name'),
                'visible' => true,
            ], [
                'field' => 'created_at',
                'searchable' => true,
                'sortable' => true,
                'switchable' => true,
                'title' => trans('general.created_at'),
                'visible' => false,
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'actions',
                'searchable' => false,
                'sortable' => false,
                'switchable' => false,
                'title' => trans('table.actions'),
                'visible' => true,
                'formatter' => 'invoicetypesActionsFormatter',
            ],
        ];

        return json_encode($layout);
    }

    /**
     * Link to this locations name
     * @return string
     */
    public function nameUrl()
    {
        return (string) link_to_route('invoicetypes.show', $this->name, $this->id);
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
        return route('invoicetypes.show', $this->id);
    }

//    public function glyph()
//    {
//        return '<i class="fas fa-map-marker-alt" aria-hidden="true"></i>';
//    }

    public function fullName()
    {
        return $this->name;
    }
}
