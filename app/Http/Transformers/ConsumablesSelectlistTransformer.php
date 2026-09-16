<?php

namespace App\Http\Transformers;

use Illuminate\Pagination\LengthAwarePaginator;

class ConsumablesSelectlistTransformer
{
    public function transformSelectlist(LengthAwarePaginator $consumables, bool $includeAvailability = false): array
    {
        $remaining = [];
        foreach ($consumables as $consumable) {
            $remaining[$consumable->id] = $consumable->numRemaining();
            $consumable->use_text = '['.$remaining[$consumable->id].'] '.e($consumable->name);
        }

        $results = (new SelectlistTransformer)->transformSelectlist($consumables);
        if ($includeAvailability) {
            foreach ($results['results'] as &$row) {
                unset($row['tag_color']);
                $row['numRemaining'] = $remaining[$row['id']];
                if ($row['numRemaining'] <= 0) {
                    $row['disabled'] = true;
                }
            }
        }

        return $results;
    }
}
