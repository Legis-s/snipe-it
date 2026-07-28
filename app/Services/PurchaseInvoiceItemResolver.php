<?php

namespace App\Services;

use App\Models\AssetModel;
use App\Models\Category;
use App\Models\Consumable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class PurchaseInvoiceItemResolver
{
    private const DEFAULT_CATEGORY_NAME = 'Без категории (AI)';

    public function resolve(array $assets, array $consumables): array
    {
        foreach ($assets as &$asset) {
            if (! ($asset['create_new'] ?? false)) {
                continue;
            }

            $model = $this->findOrCreateAssetModel($asset);
            $asset['model_id'] = (string) $model->id;
            $asset['model'] = $this->displayName($model);
            $asset['create_new'] = false;
        }
        unset($asset);

        foreach ($consumables as &$consumable) {
            if (! ($consumable['create_new'] ?? false)) {
                continue;
            }

            $item = $this->findOrCreateConsumable($consumable);
            $consumable['consumable_id'] = (string) $item->id;
            $consumable['consumable'] = $this->displayName($item);
            $consumable['create_new'] = false;
        }
        unset($consumable);

        return [$assets, $consumables];
    }

    private function findOrCreateAssetModel(array $row): AssetModel
    {
        $name = $this->itemName($row);
        $modelNumber = $this->modelNumber($row);
        $existing = $this->findExact(AssetModel::query(), $name, $modelNumber);

        if ($existing instanceof AssetModel) {
            return $existing;
        }

        $model = new AssetModel;
        $model->name = $name;
        $model->model_number = $modelNumber !== '' ? $modelNumber : null;
        $model->category_id = $this->defaultCategory('asset')->id;

        if (! $model->save()) {
            throw new RuntimeException('Не удалось создать модель актива из счёта: '.$name.'.');
        }

        return $model;
    }

    private function findOrCreateConsumable(array $row): Consumable
    {
        $name = $this->itemName($row);
        $modelNumber = $this->modelNumber($row);
        $existing = $this->findExact(Consumable::query(), $name, $modelNumber);

        if ($existing instanceof Consumable) {
            return $existing;
        }

        $consumable = new Consumable;
        $consumable->name = $name;
        $consumable->model_number = $modelNumber !== '' ? $modelNumber : null;
        $consumable->category_id = $this->defaultCategory('consumable')->id;
        $consumable->qty = 0;

        if (! $consumable->save()) {
            throw new RuntimeException('Не удалось создать расходник из счёта: '.$name.'.');
        }

        return $consumable;
    }

    private function defaultCategory(string $type): Category
    {
        $category = Category::withTrashed()
            ->where('name', self::DEFAULT_CATEGORY_NAME)
            ->where('category_type', $type)
            ->first();

        if ($category) {
            if ($category->trashed()) {
                $category->restore();
            }

            return $category;
        }

        $category = new Category;
        $category->name = self::DEFAULT_CATEGORY_NAME;
        $category->category_type = $type;
        $category->created_by = auth()->id();

        if (! $category->save()) {
            throw new RuntimeException('Не удалось создать служебную категорию для AI.');
        }

        return $category;
    }

    private function findExact(Builder $query, string $name, string $modelNumber): ?Model
    {
        if ($modelNumber !== '') {
            $matches = (clone $query)
                ->whereRaw('LOWER(TRIM(model_number)) = ?', [$this->normalize($modelNumber)])
                ->limit(2)
                ->get();

            if ($matches->count() === 1) {
                return $matches->first();
            }
        }

        $matches = (clone $query)
            ->whereRaw('LOWER(TRIM(name)) = ?', [$this->normalize($name)])
            ->limit(2)
            ->get();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    private function itemName(array $row): string
    {
        $name = trim((string) ($row['new_item_name'] ?? ''));
        $modelNumber = $this->modelNumber($row);
        $name = $name !== '' ? $name : $modelNumber;

        if ($name === '') {
            throw new RuntimeException('AI не указал название новой позиции счёта.');
        }

        return mb_substr($name, 0, 255);
    }

    private function modelNumber(array $row): string
    {
        return mb_substr(trim((string) ($row['new_item_model_number'] ?? '')), 0, 255);
    }

    private function displayName(Model $item): string
    {
        $name = trim((string) $item->name);
        $modelNumber = trim((string) $item->model_number);

        if ($modelNumber !== '' && ! str_contains($this->normalize($name), $this->normalize($modelNumber))) {
            return $name.' ('.$modelNumber.')';
        }

        return $name;
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value) ?? $value));
    }
}
