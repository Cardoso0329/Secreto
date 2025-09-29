<?php

namespace App\Imports;

use App\Models\Recado;
use App\Models\User;
use App\Models\Grupo;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class RecadosImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        $row = array_map('trim', $row);

        // Campos obrigatórios
        $required = ['name', 'sla_id', 'tipo_id', 'setor_id', 'departamento_id', 'mensagem'];
        foreach ($required as $field) {
            if (empty($row[$field])) {
                Log::warning("Linha ignorada: campo obrigatório {$field} vazio", $row);
                return null;
            }
        }

        // Verificação de duplicados (todos os campos importantes)
        $uniqueFields = [
            'name', 'contact_client', 'plate', 'operator_email',
            'tipo_formulario_id', 'estado_id', 'sla_id', 'tipo_id',
            'origem_id', 'setor_id', 'departamento_id', 'aviso_id',
            'mensagem', 'wip', 'abertura'
        ];

        $query = Recado::query();
        foreach ($uniqueFields as $field) {
            $query->where($field, $row[$field] ?? null);
        }

        if ($query->exists()) {
            Log::info("Recado já existe com todos os campos iguais, ignorando importação", $row);
            return null; // Não duplica se tudo for igual
        }

        // Cria recado
        $recado = Recado::create([
            'name' => $row['name'],
            'contact_client' => $row['contact_client'] ?? null,
            'plate' => $row['plate'] ?? null,
            'operator_email' => $row['operator_email'] ?? auth()->user()->email,
            'tipo_formulario_id' => $row['tipo_formulario_id'] ?? 1,
            'estado_id' => $row['estado_id'] ?? 1,
            'sla_id' => $row['sla_id'],
            'tipo_id' => $row['tipo_id'],
            'origem_id' => $row['origem_id'] ?? null,
            'setor_id' => $row['setor_id'],
            'departamento_id' => $row['departamento_id'],
            'aviso_id' => $row['aviso_id'] ?? null,
            'mensagem' => $row['mensagem'],
            'wip' => $row['wip'] ?? null,
            'abertura' => $row['abertura'] ?? now(),
        ]);

        // ======================
        // Destinatários (opcional)
        // ======================

        // Usuários
        if (!empty($row['destinatarios_users'])) {
            $userIds = array_filter(array_map('trim', explode(',', $row['destinatarios_users'])));
            $recado->destinatarios()->sync($userIds);
        }

        // Grupos
        if (!empty($row['destinatarios_grupos'])) {
            $grupoIds = array_filter(array_map('trim', explode(',', $row['destinatarios_grupos'])));
            $allUserIds = [];
            foreach ($grupoIds as $gid) {
                $grupo = Grupo::find($gid);
                if ($grupo) {
                    $allUserIds = array_merge($allUserIds, $grupo->users->pluck('id')->toArray());
                }
            }
            $allUserIds = array_unique($allUserIds); // Remove duplicados
            if (!empty($allUserIds)) {
                $recado->destinatarios()->syncWithoutDetaching($allUserIds);
            }
        }

        // Destinatários livres
        if (!empty($row['destinatario_livre'])) {
            $livres = array_filter(array_map('trim', explode(',', $row['destinatario_livre'])));
            $recado->destinatario_livre = json_encode($livres);
            $recado->save();
        }

        return $recado;
    }

    // Chunk para importação grande
    public function chunkSize(): int
    {
        return 1000;
    }
}
