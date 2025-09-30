<?php

namespace App\Imports;

use App\Models\Recado;
use App\Models\User;
use App\Models\Grupo;
use App\Models\Setor;
use App\Models\Departamento;
use App\Models\Aviso;
use App\Models\Estado;
use App\Models\SLA;
use App\Models\Tipo;
use App\Models\Origem;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class RecadosImport implements ToModel, WithHeadingRow, WithChunkReading
{
    public function model(array $row)
    {
        $row = array_map('trim', $row);

        // Campos obrigatórios (com nomes, não IDs)
        $required = ['name', 'sla', 'tipo', 'setor', 'departamento', 'mensagem'];
        foreach ($required as $field) {
            if (empty($row[$field])) {
                Log::warning("Linha ignorada: campo obrigatório {$field} vazio", $row);
                return null;
            }
        }

        // Normaliza tabelas relacionadas (cria se não existir)
        $setor = !empty($row['setor']) ? Setor::firstOrCreate(['name' => $row['setor']]) : null;
        $departamento = !empty($row['departamento']) ? Departamento::firstOrCreate(['name' => $row['departamento']]) : null;
        $aviso = !empty($row['aviso']) ? Aviso::firstOrCreate(['name' => $row['aviso']]) : null;
        $estado = !empty($row['estado']) ? Estado::firstOrCreate(['name' => $row['estado']]) : null;
        $sla = !empty($row['sla']) ? SLA::firstOrCreate(['name' => $row['sla']]) : null;
        $tipo = !empty($row['tipo']) ? Tipo::firstOrCreate(['name' => $row['tipo']]) : null;
        $origem = !empty($row['origem']) ? Origem::firstOrCreate(['name' => $row['origem']]) : null;

        // Verificação de duplicados com todos os campos iguais
        $duplicateQuery = Recado::where('name', $row['name'])
            ->where('contact_client', $row['contact_client'] ?? null)
            ->where('plate', $row['plate'] ?? null)
            ->where('operator_email', $row['operator_email'] ?? auth()->user()->email)
            ->where('tipo_formulario_id', $row['tipo_formulario_id'] ?? 1)
            ->where('estado_id', $estado?->id ?? 1)
            ->where('sla_id', $sla?->id)
            ->where('tipo_id', $tipo?->id)
            ->where('origem_id', $origem?->id)
            ->where('setor_id', $setor?->id)
            ->where('departamento_id', $departamento?->id)
            ->where('aviso_id', $aviso?->id)
            ->where('mensagem', $row['mensagem'])
            ->where('wip', $row['wip'] ?? null)
            ->where('abertura', $row['abertura'] ?? now());

        if ($duplicateQuery->exists()) {
            Log::info("Recado já existe com todos os campos iguais, ignorando importação", $row);
            return null;
        }

        // Cria recado
        $recado = Recado::create([
            'name' => $row['name'],
            'contact_client' => $row['contact_client'] ?? null,
            'plate' => $row['plate'] ?? null,
            'operator_email' => $row['operator_email'] ?? auth()->user()->email,
            'tipo_formulario_id' => $row['tipo_formulario_id'] ?? 1,
            'estado_id' => $estado?->id ?? 1,
            'sla_id' => $sla?->id,
            'tipo_id' => $tipo?->id,
            'origem_id' => $origem?->id,
            'setor_id' => $setor?->id,
            'departamento_id' => $departamento?->id,
            'aviso_id' => $aviso?->id,
            'mensagem' => $row['mensagem'],
            'wip' => $row['wip'] ?? null,
            'abertura' => $row['abertura'] ?? now(),
        ]);

        // ======================
        // Destinatários (opcional)
        // ======================

        // Usuários (IDs ou emails separados por vírgula)
        if (!empty($row['destinatarios_users'])) {
            $users = array_filter(array_map('trim', explode(',', $row['destinatarios_users'])));
            $userIds = [];
            foreach ($users as $u) {
                if (is_numeric($u)) {
                    $user = User::find($u);
                } else {
                    $user = User::firstOrCreate(['email' => $u], ['name' => $u]);
                }
                if ($user) {
                    $userIds[] = $user->id;
                }
            }
            if (!empty($userIds)) {
                $recado->destinatarios()->sync($userIds);
            }
        }

        // Grupos (IDs ou nomes separados por vírgula)
        if (!empty($row['destinatarios_grupos'])) {
            $grupos = array_filter(array_map('trim', explode(',', $row['destinatarios_grupos'])));
            $allUserIds = [];
            foreach ($grupos as $g) {
                $grupo = is_numeric($g) ? Grupo::find($g) : Grupo::firstOrCreate(['name' => $g]);
                if ($grupo) {
                    $allUserIds = array_merge($allUserIds, $grupo->users->pluck('id')->toArray());
                }
            }
            $allUserIds = array_unique($allUserIds);
            if (!empty($allUserIds)) {
                $recado->destinatarios()->syncWithoutDetaching($allUserIds);
            }
        }

        // Destinatários livres (texto separado por vírgula)
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
