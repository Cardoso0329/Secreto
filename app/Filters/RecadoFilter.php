<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;

class RecadoFilter
{
    /**
     * Campos permitidos para filtro
     */
    protected static array $allowed = [
        'id',
        'contact_client',
        'plate',
        'estado_id',
        'tipo_formulario_id',
        'campanha_id',
        'tipo_id',
    ];

    /**
     * Campos que usam LIKE
     */
    protected static array $like = [
        'contact_client',
        'plate',
    ];

    public static function apply(Builder $query, array $filters): Builder
    {
        foreach ($filters as $campo => $valor) {

            // ignora vazios
            if ($valor === '' || $valor === null) {
                continue;
            }

            // ignora campos não permitidos
            if (!in_array($campo, self::$allowed)) {
                continue;
            }

            // LIKE ou =
            if (in_array($campo, self::$like)) {
                $query->where($campo, 'like', "%{$valor}%");
            } else {
                $query->where($campo, $valor);
            }
        }

        return $query;
    }
}
