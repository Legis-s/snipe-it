@props([
    'label',
    'name' => 'quality',
    'selected' => null,
    'required' => false,
])

@php
    $id = $attributes->get('id', $name);
    $qualityOptions = [
        5 => 'Новое запакованное',
        4 => 'В отличном состоянии, но использовалось',
        3 => 'Рабочее, но с небольшими следами повреждений, небольшим загрязнением',
        2 => 'Частично рабочее или сильно загрязненное',
        1 => 'Полностью не рабочее',
    ];
@endphp

@pushOnce('css', 'quality-select-star-rating-css')
    <style>
        :root {
            --gl-star-empty: url('/img/star-empty.svg');
            --gl-star-full: url('/img/star-full.svg');
            --gl-star-size: 32px;
        }
    </style>
@endPushOnce

<div
    @class([
        'form-group',
        'has-error' => $errors->has($name),
    ])
>
    <label for="{{ $id }}" class="col-md-3 control-label">{{ $label }}</label>

    <div class="col-md-9">
        <div class="input-group col-md-4" style="padding-left: 0;">
            <select
                {{ $attributes->except('id')->class(['star-rating']) }}
                name="{{ $name }}"
                id="{{ $id }}"
                @required($required)
            >
                <option value="" @selected(blank($selected))>Оцените состояние</option>

                @foreach ($qualityOptions as $value => $optionLabel)
                    <option value="{{ $value }}" @selected((string) $selected === (string) $value)>
                        {{ $optionLabel }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-9" style="padding-left: 0;">
            <x-form.error :name="$name" />
        </div>
    </div>
</div>


@pushOnce('js', 'quality-select-star-rating-js')
    <script src="{{ url(mix('js/dist/star-rating.min.js')) }}" nonce="{{ csrf_token() }}"></script>
    <script nonce="{{ csrf_token() }}">
        $(function () {
            if (typeof window.StarRating !== 'function') {
                console.error('StarRating library failed to load.');
                return;
            }

            new window.StarRating('.star-rating', {
                maxStars: 5,
                tooltip: 'Оцените состояние',
                clearable: false,
            });
        });
    </script>
@endPushOnce
