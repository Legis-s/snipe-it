<?php


namespace App\Presenters;


class PurchasePresenter extends Presenter
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
                "field" => "invoice_number",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Название",
                "visible" => true,
                "formatter" => "purchasesLinkFormatter"
            ],[
                "field" => "supplier",
                "searchable" => true,
                "sortable" => true,
                "title" => trans('general.supplier'),
                "visible" => false,
                "formatter" => "suppliersLinkObjFormatter"
            ],[
                "field" => "invoice_type",
                "searchable" => true,
                "sortable" => true,
                "title" => "Тип счета",
                "visible" => false,
            ],[
                "field" => "legal_person",
                "searchable" => true,
                "sortable" => true,
                "title" => "Юр. лицо",
                "visible" => false,
            ],[
                "field" => "assets_count",
                "searchable" => false,
                "sortable" => true,
                "title" => "Активов",
                "visible" => true,
                "formatter" => "assetsCountFormatter"
            ],[
                "field" => "consumables_count",
                "searchable" => false,
                "sortable" => true,
                "title" => "Расходников",
                "visible" => true,
                "formatter" => "consumablesCountFormatter"
            ],[
                "field" => "invoice_file",
                "searchable" => false,
                "sortable" => true,
                "title" => "Файл счета",
                "visible" => true,
                "formatter" => "fileFormatter"
            ],[
                "field" => "bitrix_id",
                "searchable" => false,
                "sortable" => true,
                "title" => "ID заявки Bitrix",
                "visible" => true,
                "formatter" => 'bitrixIdFormatter',
                "events"=> "operateEvents"
            ],[
                "field" => "bitrix_task_id",
                "searchable" => false,
                "sortable" => true,
                "title" => "ID задачи Bitrix",
                "visible" => true,
                "formatter" => 'bitrixTaskIdFormatter',
            ],[
                "field" => "final_price",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Сумма",
                "visible" => true,
                "align" => 'right',
                "formatter" => 'priceFormatter',
            ],[
                "field" => "delivery_cost",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Доставка",
                "visible" => true,
                "align" => 'right',
                "formatter" => 'deliveryСostFormatter',
            ], [
                "field" => "status",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Статус",
                "visible" => true,
                "formatter" => 'purchaseStatusFormatter',
            ],[
                "field" => "comment",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Комментарий",
                "visible" => true,
            ], [
                'field' => 'created_by',
                'searchable' => false,
                'sortable' => true,
                'title' => trans('general.created_by'),
                'visible' => false,
                'formatter' => 'usersLinkObjFormatter',
            ],[
                "field" => "created_at",
                "searchable" => false,
                "sortable" => true,
                "visible" => false,
                "title" => trans('general.created_at'),
                "formatter" => "dateDisplayFormatter"
            ],[
                "field" => "updated_at",
                "searchable" => false,
                "sortable" => true,
                "visible" => false,
                "title" => trans('general.updated_at'),
                "formatter" => "dateDisplayFormatter"
            ], [
                "field" => "actions",
                "searchable" => false,
                "sortable" => false,
                "switchable" => true,
                "title" => "Действия",
                "visible" => true,
                "formatter" => "purchasesActionsFormatter",
            ]
        ];

        return json_encode($layout);
    }


    /**
     * Link to this companies name
     * @return string
     */
    public function nameUrl()
    {
        return (string) link_to_route('purchases.show', $this->name, $this->id);
    }

    /**
     * Url to view this item.
     * @return string
     */
    public function viewUrl()
    {
        return route('purchases.show', $this->id);
    }

}