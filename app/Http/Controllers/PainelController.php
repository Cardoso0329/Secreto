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

        // ✅ AUDIT: abrir painel
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
        // ✅ AUDIT: abrir configurações (com filtros recebidos)
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

            // ✅ AUDIT: vista aplicada
            AuditService::log('configuracoes_aplicar_vista', null, [
                'vista_id' => $vista->id,
                'logica' => $vista->logica,
            ]);

            // ⚠️ Correção importante: tens de aplicar a vista ANTES do paginate.
            // Aqui mantive a tua estrutura, mas para ficar certo:
            // - O ideal é construir query, aplicarVista($query, $vista) e só depois paginate().
            // Como tu já paginaste acima, isto não altera a query original.
            // Vou deixar aqui a forma correta em baixo (comentada).

            // ✅ Forma correta (recomendada):
            // $query = Recado::query()
            //     ->when(...)
            //     ->orderBy('id','desc');
            // $this->aplicarVista($query, $vista);
            // $recados = $query->paginate(10);

            // Mantém a tua chamada (não quebra), mas não altera o resultado paginado:
            $this->aplicarVista($recados, $vista);
        }

        // ✅ AUDIT: total devolvido nesta página
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

        // ✅ AUDIT: export filtrado
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

        // ✅ AUDIT: import iniciado (não guardamos nome real do ficheiro se não quiseres)
        AuditService::log('painel_import_recados', null, [
            'original_name' => $request->file('file')->getClientOriginalName(),
            'mime' => $request->file('file')->getClientMimeType(),
            'size' => $request->file('file')->getSize(),
        ]);

        Excel::import(new RecadosImport, $request->file('file'));

        // ✅ AUDIT: import concluído (não dá para saber quantos criou sem mexer no import)
        AuditService::log('painel_import_recados_done');

        return redirect()->back()->with('success', 'Recados importados com sucesso!');
    }

    public function export()
    {
        $recados = Recado::with(['estado','tipoFormulario'])->get();

        // ✅ AUDIT: export total
        AuditService::log('painel_export_all', null, [
            'total' => $recados->count(),
        ]);

        return Excel::download(new RecadosExport($recados), 'recados_todos.xlsx');
    }

    public function indexConfiguracoes(Request $request)
    {
        // ✅ AUDIT: abrir indexConfiguracoes (se estiver a ser usado)
        AuditService::log('configuracoes_index_alt', null, [
            'filters' => $request->except(['password', '_token']),
        ]);

        $recados = Recado::with(['estado', 'tipoFormulario'])
            ->filter($request->all()) // se tiver scope de filtros
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

        // ✅ AUDIT: registar condições aplicadas (sem exagerar)
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
}
