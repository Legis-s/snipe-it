@props([
    'item' => null,
])
@if ($item->deleted_at=='')
    <button class="btn btn-sm btn-info" id="print_tag" data-tooltip="true" title="{!! (!$item->model ? ' '.trans('admin/hardware/general.model_invalid') : trans_choice('button.print_label', 1)) !!}">
        <x-icon type="assets" class="fa-fw" />
    </button>

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
                } catch (error) {
                    console.log('error', error);
                }
            });
        });
    </script>
@endif