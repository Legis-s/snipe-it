@props([
    'count' => null,
    'class' => false,

])

<x-tabs.nav-item
    :$class
    name="consumables"
    icon_type="consumables"
    label="{{ trans('general.consumables') }}"
    count="{{ $count }}"
    tooltip="{{ trans('general.consumables') }}"
/>