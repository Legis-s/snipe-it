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
                "title" => trans('admin/locations/table.name'),
                "visible" => true,
//                "formatter" => "locationsLinkFormatter"
            ], [
                "field" => "number",
                "searchable" => true,
                "sortable" => true,
                "title" => "Номер",
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




}