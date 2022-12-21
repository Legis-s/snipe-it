<?php

namespace App\Presenters;

use App\Helpers\Helper;



/**
 * Class LocationPresenter
 * @package App\Presenters
 */
class ContractPresenter extends Presenter
{

    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableLayout()
    {
        $layout = [

            [
                "field" => "id",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('general.id'),
                "visible" => false
            ],[
                "field" => "bitrix_id",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => "Bitrix id",
                "visible" => true,
                "formatter" => "bitrixIdContractFormatter"
            ], [
                "field" => "name",
                "searchable" => true,
                "sortable" => true,
                "title" => "Название",
                "visible" => true,
                "formatter" => "contractsLinkFormatter"
            ], [
                "field" => "assets_count",
                "searchable" => true,
                "sortable" => true,
                "title" => "Активов",
                "visible" => true,
//                "formatter" => "contractsLinkFormatter"
            ], [
                "field" => "number",
                "searchable" => true,
                "sortable" => true,
                "title" => "Номер",
                "visible" => true,
//                "formatter" => "locationsLinkFormatter"
            ], [
                "field" => "status",
                "searchable" => true,
                "sortable" => true,
                "title" => "Статус",
                "visible" => true,
//                "formatter" => "locationsLinkFormatter"
            ], [
                "field" => "type",
                "searchable" => true,
                "sortable" => true,
                "title" => "Тип",
                "visible" => true,
//                "formatter" => "locationsLinkFormatter"
            ],
            [
                "field" => "created_at",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('general.created_at'),
                "visible" => false,
                'formatter' => 'dateDisplayFormatter'
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
        return (string)link_to_route('contracts.show', $this->name, $this->id);
    }


    /**
     * Url to view this item.
     * @return string
     */
    public function viewUrl()
    {
        return route('contracts.show', $this->id);
    }


    public function glyph()
    {
        return '<i class="fas fa-file-lines" aria-hidden="true"></i>';
    }


    public function fullName() {
        return "[".$this->number."] ".$this->name;
    }



}