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
        // ============================
        // Normalização dos cabeçalhos
        // ============================
        $normalized = [];
        foreach ($row as $key => $value) {
            $key = strtolower(trim($key));
            $key = str_replace(
                [' ', '-', 'á', 'ã', 'â', 'à', 'é', 'ê', 'í', 'ó', 'ô', 'õ', 'ú', 'ç'],
                ['_', '_', 'a', 'a', 'a', 'a', 'e', 'e', 'i', 'o', 'o', 'o', 'u', 'c'],
                $key
            );
            $normalized[$key] = trim($value);
        }
        $row = $normalized;

        // ============================
        // Campos obrigatórios mínimos
        // ============================
        $required = ['cliente', 'mensagem'];
        foreach ($required as $field) {
            if (empty($row[$field])) {
                Log::warning("Linha ignorada: campo obrigatório {$field} vazio", $row);
                return null;
            }
        }

        // ============================
        // Normalização de tabelas relacionadas
        // ============================
        $setor = !empty($row['setor']) ? Setor::firstOrCreate(['name' => $row['setor']]) : null;
        $departamento = !empty($row['departamento']) ? Departamento::firstOrCreate(['name' => $row['departamento']]) : null;
        $aviso = !empty($row['aviso']) ? Aviso::firstOrCreate(['name' => $row['aviso']]) : null;
        $estado = !empty($row['estado']) ? Estado::firstOrCreate(['name' => $row['estado']]) : Estado::firstOrCreate(['name' => 'Pendente']);
        $sla = !empty($row['sla']) ? SLA::firstOrCreate(['name' => $row['sla']]) : null;
        $tipo = !empty($row['tipo']) ? Tipo::firstOrCreate(['name' => $row['tipo']]) : null;
        $origem = !empty($row['origem']) ? Origem::firstOrCreate(['name' => $row['origem']]) : null;

        // ============================
        // Evita duplicados idênticos
        // ============================
        $duplicate = Recado::where('contact_client', $row['cliente'])
            ->where('plate', $row['matricula'] ?? null)
            ->where('mensagem', $row['mensagem'])
            ->where('estado_id', $estado?->id)
            ->where('tipo_id', $tipo?->id)
            ->where('setor_id', $setor?->id)
            ->where('departamento_id', $departamento?->id)
            ->where('sla_id', $sla?->id)
            ->where('origem_id', $origem?->id)
            ->exists();

        if ($duplicate) {
            Log::info("Recado duplicado ignorado", $row);
            return null;
        }

        // ============================
        // Cria o Recado
        // ============================
       $recado = Recado::create([
    'name'               => $row['cliente'],
    'contact_client'     => $row['cliente'] ?? null,
    'plate'              => $row['matricula'] ?? null,
    'operator_email'     => $row['email_operador'] ?? 'import@system.local',
    'tipo_formulario_id' => 1, // ou ajusta conforme necessário
    'estado_id'          => $estado?->id,
    'sla_id'             => $sla?->id,
    'tipo_id'            => $tipo?->id,
    'origem_id'          => $origem?->id,
    'setor_id'           => $setor?->id,
    'departamento_id'    => $departamento?->id,
    'aviso_id'           => $aviso?->id,
    'mensagem'           => $row['mensagem'],
    'wip'                => $row['wip'] ?? null,
    'ficheiro'           => $row['ficheiro'] ?? null,
    'abertura'           => !empty($row['data_abertura']) ? $row['data_abertura'] : now(),
]);


        // ============================
        // Destinatários Users
        // ============================
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

        // ============================
        // Destinatários Grupos
        // ============================
        if (!empty($row['destinatarios_grupos'])) {
            $grupos = array_filter(array_map('trim', explode(',', $row['destinatarios_grupos'])));
            $allUserIds = [];
            foreach ($grupos as $g) {
                $grupo = is_numeric($g) ? Grupo::find($g) : Grupo::firstOrCreate(['name' => $g]);
                if ($grupo && $grupo->users) {
                    $allUserIds = array_merge($allUserIds, $grupo->users->pluck('id')->toArray());
                }
            }
            $allUserIds = array_unique($allUserIds);
            if (!empty($allUserIds)) {
                $recado->destinatarios()->syncWithoutDetaching($allUserIds);
            }
        }

        // ============================
        // Destinatário Livre
        // ============================
        if (!empty($row['destinatario_livre'])) {
            $livres = array_filter(array_map('trim', explode(',', $row['destinatario_livre'])));
            $recado->destinatario_livre = json_encode($livres);
            $recado->save();
        }

        return $recado;
    }

    // ============================
    // Chunk para grandes ficheiros
    // ============================
    public function chunkSize(): int
    {
        return 1000;
    }
}
