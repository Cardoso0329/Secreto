<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder;

class RecadoQuery
{
   public static function applyFilters($query, $filtros, $logica='AND')
{
    if (!is_array($filtros)) $filtros = []; // garante array

    foreach ($filtros as $f) {
        $field = $f['field'] ?? null;
        $operator = $f['operator'] ?? '=';
        $value = $f['value'] ?? '';

        if (!$field) continue;

        if ($operator === 'like') $value = "%{$value}%";

        if ($logica === 'AND') {
            $query->where($field, $operator, $value);
        } else {
            $query->orWhere($field, $operator, $value);
        }
    }

    return $query;
}

}

