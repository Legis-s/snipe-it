@props([
    'item' => null,
    'route' => null,
    'wide' => false,
    'count' => 0,
    'tooltip' => trans('admin/users/general.print_assigned'),
])

@can('view', $item)
    @if ($count > 0)
        <a href="{{ $route }}" class="btn btn-sm btn-primary hidden-print" data-tooltip="true" title="{{ $tooltip }}">
             <x-icon type="print"/>
             @if ($wide=='true')
                {{ trans('general.print') }}
            @endif
        </a>

        <button class="btn btn-sm btn-info" id="print_tag" data-tooltip="true" title="{!! (!$item->model ? ' '.trans('admin/hardware/general.model_invalid') : trans_choice('button.print_label', 1)) !!}">
            <x-icon type="assets" class="fa-fw" />
        </button>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const printTagButton = document.getElementById('print_tag');

            if (!printTagButton) return;

            printTagButton.addEventListener('click', async function () {
                try {
                    const printResponse = await fetch(
                        'http://localhost:8001/termal_print?text={{ urlencode($item->asset_tag) }}',
                        {
                            method: 'GET'
                        }
                    );

                    if (printResponse.ok) {
                        await fetch("{{ route('api.assets.inventory', $item->id) }}", {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                                'Accept': 'application/json'
                            }
                        });
                    }
                } 3catch (error) {
                    console.log('error', error);
                }
            });
        });
    </script>
@endcan
