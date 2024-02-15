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
            [
                "field" => "id",
                "searchable" => false,
                "sortable" => true,
                "title" => trans('general.id'),
                "visible" => false
            ],
            [
                "field" => "name",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('admin/massoperations/general.title_table'),
                "formatter" => 'bulkLinkFormatter'
            ],
            [
                "field" => "bitrix_task_id",
                "searchable" => true,
                "sortable" => true,
                "title" => "BitrixID",
            ],
            [
                "field" => "action_type",
                "searchable" => true,
                "sortable" => true,
                "visible" => true,
                "title" => trans('admin/massoperations/general.type_table'),
            ], [
                'field' => 'assigned_to',
                'searchable' => true,
                'sortable' => true,
                'title' => trans('admin/hardware/form.checkedout_to'),
                'visible' => true,
                'formatter' => 'polymorphicItemFormatter',
            ],
            [
                "field" => "assets_count",
                "searchable" => false,
                "sortable" => true,
                "title" => trans('admin/massoperations/general.assets_count'),
            ],
            [
                "field" => "consumables_count",
                "searchable" => false,
                "sortable" => true,
                "title" => trans('admin/massoperations/general.consumables_count'),
            ],[
                'field' => 'user',
                'searchable' => false,
                'sortable' => true,
                'switchable' => true,
                'title' =>  "Созданно",
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ],
            [
                "field" => "notes",
                "searchable" => true,
                "sortable" => false,
                "visible" => true,
                "title" => "Комментарий",
            ], [
                'field' => 'created_at',
                'searchable' => false,
                'sortable' => true,
                'visible' => true,
                'title' => trans('general.created_at'),
                'formatter' => 'dateDisplayFormatter',
            ], [
                'field' => 'updated_at',
                'searchable' => false,
                'sortable' => true,
                'visible' => false,
                'title' => trans('general.updated_at'),
                'formatter' => 'dateDisplayFormatter',
            ]
        ];


        return json_encode($layout);
    }

    public function actionType()
    {
        return mb_strtolower(trans('general.'.str_replace(' ', '_', $this->operation_type)));
    }


}
