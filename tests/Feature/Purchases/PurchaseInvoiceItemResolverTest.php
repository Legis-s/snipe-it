<?php

namespace Tests\Feature\Purchases;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Consumable;
use App\Services\PurchaseInvoiceItemResolver;
use Tests\TestCase;

class PurchaseInvoiceItemResolverTest extends TestCase
{
    public function test_new_ai_items_are_created_with_separate_default_categories(): void
    {
        [$assets, $consumables] = app(PurchaseInvoiceItemResolver::class)->resolve(
            [[
                'create_new' => true,
                'new_item_name' => 'Ноутбук TestBook',
                'new_item_model_number' => 'TB-100',
                'quantity' => 2,
                'purchase_cost' => 1000,
            ]],
            [[
                'create_new' => true,
                'new_item_name' => 'Картридж TestPrint',
                'new_item_model_number' => 'TP-200',
                'quantity' => 3,
                'purchase_cost' => 100,
            ]]
        );

        $assetModel = AssetModel::findOrFail($assets[0]['model_id']);
        $consumable = Consumable::findOrFail($consumables[0]['consumable_id']);

        $this->assertSame('asset', $assetModel->category->category_type);
        $this->assertSame('consumable', $consumable->category->category_type);
        $this->assertSame(0, $consumable->qty);
        $this->assertFalse($assets[0]['create_new']);
        $this->assertFalse($consumables[0]['create_new']);
        $this->assertDatabaseHas('categories', [
            'name' => 'Без категории (AI)',
            'category_type' => 'asset',
        ]);
        $this->assertDatabaseHas('categories', [
            'name' => 'Без категории (AI)',
            'category_type' => 'consumable',
        ]);
    }

    public function test_existing_catalog_item_is_reused_when_invoice_is_saved(): void
    {
        $category = Category::factory()->create(['category_type' => 'consumable']);
        $existing = Consumable::factory()->create([
            'name' => 'Картридж TestPrint',
            'model_number' => 'TP-200',
            'category_id' => $category->id,
            'qty' => 7,
        ]);

        [, $consumables] = app(PurchaseInvoiceItemResolver::class)->resolve([], [[
            'create_new' => true,
            'new_item_name' => 'Картридж TestPrint',
            'new_item_model_number' => 'TP-200',
        ]]);

        $this->assertSame((string) $existing->id, $consumables[0]['consumable_id']);
        $this->assertSame(1, Consumable::where('model_number', 'TP-200')->count());
        $this->assertSame(7, $existing->fresh()->qty);
    }
}
