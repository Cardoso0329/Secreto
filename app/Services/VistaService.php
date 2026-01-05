<?php

namespace App\Services;

class VistaService
{
    /**
     * Retorna as vistas visíveis para um user.
     * - all: todos
     * - department: se user pertence a algum departamento da vista
     * - specific: se user_id está em users[]
     */
    public static function visiveisPara($user): array
    {
        // ✅ se vier null, não rebenta
        if (!$user) {
            // opção A (recomendada): ninguém autenticado -> nenhuma vista
            return [];

            // opção B: se quiseres mostrar vistas públicas mesmo sem login:
            // return array_values(array_filter(VistaRepo::all(), fn($v) => ($v['acesso'] ?? 'all') === 'all'));
        }

        $userId = (int)($user->id ?? 0);

        // tenta descobrir departamento(s) do user:
        $userDeptIds = [];

        // Caso A: coluna direta users.departamento_id
        if (!empty($user->departamento_id)) {
            $userDeptIds[] = (int) $user->departamento_id;
        }

        // Caso B: relação departamentos() (many-to-many)
        if (is_object($user) && method_exists($user, 'departamentos')) {
            try {
                $ids = $user->departamentos()->pluck('departamentos.id')->all();
                foreach ($ids as $id) {
                    if ($id !== null && $id !== '') $userDeptIds[] = (int) $id;
                }
            } catch (\Throwable $e) {
                // ignora se a relação não estiver pronta
            }
        }

        $userDeptIds = array_values(array_unique($userDeptIds));

        $out = [];
        foreach (VistaRepo::all() as $v) {
            $acesso = $v['acesso'] ?? 'all';

            if ($acesso === 'all') {
                $out[] = $v;
                continue;
            }

            if ($acesso === 'specific') {
                $allowed = array_map('intval', is_array($v['users'] ?? null) ? $v['users'] : []);
                if (in_array($userId, $allowed, true)) $out[] = $v;
                continue;
            }

            if ($acesso === 'department') {
                $vistaDepts = array_map('intval', is_array($v['departamentos'] ?? null) ? $v['departamentos'] : []);
                if ($vistaDepts && array_intersect($vistaDepts, $userDeptIds)) {
                    $out[] = $v;
                }
                continue;
            }

            // (opcional) owner, se quiseres suportar:
            if ($acesso === 'owner') {
                if ((int)($v['created_by'] ?? 0) === $userId) $out[] = $v;
                continue;
            }
        }

        usort($out, fn($a, $b) => strcmp($a['nome'] ?? '', $b['nome'] ?? ''));
        return $out;
    }
}
