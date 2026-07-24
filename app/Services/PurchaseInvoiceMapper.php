<?php

namespace App\Services;

use App\Models\AssetModel;
use App\Models\Consumable;
use App\Models\LegalPerson;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PurchaseInvoiceMapper
{
    public function map(array $recognized): array
    {
        $assets = [];
        $consumables = [];
        $unmatched = [];

        foreach ($recognized['items'] as $item) {
            if (! is_array($item)) {
                continue;
            }

            $type = ($item['type'] ?? '') === 'consumable' ? 'consumable' : 'asset';
            $query = $type === 'consumable'
                ? Consumable::query()
                : AssetModel::query();
            $match = $this->findExactItem($query, $item);
            $candidates = [];

            if (! $match) {
                $candidates = $this->findCandidates($query, $item);
                $match = $this->findConfidentCandidate($query, $candidates);
            }

            if (! $match) {
                $unmatched[] = [
                    'name' => trim((string) ($item['name'] ?? '')),
                    'model_number' => trim((string) ($item['model_number'] ?? '')),
                    'type' => $type,
                    'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
                    'unit_price' => $this->number($item['unit_price'] ?? 0),
                    'candidates' => $candidates,
                ];

                continue;
            }

            $row = [
                'purchase_cost' => $this->number($item['unit_price'] ?? 0),
                'nds' => $this->number($item['vat_percent'] ?? 0),
                'quantity' => max(1, (int) ($item['quantity'] ?? 1)),
            ];

            if ($type === 'consumable') {
                $consumables[] = array_merge($row, [
                    'consumable_id' => (string) $match->id,
                    'consumable' => $this->displayName($match),
                    'check' => false,
                    'status' => trans('general.purchase_statuses.inprogress'),
                ]);
            } else {
                $assets[] = array_merge($row, [
                    'model_id' => (string) $match->id,
                    'model' => $this->displayName($match),
                    'location_id' => null,
                    'location' => '',
                    'warranty' => max(0, (int) ($item['warranty_months'] ?? 0)),
                ]);
            }
        }

        $deliveryCost = $this->number($recognized['delivery_cost'] ?? 0);
        $finalPrice = $this->number($recognized['final_price'] ?? 0);
        if ($finalPrice <= 0) {
            $finalPrice = $deliveryCost + collect($recognized['items'])
                ->filter(fn ($item) => is_array($item))
                ->sum(fn ($item) => $this->number($item['unit_price'] ?? 0) * max(1, (int) ($item['quantity'] ?? 1)));
        }

        $supplier = $this->findSupplier(
            (string) ($recognized['supplier'] ?? ''),
            (string) ($recognized['supplier_inn'] ?? '')
        );
        $legalPerson = $this->findLegalPerson((string) ($recognized['buyer'] ?? ''));
        $purchaseName = trim((string) ($recognized['purchase_name'] ?? ''));
        if ($purchaseName === '') {
            $purchaseName = trim((string) ($recognized['invoice_number'] ?? ''));
        }

        return [
            'invoice_number' => $purchaseName,
            'source_invoice_number' => trim((string) ($recognized['invoice_number'] ?? '')),
            'final_price' => $finalPrice,
            'delivery_cost' => $deliveryCost,
            'comment' => trim((string) ($recognized['comment'] ?? '')),
            'supplier' => $supplier ? ['id' => (string) $supplier->id, 'text' => $supplier->name] : null,
            'legal_person' => $legalPerson ? ['id' => (string) $legalPerson->id, 'text' => $legalPerson->name] : null,
            'assets' => $assets,
            'consumables' => $consumables,
            'unmatched' => $unmatched,
        ];
    }

    private function findExactItem(Builder $query, array $item): ?Model
    {
        $modelNumber = trim((string) ($item['model_number'] ?? ''));
        $name = trim((string) ($item['name'] ?? ''));

        if ($modelNumber !== '') {
            $matches = (clone $query)->whereRaw(
                'LOWER(TRIM(model_number)) = ?',
                [$this->normalize($modelNumber)]
            )->limit(2)->get();
            if ($matches->count() === 1) {
                return $matches->first();
            }
        }

        if ($name === '') {
            return null;
        }

        $matches = (clone $query)->whereRaw(
            'LOWER(TRIM(name)) = ?',
            [$this->normalize($name)]
        )->limit(2)->get();

        return $matches->count() === 1 ? $matches->first() : null;
    }

    private function findCandidates(Builder $query, array $item): array
    {
        $name = trim((string) ($item['name'] ?? ''));
        $modelNumber = trim((string) ($item['model_number'] ?? ''));
        $terms = $this->searchTerms($modelNumber.' '.$name);

        if ($terms === []) {
            return [];
        }

        $candidates = Collection::make();

        foreach ($terms as $term) {
            $matches = (clone $query)
                ->with(['manufacturer:id,name', 'category:id,name'])
                ->where(function (Builder $candidateQuery) use ($term) {
                    $candidateQuery->where('model_number', 'like', '%'.$this->escapeLike($term).'%')
                        ->orWhere('name', 'like', '%'.$this->escapeLike($term).'%');
                })
                ->limit(15)
                ->get();

            $candidates = $candidates->concat($matches);
        }

        return $candidates
            ->unique(fn (Model $candidate) => $candidate->getKey())
            ->map(fn (Model $candidate) => $this->candidateData($candidate, $name, $modelNumber))
            ->sortByDesc('score')
            ->take(5)
            ->values()
            ->all();
    }

    private function findConfidentCandidate(Builder $query, array $candidates): ?Model
    {
        $best = $candidates[0] ?? null;
        if (! $best || $best['score'] < 80) {
            return null;
        }

        $second = $candidates[1] ?? null;
        if ($second && ($best['score'] - $second['score']) < 8) {
            return null;
        }

        return (clone $query)->find($best['id']);
    }

    private function candidateData(Model $candidate, string $name, string $modelNumber): array
    {
        $candidateName = (string) $candidate->name;
        $candidateModelNumber = (string) $candidate->model_number;
        $score = $this->similarity($name, $candidateName);

        if ($modelNumber !== '' && $candidateModelNumber !== '') {
            $score = max($score, $this->similarity($modelNumber, $candidateModelNumber));
        }

        return [
            'id' => (string) $candidate->getKey(),
            'text' => $this->displayName($candidate),
            'model_number' => $candidateModelNumber,
            'manufacturer' => $candidate->manufacturer?->name,
            'category' => $candidate->category?->name,
            'score' => round($score, 2),
        ];
    }

    private function displayName(Model $item): string
    {
        $name = trim((string) $item->name);
        $modelNumber = trim((string) $item->model_number);

        if ($modelNumber !== '' && ! str_contains($this->normalize($name), $this->normalize($modelNumber))) {
            return $name !== '' ? "{$name} ({$modelNumber})" : $modelNumber;
        }

        return $name !== '' ? $name : '#'.$item->getKey();
    }

    private function searchTerms(string $value): array
    {
        return Collection::make(preg_split('/[^\pL\pN_-]+/u', $value) ?: [])
            ->map(fn (string $term) => trim($term))
            ->filter(fn (string $term) => mb_strlen($term) >= 3)
            ->unique()
            ->sortByDesc(fn (string $term) => mb_strlen($term))
            ->take(6)
            ->values()
            ->all();
    }

    private function similarity(string $left, string $right): float
    {
        $left = $this->normalize($left);
        $right = $this->normalize($right);

        if ($left === '' || $right === '') {
            return 0;
        }

        similar_text($left, $right, $percent);

        return $percent;
    }

    private function normalize(string $value): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $value) ?? $value));
    }

    private function findSupplier(string $name, string $inn): ?Supplier
    {
        $inn = preg_replace('/\D+/', '', $inn) ?? '';
        if ($inn !== '') {
            $supplier = Supplier::where('inn', $inn)
                ->orWhere('inn', 'like', '%'.$this->escapeLike($inn).'%')
                ->first();
            if ($supplier) {
                return $supplier;
            }
        }

        $name = trim($name);
        if ($name === '') {
            return null;
        }

        return $this->findOrganizationByName(Supplier::query(), $name);
    }

    private function findLegalPerson(string $name): ?LegalPerson
    {
        $name = trim($name);
        if ($name === '') {
            return null;
        }

        return $this->findOrganizationByName(LegalPerson::query(), $name);
    }

    private function findOrganizationByName(Builder $query, string $name): ?Model
    {
        $normalizedName = $this->normalizeOrganization($name);
        $exactMatches = (clone $query)->get()
            ->filter(fn (Model $organization) => $this->normalizeOrganization((string) $organization->name) === $normalizedName);

        if ($exactMatches->count() === 1) {
            return $exactMatches->first();
        }

        $terms = $this->searchTerms($name);
        if ($terms === []) {
            return null;
        }

        $candidates = Collection::make();
        foreach ($terms as $term) {
            $candidates = $candidates->concat(
                (clone $query)
                    ->where('name', 'like', '%'.$this->escapeLike($term).'%')
                    ->limit(20)
                    ->get()
            );
        }

        $ranked = $candidates
            ->unique(fn (Model $organization) => $organization->getKey())
            ->map(fn (Model $organization) => [
                'model' => $organization,
                'score' => $this->similarity(
                    $normalizedName,
                    $this->normalizeOrganization((string) $organization->name)
                ),
            ])
            ->sortByDesc('score')
            ->values();

        $best = $ranked->get(0);
        $second = $ranked->get(1);
        if (! $best || $best['score'] < 80 || ($second && ($best['score'] - $second['score']) < 8)) {
            return null;
        }

        return $best['model'];
    }

    private function normalizeOrganization(string $value): string
    {
        $value = str_replace('ё', 'е', mb_strtolower($value));
        $value = preg_replace('/общество\s+с\s+ограниченной\s+ответственностью/u', 'ооо', $value) ?? $value;
        $value = preg_replace('/индивидуальный\s+предприниматель/u', 'ип', $value) ?? $value;

        return trim(preg_replace('/[^\pL\pN]+/u', ' ', $value) ?? $value);
    }

    private function number(mixed $value): float
    {
        if (is_string($value)) {
            $value = str_replace([' ', ','], ['', '.'], $value);
        }

        return round(max(0, (float) $value), 2);
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }
}
