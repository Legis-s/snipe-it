<?php

namespace App\Console\Commands;

use App\Models\Deal;
use App\Models\InvoiceType;
use App\Models\LegalPerson;
use App\Models\Location;
use App\Models\Supplier;
use App\Models\User;
use DateTime;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class SyncBitrix extends Command
{
    private const BITRIX_AVATAR_MIME_EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/bmp' => 'bmp',
        'image/x-ms-bmp' => 'bmp',
        'image/webp' => 'webp',
        'image/avif' => 'avif',
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'snipeit:sync-bitrix {--output= : info|warn|error|all} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This utility will sync with bitrix';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $bitrixMainUrl = config('services.bitrix.url');
        $bitrixUser = config('services.bitrix.user');
        $bitrixKey = config('services.bitrix.key');

        if ($bitrixMainUrl === null || $bitrixUser === null || $bitrixKey === null) {
            $this->error('Required Bitrix environment variables are not set. Please check BITRIX_URL, BITRIX_USER and BITRIX_KEY.');

            return 1;
        }

        $bitrixUrl = rtrim($bitrixMainUrl, '/') . '/rest/' . $bitrixUser . '/' . $bitrixKey . '/';
        $client = new Client([
            'connect_timeout' => 10,
            'timeout' => 60,
        ]);

        $this->syncUsers($client, $bitrixUrl);
        $this->syncObjects($client, $bitrixUrl);
        $this->syncSuppliers($client, $bitrixUrl);
        $this->syncLegals($client, $bitrixUrl);
        $this->syncDeals($client, $bitrixUrl);
        $this->syncTypes($client, $bitrixUrl);

        return 0;
    }

    private function syncUsers(Client $client, string $bitrixUrl): void
    {
        $bitrixUsers = $this->fetchPaged($client, $bitrixUrl, 'user.get.json', [], function (array $response): array {
            return $response['result'] ?? [];
        }, true);

        $avatars = 0;
        $avatarErrors = 0;

        foreach ($bitrixUsers as $value) {
            if (!$this->isActive($value['ACTIVE'] ?? null)) {
                User::where('bitrix_id', $value['ID'])->update(['activated' => false]);
                continue;
            }

            $user = User::firstOrNew(['bitrix_id' => $value['ID']]);
            if (!$user->exists) {
                $user->password = bcrypt($value['EMAIL'] ?? bin2hex(random_bytes(16)));
            }

            $user->fill([
                'username' => $value['EMAIL'] ?? $user->username,
                'last_name' => $value['LAST_NAME'] ?? null,
                'first_name' => $value['NAME'] ?? null,
                'email' => $value['EMAIL'] ?? null,
                'activated' => true,
            ]);

            if (!$user->exists || $user->isDirty()) {
                $user->save();
            }

            try {
                if ($this->syncBitrixAvatar($client, $bitrixUrl, $user, $value)) {
                    $avatars++;
                }
            } catch (Throwable $exception) {
                $avatarErrors++;
                $this->warn(sprintf(
                    'Не удалось синхронизировать аватар пользователя Bitrix %s: %s',
                    $value['ID'],
                    $exception->getMessage()
                ));
            }
        }

        $this->line('Синхрониизтрованно ' . count($bitrixUsers) . ' пользователей Битрикс');
        $this->line('Синхронизировано ' . $avatars . ' аватаров пользователей Битрикс');
        if ($avatarErrors > 0) {
            $this->line('Ошибок синхронизации аватаров: ' . $avatarErrors);
        }
    }

    private function syncBitrixAvatar(Client $client, string $bitrixUrl, User $user, array $bitrixUser): bool
    {
        $photoUrl = $this->getBitrixPhotoUrl($bitrixUser, $bitrixUrl);
        if ($photoUrl === null) {
            return false;
        }

        $bitrixId = (string) $bitrixUser['ID'];
        $photoHash = substr(sha1($photoUrl), 0, 12);
        if (is_string($user->avatar) && preg_match('/^bitrix-' . preg_quote($bitrixId, '/') . '-' . preg_quote($photoHash, '/') . '\\./', $user->avatar) === 1) {
            return false;
        }

        $response = $client->request('GET', $photoUrl, [
            'connect_timeout' => 5,
            'http_errors' => false,
            'timeout' => 15,
        ]);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new RuntimeException('Bitrix photo download returned HTTP ' . $response->getStatusCode());
        }

        $image = $response->getBody()->getContents();
        if ($image === '') {
            throw new RuntimeException('Bitrix photo download returned an empty body');
        }

        $mime = $this->detectImageMime($image);
        if ($mime === null || !array_key_exists($mime, self::BITRIX_AVATAR_MIME_EXTENSIONS)) {
            throw new RuntimeException('Unsupported Bitrix photo mime type: ' . ($mime ?? 'unknown'));
        }

        $filename = sprintf(
            'bitrix-%s-%s.%s',
            $bitrixId,
            $photoHash,
            self::BITRIX_AVATAR_MIME_EXTENSIONS[$mime]
        );

        Storage::disk('public')->makeDirectory('avatars');
        Storage::disk('public')->put('avatars/' . $filename, $image);
        $this->deletePreviousBitrixAvatar($user, $filename);

        $user->avatar = $filename;
        $user->save();

        return true;
    }

    private function getBitrixPhotoUrl(array $bitrixUser, string $bitrixUrl): ?string
    {
        $photo = $bitrixUser['PERSONAL_PHOTO'] ?? $bitrixUser['PERSONAL_PHOTO_URL'] ?? $bitrixUser['PHOTO'] ?? null;

        if (is_array($photo)) {
            foreach (['downloadUrl', 'url', 'src', 'SRC', 'VALUE'] as $key) {
                if (isset($photo[$key]) && is_string($photo[$key])) {
                    $photo = $photo[$key];
                    break;
                }
            }
        }

        if (!is_string($photo) || trim($photo) === '') {
            return null;
        }

        $photo = trim($photo);
        if (str_starts_with($photo, '//')) {
            $scheme = parse_url($bitrixUrl, PHP_URL_SCHEME) ?: 'https';
            $photo = $scheme . ':' . $photo;
        } elseif (str_starts_with($photo, '/')) {
            $scheme = parse_url($bitrixUrl, PHP_URL_SCHEME);
            $host = parse_url($bitrixUrl, PHP_URL_HOST);
            if ($scheme === null || $host === null) {
                return null;
            }

            $photo = $scheme . '://' . $host . $photo;
        }

        if (!filter_var($photo, FILTER_VALIDATE_URL)) {
            return null;
        }

        $scheme = parse_url($photo, PHP_URL_SCHEME);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        return $photo;
    }

    private function detectImageMime(string $image): ?string
    {
        $imageInfo = @getimagesizefromstring($image);
        if (is_array($imageInfo) && isset($imageInfo['mime']) && is_string($imageInfo['mime'])) {
            return $imageInfo['mime'];
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($image);

        return is_string($mime) ? $mime : null;
    }

    private function deletePreviousBitrixAvatar(User $user, string $currentFilename): void
    {
        if (!is_string($user->avatar) || $user->avatar === $currentFilename) {
            return;
        }

        if (preg_match('/^bitrix-' . preg_quote((string) $user->bitrix_id, '/') . '-[a-f0-9]{12}\.[a-z0-9]+$/', $user->avatar) !== 1) {
            return;
        }

        Storage::disk('public')->delete('avatars/' . $user->avatar);
    }

    private function syncObjects(Client $client, string $bitrixUrl): void
    {
        $bitrixObjects = $this->fetchPaged($client, $bitrixUrl, 'crm.item.list/', [
            'entityTypeId' => 1032,
            'select' => [
                'id',
                'title',
                'stageId',
                'assignedById',
                'ufCrm5_1721062689',
                'ufCrm5_1721063355',
                'ufCrm5_1742195522',
            ],
            'filter' => [
                'ufCrm5_1721062689' => [843, 845, 847, 848],
            ],
        ], function (array $response): array {
            return $response['result']['items'] ?? [];
        });

        $managerIds = array_values(array_unique(array_filter(array_column($bitrixObjects, 'assignedById'))));
        $managerIdByBitrixId = $managerIds === []
            ? []
            : User::whereIn('bitrix_id', $managerIds)->pluck('id', 'bitrix_id')->all();

        $objectIds = array_values(array_unique(array_filter(array_column($bitrixObjects, 'id'))));
        $locationsByBitrixId = $objectIds === []
            ? collect()
            : Location::withTrashed()->whereIn('bitrix_id', $objectIds)->get()->keyBy('bitrix_id');

        $count = 0;
        foreach ($bitrixObjects as $value) {
            $count++;
            $location = $locationsByBitrixId->get($value['id']);
            $objectData = $this->mapObjectPayload($value, $managerIdByBitrixId[(string) ($value['assignedById'] ?? '')] ?? null);

            if (!$objectData['active'] && $location && $location->isDeletableNoGate()) {
                if (!$location->trashed()) {
                    $location->delete();
                }

                continue;
            }

            $payload = $objectData['payload'];
            if ($location) {
                $location->update($payload);
                if ($location->trashed()) {
                    $location->restore();
                }
            } else {
                Location::create(array_merge(['bitrix_id' => $value['id']], $payload));
            }
        }

        $this->line('Синхронизировано ' . $count . ' объектов Битрикс');
    }

    private function mapObjectPayload(array $value, ?int $managerId): array
    {
        [$address, $coordinates] = $this->parseYandexMap($value['ufCrm5_1742195522'] ?? null);

        $objectCode = (int) ($value['ufCrm5_1721062689'] ?? 0);
        $title = $value['title'] ?? '';
        $name = match ($objectCode) {
            845 => '[Тех. безопасность] ' . $title,
            847 => '[Клининг] ' . $title,
            848 => '[Биометрика] ' . $title,
            default => $title,
        };

        $active = true;
        $closeDate = $value['ufCrm5_1721063355'] ?? '';
        if ($closeDate !== '') {
            $dateTime = DateTime::createFromFormat('d.m.Y', $closeDate);
            if ($dateTime instanceof DateTime && $dateTime <= new DateTime()) {
                $active = false;
                $name = '[Закрыто]' . $title;
            }
        }

        if (($value['stageId'] ?? null) === 'DT1032_7:FAIL') {
            $active = false;
            $name = '[Закрыто]' . $title;
        }

        return [
            'active' => $active,
            'payload' => [
                'name' => $name,
                'address' => $address,
                'coordinates' => $coordinates,
                'object_code' => $objectCode,
                'manager_id' => $managerId,
            ],
        ];
    }

    private function parseYandexMap(?string $yandexMapJson): array
    {
        if ($yandexMapJson === null || trim($yandexMapJson) === '') {
            return ['', ''];
        }

        $yandexMap = json_decode($yandexMapJson);
        if (!is_object($yandexMap)) {
            return ['', ''];
        }

        $address = property_exists($yandexMap, 'address') ? (string) $yandexMap->address : '';
        $coordinates = '';
        if (property_exists($yandexMap, 'coord') && is_array($yandexMap->coord)) {
            $coordinates = implode(',', $yandexMap->coord);
        }

        return [$address, $coordinates];
    }

    private function syncSuppliers(Client $client, string $bitrixUrl): void
    {
        $bitrixSuppliers = $this->fetchPaged($client, $bitrixUrl, 'crm.company.list', [
            'select' => [
                'ID',
                'TITLE',
                'ADDRESS',
                'ADDRESS_2',
                'ADDRESS_CITY',
                'COMMENTS',
                'COMPANY_TYPE',
            ],
            'filter' => [
                'COMPANY_TYPE' => 1,
            ],
        ], function (array $response): array {
            return $response['result'] ?? [];
        });

        $existingSuppliers = [];
        foreach ($bitrixSuppliers as $value) {
            $existingSuppliers[] = $value['ID'];
            Supplier::updateOrCreate(
                ['bitrix_id' => $value['ID']],
                [
                    'name' => $value['TITLE'] ?? '',
                    'city' => $value['ADDRESS_CITY'] ?? null,
                    'notes' => substr((string) ($value['COMMENTS'] ?? ''), 0, 185),
                    'address' => $value['ADDRESS'] ?? null,
                    'address2' => $value['ADDRESS_2'] ?? null,
                ]
            );
        }

        if ($existingSuppliers !== []) {
            Supplier::whereNotIn('bitrix_id', $existingSuppliers)->delete();
        }

        $this->line('Синхронизировано ' . count($bitrixSuppliers) . ' поставщиков');
    }

    private function syncLegals(Client $client, string $bitrixUrl): void
    {
        $bitrixLegalPersons = $this->bitrixGet($client, $bitrixUrl, 'lists.element.get', [
            'IBLOCK_TYPE_ID' => 'lists',
            'IBLOCK_ID' => 77,
        ])['result'] ?? [];

        $existingLegalPersons = [];
        foreach ($bitrixLegalPersons as $value) {
            $existingLegalPersons[] = $value['ID'];
            LegalPerson::updateOrCreate(
                ['bitrix_id' => $value['ID']],
                ['name' => $value['NAME'] ?? '']
            );
        }

        if ($existingLegalPersons !== []) {
            LegalPerson::whereNotIn('bitrix_id', $existingLegalPersons)->delete();
        }

        $this->line('Синхронизировано ' . count($bitrixLegalPersons) . ' юр. лиц');
    }

    private function syncDeals(Client $client, string $bitrixUrl): void
    {
        $deals = $this->fetchPaged($client, $bitrixUrl, 'crm.deal.list/', [
            'select' => [
                'ID',
                'TITLE',
                'OPPORTUNITY',
                'CATEGORY_ID',
                'STAGE_ID',
                'BEGINDATE',
                'CLOSEDATE',
                'ASSIGNED_BY_ID',
                'UF_CRM_1407316260',
            ],
            'filter' => [
                'CATEGORY_ID' => [3, 2, 13, 14, 9],
            ],
        ], function (array $response): array {
            return $response['result'] ?? [];
        });

        foreach ($deals as $value) {
            Deal::updateOrCreate(
                ['bitrix_id' => $value['ID']],
                [
                    'name' => $value['TITLE'] ?? '',
                    'number' => $value['UF_CRM_1407316260'] ?? null,
                    'status' => $value['STAGE_ID'] ?? null,
                    'type' => $value['CATEGORY_ID'] ?? null,
                    'date_start' => $value['BEGINDATE'] ?? null,
                    'date_end' => $value['CLOSEDATE'] ?? null,
                    'summ' => $value['OPPORTUNITY'] ?? null,
                    'assigned_by_id' => $value['ASSIGNED_BY_ID'] ?? null,
                ]
            );
        }

        $this->line('Синхронизировано ' . count($deals) . ' сделок');
    }

    private function syncTypes(Client $client, string $bitrixUrl): void
    {
        $bitrixInvoiceTypes = $this->bitrixGet($client, $bitrixUrl, 'lists.element.get', [
            'IBLOCK_TYPE_ID' => 'lists',
            'IBLOCK_ID' => 166,
        ])['result'] ?? [];

        $existingInvoiceTypes = [];
        foreach ($bitrixInvoiceTypes as $value) {
            $existingInvoiceTypes[] = $value['ID'];
            InvoiceType::updateOrCreate(
                ['bitrix_id' => $value['ID']],
                ['name' => $value['NAME'] ?? '']
            );
        }

        if ($existingInvoiceTypes !== []) {
            InvoiceType::whereNotIn('bitrix_id', $existingInvoiceTypes)->delete();
        }

        $this->line('Синхронизировано ' . count($bitrixInvoiceTypes) . ' типов закупок');
    }

    private function fetchPaged(Client $client, string $bitrixUrl, string $method, array $query, callable $extractItems, bool $continueByPageSize = false): array
    {
        $start = 0;
        $items = [];

        while (true) {
            $response = $this->bitrixGet($client, $bitrixUrl, $method, array_merge($query, ['start' => $start]));
            $pageItems = $extractItems($response);
            if (!is_array($pageItems)) {
                throw new RuntimeException('Bitrix response items are not an array for ' . $method);
            }

            foreach ($pageItems as $item) {
                $items[] = $item;
            }

            if (isset($response['next'])) {
                $start = (int) $response['next'];
                continue;
            }

            if ($continueByPageSize && count($pageItems) === 50) {
                $start += 50;
                continue;
            }

            break;
        }

        return $items;
    }

    private function bitrixGet(Client $client, string $bitrixUrl, string $method, array $query = []): array
    {
        $response = $client->request('GET', $bitrixUrl . $method, [
            'http_errors' => false,
            'query' => $query,
        ]);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            throw new RuntimeException(sprintf(
                'Bitrix request %s failed with HTTP %d',
                $method,
                $response->getStatusCode()
            ));
        }

        try {
            $data = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $exception) {
            throw new RuntimeException('Bitrix request ' . $method . ' returned invalid JSON: ' . $exception->getMessage(), 0, $exception);
        }

        if (!is_array($data)) {
            throw new RuntimeException('Bitrix request ' . $method . ' returned a non-array response');
        }

        if (isset($data['error'])) {
            throw new RuntimeException('Bitrix request ' . $method . ' failed: ' . ($data['error_description'] ?? $data['error']));
        }

        return $data;
    }

    private function isActive(mixed $value): bool
    {
        return in_array($value, [1, '1', true, 'Y', 'y'], true);
    }
}
