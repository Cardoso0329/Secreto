<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Destinatario;
use App\Models\SLA;
use App\Models\Departamento;
use App\Models\Origem;
use App\Models\Aviso;
use App\Models\Estado;
use App\Models\Setor;
use App\Models\Tipo;
use App\Models\Cargo;
use App\Models\Campanha;
use App\Models\User;
use App\Models\CampanhaDepartamento;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\Recado;
use App\Models\Vista;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RecadosExport;
use App\Imports\RecadosImport;
use App\Models\TipoFormulario;
use App\Services\AuditService; // ✅ AUDIT (tudo menos emails)
use Carbon\Carbon;

class PainelController extends Controller
{
    public function index()
    {
        $destinatarios = Destinatario::all();
        $slas = SLA::all();
        $departamentos = Departamento::all();
        $origens = Origem::all();
        $setores = Setor::all();
        $estados = Estado::all();
        $avisos = Aviso::all();
        $tipos = Tipo::all();

        AuditService::log('painel_index', null, [
            'totais' => [
                'destinatarios' => $destinatarios->count(),
                'slas' => $slas->count(),
                'departamentos' => $departamentos->count(),
                'origens' => $origens->count(),
                'setores' => $setores->count(),
                'estados' => $estados->count(),
                'avisos' => $avisos->count(),
                'tipos' => $tipos->count(),
            ],
        ]);

        return view('painel.index', [
            'destinatarios' => $destinatarios,
            'slas' => $slas,
            'departamentos' => $departamentos,
            'origens' => $origens,
            'setores' => $setores,
            'estados' => $estados,
            'avisos' => $avisos,
            'tipos' => $tipos,
        ]);
    }

    public function configuracoes(Request $request)
    {
        AuditService::log('configuracoes_index', null, [
            'filters' => $request->except(['password', '_token']),
        ]);

        $recados = Recado::query()
            ->when($request->id, fn($q) => $q->where('id', $request->id))
            ->when($request->contact_client, fn($q) => $q->where('contact_client', 'like', "%{$request->contact_client}%"))
            ->when($request->plate, fn($q) => $q->where('plate', $request->plate))
            ->when($request->estado_id, fn($q) => $q->where('estado_id', $request->estado_id))
            ->when($request->tipo_formulario_id, fn($q) => $q->where('tipo_formulario_id', $request->tipo_formulario_id))
            ->orderBy('id', 'desc')
            ->paginate(10);

        $estados = Estado::all();
        $tiposFormulario = TipoFormulario::all();

        if ($request->vista_id) {
            $vista = Vista::findOrFail($request->vista_id);

            AuditService::log('configuracoes_aplicar_vista', null, [
                'vista_id' => $vista->id,
                'logica' => $vista->logica,
            ]);

            // ⚠️ Nota: isto não altera o paginate (porque já paginaste).
            // Se quiseres aplicar vista "a sério", tens de aplicar na query antes do paginate.
            $this->aplicarVista($recados, $vista);
        }

        AuditService::log('configuracoes_resultados', null, [
            'page_total' => $recados->count(),
            'vista_id' => $request->vista_id ?: null,
        ]);

        return view('configuracoes.index', compact('recados', 'estados', 'tiposFormulario'));
    }

    public function exportFiltered(Request $request)
    {
        $query = Recado::query()
            ->when($request->id, fn($q) => $q->where('id', $request->id))
            ->when($request->contact_client, fn($q) => $q->where('contact_client', 'like', "%{$request->contact_client}%"))
            ->when($request->plate, fn($q) => $q->where('plate', $request->plate))
            ->when($request->estado_id, fn($q) => $q->where('estado_id', $request->estado_id))
            ->when($request->tipo_formulario_id, fn($q) => $q->where('tipo_formulario_id', $request->tipo_formulario_id));

        $recados = $query->get();

        AuditService::log('painel_export_filtered', null, [
            'total' => $recados->count(),
            'filters' => $request->except(['password', '_token']),
        ]);

        return Excel::download(new RecadosExport($recados), 'recados_filtrados.xlsx');
    }

    public function importar(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        AuditService::log('painel_import_recados', null, [
            'original_name' => $request->file('file')->getClientOriginalName(),
            'mime' => $request->file('file')->getClientMimeType(),
            'size' => $request->file('file')->getSize(),
        ]);

        Excel::import(new RecadosImport, $request->file('file'));

        AuditService::log('painel_import_recados_done');

        return redirect()->back()->with('success', 'Recados importados com sucesso!');
    }

    public function export()
    {
        $recados = Recado::with(['estado','tipoFormulario'])->get();

        AuditService::log('painel_export_all', null, [
            'total' => $recados->count(),
        ]);

        return Excel::download(new RecadosExport($recados), 'recados_todos.xlsx');
    }

    public function indexConfiguracoes(Request $request)
    {
        AuditService::log('configuracoes_index_alt', null, [
            'filters' => $request->except(['password', '_token']),
        ]);

        $recados = Recado::with(['estado', 'tipoFormulario'])
            ->filter($request->all())
            ->paginate(10);

        $estados = Estado::all();
        $tiposFormulario = TipoFormulario::all();

        AuditService::log('configuracoes_index_alt_resultados', null, [
            'page_total' => $recados->count(),
        ]);

        return view('configuracoes.index', compact('recados', 'estados', 'tiposFormulario'));
    }

    private function aplicarVista($query, $vista)
    {
        $conditions = $vista->filtros['conditions'] ?? [];

        AuditService::log('vista_apply', null, [
            'vista_id' => $vista->id ?? null,
            'logica' => $vista->logica ?? null,
            'conditions_count' => count($conditions),
        ]);

        $query->where(function ($q) use ($conditions, $vista) {
            foreach ($conditions as $cond) {
                $method = ($vista->logica ?? 'AND') === 'AND' ? 'where' : 'orWhere';
                $q->$method($cond['field'], $cond['operator'], $cond['value']);
            }
        });
    }

    /* ============================================================
     | ✅ ANONIMIZAÇÃO (ATUALIZADA)
     | Só recados no estado "Tratado"
     | name / contact_client / mensagem -> "Anonimizado"
     | marca anonymized_at para não repetir
     ============================================================ */

    private function ensureAdmin(): void
    {
        $user = auth()->user();
        if (!$user) abort(403);
        if ((int)($user->cargo_id ?? 0) !== 1 && ($user->cargo?->name ?? null) !== 'admin') abort(403);
    }

    private function tratadoEstadoId(): ?int
    {
        return Estado::whereRaw('LOWER(name) = ?', ['tratado'])->value('id');
    }

    private function aplicarAnonimizacaoEmRecados($recados): int
    {
        foreach ($recados as $r) {
            $r->name = 'Anonimizado';
            $r->contact_client = 'Anonimizado';
            $r->mensagem = 'Anonimizado';
            $r->anonymized_at = now();
            $r->save();
        }
        return $recados->count();
    }

    public function anonimizarRecados3Meses(Request $request)
    {
        $this->ensureAdmin();

        $estadoTratadoId = $this->tratadoEstadoId();
        if (!$estadoTratadoId) {
            return back()->with('error', 'Estado "Tratado" não encontrado.');
        }

        $cutoff = now()->subMonths(3)->startOfDay();

        $count = DB::transaction(function () use ($cutoff, $estadoTratadoId) {

            $recados = Recado::query()
                ->where('estado_id', $estadoTratadoId)
                ->whereNull('anonymized_at')
                ->where(function ($q) use ($cutoff) {
                    $q->whereNotNull('abertura')->where('abertura', '<=', $cutoff)
                      ->orWhereNull('abertura')->where('created_at', '<=', $cutoff);
                })
                ->get(['id','name','contact_client','mensagem','anonymized_at']);

            return $this->aplicarAnonimizacaoEmRecados($recados);
        });

        AuditService::log('recados_anonimizar_3meses', null, [
            'cutoff' => $cutoff->toDateString(),
            'estado' => 'Tratado',
            'total' => $count,
        ]);

        return back()->with('success', "Anonimização concluída: {$count} recado(s) anonimizados (>= 3 meses, Tratado).");
    }

    public function anonimizarRecadosManual(Request $request)
    {
        $this->ensureAdmin();

        $estadoTratadoId = $this->tratadoEstadoId();
        if (!$estadoTratadoId) {
            return back()->with('error', 'Estado "Tratado" não encontrado.');
        }

        $data = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to'   => ['required', 'date', 'after_or_equal:date_from'],
        ]);

        $from = !empty($data['date_from']) ? Carbon::parse($data['date_from'])->startOfDay() : null;
        $to   = Carbon::parse($data['date_to'])->endOfDay();

        $count = DB::transaction(function () use ($from, $to, $estadoTratadoId) {

            $recados = Recado::query()
                ->where('estado_id', $estadoTratadoId)
                ->whereNull('anonymized_at')
                ->where(function ($w) use ($from, $to) {
                    $w->where(function ($a) use ($from, $to) {
                        $a->whereNotNull('abertura');
                        if ($from) $a->where('abertura', '>=', $from);
                        $a->where('abertura', '<=', $to);
                    })->orWhere(function ($c) use ($from, $to) {
                        $c->whereNull('abertura');
                        if ($from) $c->where('created_at', '>=', $from);
                        $c->where('created_at', '<=', $to);
                    });
                })
                ->get(['id','name','contact_client','mensagem','anonymized_at']);

            return $this->aplicarAnonimizacaoEmRecados($recados);
        });

        AuditService::log('recados_anonimizar_manual', null, [
            'from' => $from?->toDateString(),
            'to' => $to->toDateString(),
            'estado' => 'Tratado',
            'total' => $count,
        ]);

        return back()->with('success', "Anonimização concluída: {$count} recado(s) anonimizados no período (Tratado).");
    }
}
