<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VistaRepo
{
    private const PATH = 'vistas.json'; // storage/app/vistas.json

    public static function all(): array
    {
        if (!Storage::disk('local')->exists(self::PATH)) return [];
        $data = json_decode(Storage::disk('local')->get(self::PATH), true);
        return is_array($data) ? $data : [];
    }

    private static function save(array $vistas): void
    {
        Storage::disk('local')->put(
            self::PATH,
            json_encode(array_values($vistas), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );
    }

    public static function findOrFail(string $id): array
    {
        foreach (self::all() as $v) {
            if (($v['id'] ?? null) === $id) return $v;
        }
        abort(404, 'Vista não encontrada.');
    }

    public static function create(array $data, int $adminId): array
    {
        $vistas = self::all();

        $vista = [
            'id'            => 'v_' . Str::random(10),
            'nome'          => $data['nome'],
            'logica'        => $data['logica'] ?? 'AND',
            'acesso'        => $data['acesso'] ?? 'all',
            'filtros'       => $data['filtros'] ?? [],
            'departamentos' => $data['departamentos'] ?? [],
            'users'         => $data['users'] ?? [],
            'created_by'    => $adminId,
            'created_at'    => now()->toDateTimeString(),
            'updated_at'    => now()->toDateTimeString(),
        ];

        $vistas[] = $vista;
        self::save($vistas);

        return $vista;
    }

    public static function update(string $id, array $data): array
    {
        $vistas = self::all();

        foreach ($vistas as &$v) {
            if (($v['id'] ?? null) === $id) {
                $v['nome']          = $data['nome'] ?? $v['nome'];
                $v['logica']        = $data['logica'] ?? $v['logica'];
                $v['acesso']        = $data['acesso'] ?? $v['acesso'];
                $v['filtros']       = $data['filtros'] ?? $v['filtros'];
                $v['departamentos'] = $data['departamentos'] ?? $v['departamentos'];
                $v['users']         = $data['users'] ?? $v['users'];
                $v['updated_at']    = now()->toDateTimeString();

                self::save($vistas);
                return $v;
            }
        }

        abort(404, 'Vista não encontrada.');
    }

    public static function delete(string $id): void
    {
        $vistas = array_values(array_filter(self::all(), fn($v) => ($v['id'] ?? null) !== $id));
        self::save($vistas);
    }
}
