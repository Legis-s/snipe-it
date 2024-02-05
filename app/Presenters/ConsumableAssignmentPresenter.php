<?php


namespace App\Presenters;


use App\Models\CustomField;

class ConsumableAssignmentPresenter extends Presenter
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
            ], [
                "field" => "name",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Запись",
                "visible" => true,
            ], [
                "field" => "assigned_to",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('admin/hardware/form.checkedout_to'),
                "visible" => true,
                "formatter" => "polymorphicItemFormatter"
            ], [
                "field" => "quantity",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => "Количество",
                "visible" => true,
                "formatter" => "quantityItemFormatter"
            ], [
                "field" => "cost",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => "Cтоимость",
                "visible" => true,
            ], [
                "field" => "responsibleUser",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => "Ответственный",
                "visible" => true,
                "formatter" => "usersLinkObjFormatter"
            ], [
                "field" => "contract",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => "Договор",
                "visible" => false,
                "formatter" => "contractsLinkObjFormatter"
            ], [
                "field" => "comment",
                "searchable" => true,
                "sortable" => true,
                "visible" => false,
                "title" => "Комментарий",
            ], [
                "field" => "return",
                "searchable" => false,
                "sortable" => false,
                "visible" => true,
                "title" => "Вернуть",
                "events"=>"operateEvents",
                "formatter" => "consumablesReturnFormatter",
            ], [
                "field" => "created_at",
                "searchable" => false,
                "sortable" => true,
                "visible" => false,
                "title" => trans('general.created_at'),
                "formatter" => "dateDisplayFormatter"
            ], [
                "field" => "updated_at",
                "searchable" => false,
                "sortable" => true,
                "visible" => false,
                "title" => trans('general.updated_at'),
                "formatter" => "dateDisplayFormatter"
            ]
        ];


        return json_encode($layout);
    }


    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableLayoutIn()
    {
        $layout = [
            [
                "field" => "id",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('general.id'),
                "visible" => false
            ], [
                "field" => "name",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Запись",
                "visible" => true,
            ], [
                "field" => "consumable",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Расходник",
                "visible" => true,
                "formatter" => "consumablesLinkObjFormatter"
            ], [
                "field" => "quantity",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => "Количество",
                "visible" => true,
                "formatter" => "quantityItemFormatter"
            ], [
                "field" => "cost",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => "Cтоимость",
                "visible" => true,
            ], [
                "field" => "responsibleUser",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => "Ответственный",
                "visible" => true,
                "formatter" => "usersLinkObjFormatter"
            ], [
                "field" => "contract",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => "Договор",
                "visible" => false,
                "formatter" => "contractsLinkObjFormatter"
            ], [
                "field" => "comment",
                "searchable" => true,
                "sortable" => true,
                "visible" => false,
                "title" => "Комментарий",
            ], [
                "field" => "created_at",
                "searchable" => false,
                "sortable" => true,
                "visible" => false,
                "title" => trans('general.created_at'),
                "formatter" => "dateDisplayFormatter"
            ], [
                "field" => "updated_at",
                "searchable" => false,
                "sortable" => true,
                "visible" => false,
                "title" => trans('general.updated_at'),
                "formatter" => "dateDisplayFormatter"
            ],[
                "field" => "return",
                "searchable" => false,
                "sortable" => false,
                "visible" => true,
                "title" => "Вернуть",
                "events"=>"operateEvents",
                "formatter" => "consumablesReturnFormatter",
            ]
        ];


        return json_encode($layout);
    }


    /**
     * Json Column Layout for bootstrap table
     * @return string
     */
    public static function dataTableLayoutNCD()
    {
        $layout = [
            [
                "field" => "id",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => trans('general.id'),
                "visible" => false
            ], [
                "field" => "consumable",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Расходник",
                "visible" => true,
                "formatter" => "consumablesLinkObjFormatter"
            ], [
                "field" => "name",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Запись",
                "visible" => true,
            ], [
                "field" => "assigned_to",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('admin/hardware/form.checkedout_to'),
                "visible" => true,
                "formatter" => "polymorphicItemFormatter"
            ], [
                "field" => "contract",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Договор",
                "visible" => true,
                "formatter" => "contractsLinkObjFormatter"
            ], [
                "field" => "quantity",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => "Количество",
                "visible" => true,
                "formatter" => "quantityItemFormatter"
            ], [
                "field" => "cost",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => "Cтоимость",
                "visible" => true,
            ], [
                "field" => "user",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => "Ответственный",
                "visible" => true,
                "formatter" => "usersLinkObjFormatter"
            ], [
                "field" => "comment",
                "searchable" => true,
                "sortable" => true,
                "visible" => false,
                "title" => "Комментарий",
            ], [
                "field" => "created_at",
                "searchable" => false,
                "sortable" => true,
                "visible" => false,
                "title" => trans('general.created_at'),
                "formatter" => "dateDisplayFormatter"
            ], [
                "field" => "updated_at",
                "searchable" => false,
                "sortable" => true,
                "visible" => false,
                "title" => trans('general.updated_at'),
                "formatter" => "dateDisplayFormatter"
            ],[
                "field" => "return",
                "searchable" => false,
                "sortable" => false,
                "visible" => true,
                "title" => "Вернуть",
                "events"=>"operateEvents",
                "formatter" => "consumablesReturnFormatter",
            ]
        ];


        return json_encode($layout);
    }



    /**
     * Get Displayable Name
     * @return string
     **/
    public function name()
    {

        if (empty($this->model->name)) {
            if (isset($this->model->model)) {
                return $this->model->model->name . ' (' . $this->model->asset_tag . ')';
            }
            return $this->model->asset_tag;
        }
        return $this->model->name . ' (' . $this->model->asset_tag . ')';

    }

}