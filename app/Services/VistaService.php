<?php

namespace App\Services;


use App\Services\VistaRepo;

class VistaService
{
    public static function visiveisPara($user): array
    {
        if (!$user) {
            return [];
        }

        $userId = (int)($user->id ?? 0);

        $userDeptIds = [];

        if (!empty($user->departamento_id)) {
            $userDeptIds[] = (int) $user->departamento_id;
        }

        if (is_object($user) && method_exists($user, 'departamentos')) {
            try {
                $ids = $user->departamentos()->pluck('departamentos.id')->all();
                foreach ($ids as $id) {
                    if ($id !== null && $id !== '') $userDeptIds[] = (int) $id;
                }
            } catch (\Throwable $e) {
                // ignora
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

            if ($acesso === 'owner') {
                if ((int)($v['created_by'] ?? 0) === $userId) $out[] = $v;
                continue;
            }
        }

        usort($out, fn($a, $b) => strcmp($a['nome'] ?? '', $b['nome'] ?? ''));
        return $out;
    }
}
