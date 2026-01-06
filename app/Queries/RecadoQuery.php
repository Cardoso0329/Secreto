<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder;

class RecadoQuery
{
    public static function applyFilters(Builder $query, $filtros, string $logica = 'AND'): Builder
{
    $filtros = is_array($filtros) ? array_values($filtros) : [];
    $logica  = strtoupper($logica) === 'OR' ? 'OR' : 'AND';

    // limpa inválidos
    $filtros = array_values(array_filter($filtros, function ($f) {
        if (!is_array($f)) return false;
        $field = $f['field'] ?? null;
        if (!$field || !is_string($field)) return false;
        return (bool) preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $field);
    }));

    if (empty($filtros)) return $query;

    // ✅ agrupar sempre
    $query->where(function (Builder $group) use ($filtros, $logica) {

        $applied = 0; // ✅ conta condições realmente aplicadas (para OR funcionar bem)

        foreach ($filtros as $f) {
            $field = $f['field'] ?? null;
            $op    = strtoupper(trim((string)($f['operator'] ?? '=')));
            $value = $f['value'] ?? null;

            if ($value === '' || $value === null) continue;

            // normaliza
            $op = match ($op) {
                '!=' => '<>',
                'LIKE' => 'LIKE',
                'like' => 'LIKE',
                default => $op,
            };

            // helper para aplicar where/orWhere
            $applyWhere = function (callable $cb) use ($group, $logica, &$applied) {
                if ($logica === 'OR') {
                    if ($applied === 0) $group->where($cb);
                    else $group->orWhere($cb);
                } else {
                    $group->where($cb);
                }
                $applied++;
            };

            /* ======================================================
               ✅ CAMPO ESPECIAL: DESTINATÁRIO (relação)
               field = destinatario_user_id
               ====================================================== */
            if ($field === 'destinatario_user_id') {
                $userId = $value;

                if ($op === '<>') {
                    $applyWhere(function (Builder $q) use ($userId) {
                        $q->whereDoesntHave('destinatarios', function ($d) use ($userId) {
                            $d->where('users.id', $userId);
                        });
                    });
                } else {
                    // por defeito trata como "="
                    $applyWhere(function (Builder $q) use ($userId) {
                        $q->whereHas('destinatarios', function ($d) use ($userId) {
                            $d->where('users.id', $userId);
                        });
                    });
                }

                continue;
            }

            /* ======================================================
               ✅ CAMPOS NORMAIS (colunas diretas)
               ====================================================== */

            // contém
            if ($op === 'LIKE') {
                $value = (string)$value;
                if (!str_contains($value, '%')) $value = "%{$value}%";
            }

            // aplica condição normal
            $applyWhere(function (Builder $q) use ($field, $op, $value) {
                $q->where($field, $op, $value);
            });
        }
    });

    return $query;
}


    private static function applyOne(Builder $q, array $f, string $logica): void
    {
        $field = $f['field'] ?? null;
        $op    = strtoupper(trim((string)($f['operator'] ?? '=')));
        $value = $f['value'] ?? null;

        if (!$field) return;

        $op = match ($op) {
            '==' => '=',
            '!=' => '<>',
            default => $op,
        };

        $apply = function (callable $cb) use ($q, $logica) {
            if ($logica === 'OR') {
                $q->orWhere(function (Builder $inner) use ($cb) { $cb($inner); });
            } else {
                $q->where(function (Builder $inner) use ($cb) { $cb($inner); });
            }
        };

        $apply(function (Builder $inner) use ($field, $op, $value) {
            switch ($op) {
                case 'LIKE':
                case 'NOT LIKE':
                    $v = (string) $value;
                    if (!str_contains($v, '%')) $v = "%{$v}%";
                    $inner->where($field, $op, $v);
                    break;

                case 'IN':
                case 'NOT IN':
                    $arr = is_array($value) ? $value : array_map('trim', explode(',', (string)$value));
                    $arr = array_values(array_filter($arr, fn($x) => $x !== '' && $x !== null));
                    if (!$arr) return;

                    $op === 'IN'
                        ? $inner->whereIn($field, $arr)
                        : $inner->whereNotIn($field, $arr);
                    break;

                case 'BETWEEN':
                case 'NOT BETWEEN':
                    $vals = is_array($value) ? $value : array_map('trim', explode(',', (string)$value));
                    $a = $vals[0] ?? null;
                    $b = $vals[1] ?? null;
                    if ($a === null || $a === '' || $b === null || $b === '') return;

                    $op === 'BETWEEN'
                        ? $inner->whereBetween($field, [$a, $b])
                        : $inner->whereNotBetween($field, [$a, $b]);
                    break;

                case 'IS NULL':
                    $inner->whereNull($field);
                    break;

                case 'IS NOT NULL':
                    $inner->whereNotNull($field);
                    break;

                case '=':
                case '<>':
                case '>':
                case '>=':
                case '<':
                case '<=':
                    // permite 0, false, etc
                    if ($value === '' || $value === null) return;
                    $inner->where($field, $op, $value);
                    break;

                default:
                    // operador não suportado -> ignora com segurança
                    return;
            }
        });
    }
}
