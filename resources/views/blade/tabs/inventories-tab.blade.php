@props([
    'count' => null,
    'class' => false,

])

<x-tabs.nav-item
    :$class
    name="inventories"
    icon_type="inventory"
    label="{{ trans('general.inventories') }}"
    count="{{ $count }}"
    tooltip="{{ trans('general.inventories') }}"
/>