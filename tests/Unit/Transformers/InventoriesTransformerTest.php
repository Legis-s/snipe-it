<?php

namespace Tests\Unit\Transformers;

use App\Http\Transformers\InventoriesTransformer;
use App\Models\Inventory;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class InventoriesTransformerTest extends TestCase
{
    #[DataProvider('inventory_statuses')]
    public function test_status_labels_are_localized_without_changing_api_status_codes(?string $status, string $english, string $russian): void
    {
        $inventory = new Inventory(['status' => $status, 'name' => 'Inventory']);
        $inventory->setRelation('location', null);

        foreach (['en-US' => $english, 'ru-RU' => $russian] as $locale => $expected) {
            app()->setLocale($locale);

            $result = (new InventoriesTransformer)->transformInventory($inventory);

            $this->assertSame($expected, $inventory->present()->statusText());
            $this->assertSame($expected, $result['status_text']);
            $this->assertSame($status ?: null, $result['status']);
        }
    }

    public static function inventory_statuses(): array
    {
        return [
            'started' => ['START', 'Started', 'Начата'],
            'completed' => ['FINISH_OK', 'Completed successfully', 'Завершена успешно'],
            'incomplete' => ['FINISH_BAD', 'Partially completed', 'Завершена не полностью'],
            'unknown' => ['UNKNOWN', '', ''],
            'empty' => ['', '', ''],
            'missing' => [null, '', ''],
        ];
    }
}
