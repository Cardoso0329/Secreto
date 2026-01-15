<?php

namespace App\Queries;

use Illuminate\Database\Eloquent\Builder;

class RecadoQuery
{
    public static function applyFilters(Builder $query, $filtros, string $logica = 'AND'): Builder
    {
        $filtros = is_array($filtros) ? array_values($filtros) : [];
        $logica  = strtoupper($logica) === 'OR' ? 'OR' : 'AND';

        // limpa inválidos (field seguro)
        $filtros = array_values(array_filter($filtros, function ($f) {
            if (!is_array($f)) return false;
            $field = $f['field'] ?? null;
            if (!$field || !is_string($field)) return false;
            return (bool) preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $field);
        }));

        if (empty($filtros)) return $query;

        $query->where(function (Builder $group) use ($filtros, $logica) {

            $applied = 0;

            $applyWhere = function (callable $cb) use ($group, $logica, &$applied) {
                if ($logica === 'OR') {
                    if ($applied === 0) $group->where($cb);
                    else $group->orWhere($cb);
                } else {
                    $group->where($cb);
                }
                $applied++;
            };

            foreach ($filtros as $f) {
                $field = $f['field'] ?? null;
                $opRaw = $f['operator'] ?? '=';
                $value = $f['value'] ?? null;

                $op = strtolower(trim((string)$opRaw));
                $op = match ($op) {
                    '!=' => '<>',
                    'like' => 'like',
                    '=' => '=',
                    '<>' => '<>',
                    'in' => 'in',
                    'not in' => 'not in',
                    default => $op,
                };

                // IN / NOT IN: value tem de ser array com pelo menos 1
                if ($op === 'in' || $op === 'not in') {
                    if (!is_array($value)) continue;
                    $arr = array_values(array_filter($value, fn($v) => $v !== '' && $v !== null));
                    if (!$arr) continue;

                    // destinatário (relação)
                    if ($field === 'destinatario_user_id') {
                        if ($op === 'not in') {
                            $applyWhere(function (Builder $q) use ($arr) {
                                $q->whereDoesntHave('destinatarios', function ($d) use ($arr) {
                                    $d->whereIn('users.id', $arr);
                                });
                            });
                        } else {
                            $applyWhere(function (Builder $q) use ($arr) {
                                $q->whereHas('destinatarios', function ($d) use ($arr) {
                                    $d->whereIn('users.id', $arr);
                                });
                            });
                        }
                        continue;
                    }

                    // colunas diretas
                    if ($op === 'not in') {
                        $applyWhere(function (Builder $q) use ($field, $arr) {
                            $q->whereNotIn($field, $arr);
                        });
                    } else {
                        $applyWhere(function (Builder $q) use ($field, $arr) {
                            $q->whereIn($field, $arr);
                        });
                    }

                    continue;
                }

                // vazios para operadores normais
                if ($value === '' || $value === null) continue;

                // DESTINATÁRIO (relação): suporta "=" e "<>"
                if ($field === 'destinatario_user_id') {
                    $userId = $value;

                    if ($op === '<>') {
                        $applyWhere(function (Builder $q) use ($userId) {
                            $q->whereDoesntHave('destinatarios', function ($d) use ($userId) {
                                $d->where('users.id', $userId);
                            });
                        });
                    } else {
                        $applyWhere(function (Builder $q) use ($userId) {
                            $q->whereHas('destinatarios', function ($d) use ($userId) {
                                $d->where('users.id', $userId);
                            });
                        });
                    }

                    continue;
                }

                // LIKE
                if ($op === 'like') {
                    $v = (string)$value;
                    if (!str_contains($v, '%')) $v = "%{$v}%";

                    $applyWhere(function (Builder $q) use ($field, $v) {
                        $q->where($field, 'LIKE', $v);
                    });

                    continue;
                }

                // operadores simples
                $applyWhere(function (Builder $q) use ($field, $op, $value) {
                    $q->where($field, $op, $value);
                });
            }
        });

        return $query;
    }
}
