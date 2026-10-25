<?php

namespace App\Console\Commands;

use App\Actions\Purchases\TransitionPurchaseAction;
use App\Models\Purchase;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

class SyncBitrixPurchases extends Command
{
    protected $signature = 'snipeit:sync-bitrix-purchases';

    protected $description = 'Synchronize purchase statuses from Bitrix list 52';

    public function handle(): int
    {
        if (! config('services.bitrix.url') || ! config('services.bitrix.user') || ! config('services.bitrix.key')) {
            $this->error('Required Bitrix configuration is missing.');

            return self::FAILURE;
        }
        $client = app(Client::class);
        $url = rtrim(config('services.bitrix.url'), '/').'/rest/'.config('services.bitrix.user').'/'.config('services.bitrix.key').'/';
        $errors = 0;
        $synced = 0;

        try {
            $fields = $this->read($client, $url, 'lists.field.get', ['FIELD_ID' => 'PROPERTY_562']);
            $field = collect($fields['result'])->firstWhere('FIELD_ID', 'PROPERTY_562');
            $values = $field['DISPLAY_VALUES_FORM'] ?? null;
            if (! is_array($values) || $values === []) {
                throw new RuntimeException('Bitrix status field values are missing.');
            }

            Purchase::whereNotNull('bitrix_id')->where('bitrix_id', '>', 0)->chunkById(50, function ($purchases) use ($client, $url, $values, &$errors, &$synced): void {
                $byBitrixId = $purchases->groupBy('bitrix_id');
                $start = 0;
                do {
                    $response = $this->read($client, $url, 'lists.element.get', [
                        'FILTER' => ['ID' => $byBitrixId->keys()->all()],
                        'SELECT' => ['ID', 'PROPERTY_562'],
                        'start' => $start,
                    ]);
                    foreach ($response['result'] as $element) {
                        $matches = $byBitrixId->get($element['ID'] ?? '');
                        if ($matches === null) {
                            continue;
                        }
                        $raw = $element['PROPERTY_562'] ?? null;
                        if (is_array($raw)) {
                            $raw = count($raw) <= 1 ? (reset($raw) ?: null) : $raw;
                        }
                        if (! is_scalar($raw) && $raw !== null) {
                            $errors++;
                            $this->warn('Invalid Bitrix status for element '.($element['ID'] ?? ''));

                            continue;
                        }
                        $status = ($raw === null || $raw === '' || $raw === false) ? '' : ($values[$raw] ?? null);
                        if (! is_string($status)) {
                            $errors++;
                            $this->warn('Unknown Bitrix status for element '.($element['ID'] ?? ''));

                            continue;
                        }
                        $transition = match ($status) {
                            '!Оплачено' => 'paid',
                            '!Отказ' => 'rejected',
                            'Оплата' => 'in_payment',
                            default => null,
                        };
                        foreach ($matches as $purchase) {
                            try {
                                TransitionPurchaseAction::run($purchase->id, $transition, $status);
                                $synced++;
                            } catch (Throwable $exception) {
                                $errors++;
                                $this->warn('Purchase '.$purchase->id.': '.get_class($exception));
                            }
                        }
                    }
                    $next = $response['next'] ?? null;
                    if ($next !== null && (int) $next <= $start) {
                        throw new RuntimeException('Invalid Bitrix pagination.');
                    }
                    $start = (int) $next;
                } while ($next !== null);
            });
        } catch (Throwable $exception) {
            $this->error('Bitrix purchase sync failed: '.get_class($exception));

            return self::FAILURE;
        }
        $this->info('Purchases synchronized: '.$synced.'; errors: '.$errors);

        return $errors === 0 ? self::SUCCESS : self::FAILURE;
    }

    /** @param array<string, mixed> $query
     * @return array<string, mixed>
     */
    private function read(Client $client, string $url, string $method, array $query): array
    {
        $response = $client->request('GET', $url.$method, [
            'http_errors' => false,
            'connect_timeout' => 10,
            'timeout' => 60,
            'query' => array_merge(['IBLOCK_TYPE_ID' => 'lists', 'IBLOCK_ID' => 52], $query),
        ]);
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new RuntimeException('Bitrix HTTP error');
        }
        $data = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($data) || isset($data['error']) || ! isset($data['result']) || ! is_array($data['result'])) {
            throw new RuntimeException('Invalid Bitrix response');
        }

        return $data;
    }
}
