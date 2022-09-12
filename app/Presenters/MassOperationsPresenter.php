<?php

namespace App\Presenters;

use App\Helpers\Helper;
use Illuminate\Support\Facades\Gate;

/**
 * Class MassOperationsPresenter
 * @package App\Presenters
 */
class MassOperationsPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableAllLayout()
    {
        $layout = [
//            [
//                "field" => "id",
//                "searchable" => false,
//                "sortable" => true,
//                "title" => trans('general.id'),
//                "visible" => false
//            ],
            [
                "field" => "operation_type",
                "searchable" => true,
                "sortable" => true,
                "visible" => true,
                "title" => trans('admin/massoperations/general.type_table'),
            ],
            [
                "field" => "name",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('admin/massoperations/general.title_table'),
            ],
            [
                "field" => "assets_count",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('admin/massoperations/general.assets_count'),
            ],
            [
                "field" => "date",
                "searchable" => true,
                "sortable" => false,
                "visible" => true,
                "switchable" => true,
                "title" => trans('admin/massoperations/general.date'),
            ],
            [
                "field" => "purpose",
                "searchable" => true,
                "sortable" => false,
                "visible" => true,
                "switchable" => true,
                "title" => trans('admin/massoperations/general.purpose'),
            ],
            [
                "field" => "notes",
                "searchable" => true,
                "sortable" => false,
                "visible" => true,
                "title" => trans('general.notes'),
            ],
        ];


        return json_encode($layout);
    }


    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableLayoutSeats()
    {
        $layout = [
           [
                "field" => "name",
                "searchable" => false,
                "sortable" => false,
                "switchable" => true,
                "title" => trans('admin/licenses/general.seat'),
                "visible" => true,
            ], [
                "field" => "assigned_user",
                "searchable" => false,
                "sortable" => false,
                "switchable" => true,
                "title" => trans('admin/licenses/general.user'),
                "visible" => true,
                "formatter" => "usersLinkObjFormatter"
            ], [
                "field" => "department",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('general.department'),
                "visible" => false,
                "formatter" => "departmentNameLinkFormatter"
            ],
            [
                "field" => "assigned_asset",
                "searchable" => false,
                "sortable" => false,
                "switchable" => true,
                "title" => trans('admin/licenses/form.asset'),
                "visible" => true,
                "formatter" => "hardwareLinkObjFormatter"
            ], [
                "field" => "location",
                "searchable" => false,
                "sortable" => false,
                "switchable" => true,
                "title" => trans('general.location'),
                "visible" => true,
                "formatter" => "locationsLinkObjFormatter"
            ],
            [
                "field" => "checkincheckout",
                "searchable" => false,
                "sortable" => false,
                "switchable" => true,
                "title" => trans('general.checkin').'/'.trans('general.checkout'),
                "visible" => true,
                "formatter" => "licenseSeatInOutFormatter"
            ]
        ];

        return json_encode($layout);
    }


    /**
     * Link to this licenses Name
     * @return string
     */
    public function nameUrl()
    {
        return (string)link_to_route('licenses.show', $this->name, $this->id);
    }

    /**
     * Link to this licenses Name
     * @return string
     */
    public function fullName()
    {
        return $this->name;
    }


    /**
     * Link to this licenses serial
     * @return string
     */
    public function serialUrl()
    {
        return (string) link_to('/licenses/'.$this->id, mb_strimwidth($this->serial, 0, 50, "..."));
    }

    /**
     * Url to view this item.
     * @return string
     */
    public function viewUrl()
    {
        return route('licenses.show', $this->id);
    }
}
