<?php

namespace App\Presenters;

/**
 * Class InventoryPresenter
 * @package App\Presenters
 */
class InventoryItemPresenter extends Presenter
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
                "field" => "checked",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Проверено",
                "visible" => true,
                "formatter" => 'trueFalseFormatter',
            ],[
                "field" => "successfully",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Успешно",
                "visible" => true,
                "formatter" => 'trueFalseFormatter',
            ],[
                "field" => "status",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Статус",
                "visible" => true,
                "formatter" => 'statusInventoryItemFormatter',
            ],[
                "field" => "photo",
                "searchable" => false,
                "sortable" => false,
                "visible" => false,
                "title" => "Фото",
                "formatter" => "photoDisplayFormatter"
            ],[
                "field" => "checked_at",
                "searchable" => false,
                "sortable" => true,
                "visible" => false,
                "title" => "Время проверки",
                "formatter" => "dateDisplayFormatter"
            ],[
                "field" => "tag",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Инв.н.",
                "visible" => true,
                "formatter" => "assetTagLinkFormatter"
            ],[
                "field" => "name",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Название",
                "visible" => true,
            ],[
                "field" => "model",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Модель",
                "visible" => true,
            ],[
                "field" => "category",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Категория",
                "visible" => true,
            ],[
                "field" => "manufacturer",
                "searchable" => true,
                "sortable" => true,
                "switchable" => true,
                "title" => "Производитель",
                "visible" => true,
            ],[
                "field" => "updated_at",
                "searchable" => false,
                "sortable" => true,
                "visible" => false,
                "title" => trans('general.updated_at'),
                "formatter" => "dateDisplayFormatter"
            ],[
                "field" => "created_at",
                "searchable" => false,
                "sortable" => true,
                "visible" => false,
                "title" => trans('general.created_at'),
                "formatter" => "dateDisplayFormatter"
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
        return (string) link_to_route('inventories.show', $this->name, $this->id);
    }

    /**
     * Url to view this item.
     * @return string
     */
    public function viewUrl()
    {
        return route('inventories.show', $this->id);
    }
}
