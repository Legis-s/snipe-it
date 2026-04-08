@props([
    'route' => route('api.inventory_items.index'),
    'name' => 'default',
    'presenter' => \App\Presenters\InventoryItemPresenter::dataTableLayout(),
    'fixed_right_number' => 2,
    'fixed_number' => 1,
    'show_search' => true,
    'show_advanced_search' => false,
    'show_column_search' => false,
    'table_header' => trans('general.inventories'),
])

<!-- start inventories tab pane -->
@can('view', \App\Models\Location::class)

    <x-slot:table_header>
        {{ $table_header }}
    </x-slot:table_header>


    <x-table
        :$presenter
        :$fixed_right_number
        :$fixed_number
        :$show_search
        :$show_column_search
        :$show_advanced_search
        api_url="{{ $route }}"
        export_filename="export-{{ str_slug($name) }}-inventory-{{ date('Y-m-d') }}"
    />


@endcan
<!-- end inventories tab pane -->