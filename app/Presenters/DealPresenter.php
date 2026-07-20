<?php

namespace App\Presenters;

use App\Helpers\Helper;


/**
 * Class LocationPresenter
 * @package App\Presenters
 */
class DealPresenter extends Presenter
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
                "field" => "bitrix_id",
                "searchable" => false,
                "sortable" => true,
                "switchable" => true,
                "title" => "Bitrix id",
                "visible" => true,
                "formatter" => "bitrixIdDealFormatter"
            ],[
                "field" => "number",
                "searchable" => true,
                "sortable" => true,
                "title" => "Номер",
                "visible" => true,
            ],[
                "field" => "name",
                "searchable" => true,
                "sortable" => true,
                "title" => "Название",
                "visible" => true,
                "formatter" => "dealsLinkFormatter"
            ], [
                "field" => "type",
                "searchable" => true,
                "sortable" => true,
                "title" => "Тип",
                "visible" => false,
            ], [
                "field" => "assets_count",
                "searchable" => true,
                "sortable" => true,
                "title" => "Акт.",
                "visible" => true,
            ], [
                "field" => "consumable_count",
                "searchable" => true,
                "sortable" => true,
                "title" => "Расх.",
                "visible" => true,
            ], [
                "field" => "assets_sum_purchase_cost",
                "searchable" => false,
                "sortable" => true,
                "title" => "Стоимость акт.",
                'class' => 'text-right',
                "formatter" => "dealsPriceFormatter",
                "visible" => true,
            ], [
                "field" => "consumables_sum_purchase_cost",
                "searchable" => false,
                "sortable" => true,
                "title" => "Стоимость расх.",
                'class' => 'text-right',
                "formatter" => "dealsPriceFormatter",
                "visible" => true,
            ], [
                "searchable" => false,
                "sortable" => true,
                "title" => "Стоимость",
                'class' => 'text-right',
                "formatter" => "dealsFullPriceFormatter",
                "visible" => true,
            ], [
                "field" => "summ",
                "searchable" => false,
                "sortable" => true,
                "title" => "Стоимость по дог.",
                'class' => 'text-right',
                "formatter" => "dealsPriceFormatter",
                "visible" => false,
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
        if (auth()->user()->can('view', ['\App\Models\Deal', $this])) {
            return '<a href="'.route('deals.show', $this->id).'">'.e($this->name).'</a>';
        } else {
            return e($this->name);
        }
    }


    /**
     * Url to view this item.
     * @return string
     */
    public function viewUrl()
    {
        return route('deals.show', $this->id);
    }

    public function glyph()
    {
        return '<x-icon type="deal"/>';
    }

    public function fullName()
    {
        return "[" . $this->number . "] " . $this->name;
    }


}