<?php

namespace App\Presenters;

use App\Helpers\Helper;
use Illuminate\Support\Facades\Gate;

/**
 * Class MassOperationPresenter
 * @package App\Presenters
 */
class MassOperationPresenter extends Presenter
{
    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableAllLayout()
    {
        $layout = [
            [
                "field" => "id",
                "searchable" => false,
                "sortable" => true,

                "title" => trans('general.id'),
                "visible" => false
            ],
            [
                "field" => "type",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('admin/massoperations/general.type_table'),
                "visible" => true
            ],
            [
                "field" => "name",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('admin/massoperations/general.title_table'),
//                "formatter" => "licensesLinkFormatter"
            ],
            [
                "field" => "assets_count",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('admin/massoperations/general.assets_count'),
//                "formatter" => "licensesLinkFormatter"
            ],
            [
                "field" => "notes",
                "searchable" => true,
                "sortable" => false,
                "visible" => true,
                "title" => trans('general.notes'),
//                "formatter" => "notesFormatter"
            ],
            [
                "field" => "date",
                "searchable" => true,
                "sortable" => false,
                "visible" => true,
                "switchable" => true,
                "title" => trans('admin/massoperations/general.date'),
//                "formatter" => "notesFormatter"
            ],
            [
                "field" => "author",
                "searchable" => true,
                "sortable" => false,
                "visible" => true,
                "switchable" => true,
                "title" => trans('admin/massoperations/general.author'),
//                "formatter" => "notesFormatter"
            ],
            [
                "field" => "purpose",
                "searchable" => true,
                "sortable" => false,
                "visible" => true,
                "switchable" => true,
                "title" => trans('admin/massoperations/general.purpose'),
//                "formatter" => "notesFormatter"
            ]
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
