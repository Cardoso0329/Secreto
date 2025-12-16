<?php


namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class RecadoFilter
{
    public static function apply(Builder $query, array $filters): Builder
    {
        foreach ($filters as $campo => $valor) {
            if ($valor === "" || $valor === null) continue;

            // Campos que usam LIKE
            if (in_array($campo, ['contact_client','plate'])) {
                $query->where($campo, 'like', "%{$valor}%");
            } else {
                $query->where($campo, $valor);
            }
        }

        return $query;
    }
}

