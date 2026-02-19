<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Estado;
use App\Models\Setor;
use App\Models\TipoFormulario;
use App\Models\Departamento;
use App\Models\Origem;
use App\Models\Tipo;
use App\Models\Campanha;
use App\Models\SLA;
use App\Models\Grupo;
use App\Models\Aviso;
use App\Models\Chefia; // ✅
use App\Services\VistaRepo;
use App\Services\AuditService; // ✅ AUDIT (tudo menos emails)
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VistaController extends Controller
{
    public function index()
    {
        $vistas = VistaRepo::all();

        // ✅ AUDIT: listar vistas
        AuditService::log('vistas_index', null, [
            'total' => is_countable($vistas) ? count($vistas) : null,
        ]);

        return view('vistas.index', compact('vistas'));
    }

    public function create()
    {
        // ✅ AUDIT: abrir form create
        AuditService::log('vista_create_form');

        return view('vistas.create', [
            'estados'         => Estado::orderBy('name')->get(),
            'setores'         => Setor::orderBy('name')->get(),
            'tiposFormulario' => TipoFormulario::orderBy('name')->get(),
            'departamentos'   => Departamento::orderBy('name')->get(),
            'origens'         => Origem::orderBy('name')->get(),
            'tipos'           => Tipo::orderBy('name')->get(),
            'slas'            => SLA::orderBy('name')->get(),
            'avisos'          => Aviso::orderBy('name')->get(),
            'users'           => User::orderBy('name')->get(),
            'campanhas'       => Campanha::orderBy('name')->get(),
            'grupos'          => Grupo::orderBy('name')->get(),
            'chefias'         => Chefia::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $allowedFields = [
            'id',
            'name',
            'contact_client',
            'plate',
            'operator_email',
            'mensagem',

            'estado_id',
            'tipo_formulario_id',
            'sla_id',
            'campanha_id',
            'departamento_id',

            'chefia_id',

            'destinatario_user_id',
            'grupo_id',

            'abertura',

            'setor_id',
            'origem_id',
            'tipo_id',
            'aviso_id',
        ];

        $data = $request->validate([
            'nome'   => 'required|string|max:255',
            'logica' => 'required|in:AND,OR',
            'acesso' => 'required|in:all,department,specific',

            'conditions'            => 'nullable|array',
            'conditions.*.field'    => ['nullable', 'string', Rule::in($allowedFields)],
            'conditions.*.operator' => ['nullable', 'string', Rule::in(['=','!=','like','in','not in'])],
            'conditions.*.value'    => 'nullable',

            'departamentos'   => 'nullable|array',
            'departamentos.*' => 'integer|exists:departamentos,id',

            'users'   => 'nullable|array',
            'users.*' => 'integer|exists:users,id',
        ]);

        // coerência de acesso
        if ($data['acesso'] === 'department' && empty($request->input('departamentos', []))) {
            return back()->withErrors(['departamentos' => 'Seleciona pelo menos 1 departamento.'])->withInput();
        }
        if ($data['acesso'] === 'specific' && empty($request->input('users', []))) {
            return back()->withErrors(['users' => 'Seleciona pelo menos 1 utilizador.'])->withInput();
        }

        $conditions = $this->normalizeConditions($request->input('conditions', []));

        // ✅ AUDIT: pedido de criação (sem emails)
        AuditService::log('vista_store_request', null, [
            'nome' => $data['nome'],
            'logica' => $data['logica'],
            'acesso' => $data['acesso'],
            'conditions_count' => count($conditions),
            'departamentos' => $request->input('departamentos', []),
            'users' => $request->input('users', []),
        ]);

        $created = VistaRepo::create([
            'nome'          => $data['nome'],
            'logica'        => $data['logica'],
            'acesso'        => $data['acesso'],
            'filtros'       => $conditions,
            'departamentos' => $request->input('departamentos', []),
            'users'         => $request->input('users', []),
        ], auth()->id());

        // ✅ AUDIT: criada (se o repo devolver id)
        AuditService::log('vista_created', null, [
            'vista_id' => is_array($created) ? ($created['id'] ?? null) : (is_object($created) ? ($created->id ?? null) : null),
            'nome' => $data['nome'],
        ]);

        return redirect()->route('vistas.index')->with('success', 'Vista criada com sucesso.');
    }

    public function edit(string $vista)
    {
        $vistaData = VistaRepo::findOrFail($vista);

        // ✅ AUDIT: abrir form edit
        AuditService::log('vista_edit_form', null, [
            'vista_id' => is_array($vistaData) ? ($vistaData['id'] ?? $vista) : $vista,
        ]);

        return view('vistas.edit', [
            'vista'           => $vistaData,
            'estados'         => Estado::orderBy('name')->get(),
            'tiposFormulario' => TipoFormulario::orderBy('name')->get(),
            'setores'         => Setor::orderBy('name')->get(),
            'departamentos'   => Departamento::orderBy('name')->get(),
            'origens'         => Origem::orderBy('name')->get(),
            'tipos'           => Tipo::orderBy('name')->get(),
            'slas'            => SLA::orderBy('name')->get(),
            'avisos'          => Aviso::orderBy('name')->get(),
            'users'           => User::orderBy('name')->get(),
            'campanhas'       => Campanha::orderBy('name')->get(),
            'grupos'          => Grupo::orderBy('name')->get(),
            'chefias'         => Chefia::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, string $vista)
    {
        $allowedFields = [
            'id',
            'name',
            'contact_client',
            'plate',
            'operator_email',
            'mensagem',

            'estado_id',
            'tipo_formulario_id',
            'sla_id',
            'campanha_id',
            'departamento_id',

            'chefia_id',

            'destinatario_user_id',
            'grupo_id',

            'abertura',

            'setor_id',
            'origem_id',
            'tipo_id',
            'aviso_id',
        ];

        $data = $request->validate([
            'nome'   => 'required|string|max:255',
            'logica' => 'required|in:AND,OR',
            'acesso' => 'required|in:all,department,specific',

            'conditions'            => 'nullable|array',
            'conditions.*.field'    => ['nullable', 'string', Rule::in($allowedFields)],
            'conditions.*.operator' => ['nullable', 'string', Rule::in(['=','!=','like','in','not in'])],
            'conditions.*.value'    => 'nullable',

            'departamentos'   => 'nullable|array',
            'departamentos.*' => 'integer|exists:departamentos,id',

            'users'   => 'nullable|array',
            'users.*' => 'integer|exists:users,id',
        ]);

        // coerência de acesso
        if ($data['acesso'] === 'department' && empty($request->input('departamentos', []))) {
            return back()->withErrors(['departamentos' => 'Seleciona pelo menos 1 departamento.'])->withInput();
        }
        if ($data['acesso'] === 'specific' && empty($request->input('users', []))) {
            return back()->withErrors(['users' => 'Seleciona pelo menos 1 utilizador.'])->withInput();
        }

        $vistaAntes = VistaRepo::findOrFail($vista);

        $conditions = $this->normalizeConditions($request->input('conditions', []));

        // ✅ AUDIT: update pedido (antes/depois)
        AuditService::log('vista_update_request', null, [
            'vista_id' => is_array($vistaAntes) ? ($vistaAntes['id'] ?? $vista) : $vista,
        ], [
            'nome' => is_array($vistaAntes) ? ($vistaAntes['nome'] ?? null) : null,
            'logica' => is_array($vistaAntes) ? ($vistaAntes['logica'] ?? null) : null,
            'acesso' => is_array($vistaAntes) ? ($vistaAntes['acesso'] ?? null) : null,
            'conditions_count' => is_array($vistaAntes) ? (isset($vistaAntes['filtros']) && is_array($vistaAntes['filtros']) ? count($vistaAntes['filtros']) : null) : null,
        ], [
            'nome' => $data['nome'],
            'logica' => $data['logica'],
            'acesso' => $data['acesso'],
            'conditions_count' => count($conditions),
        ]);

        VistaRepo::update($vista, [
            'nome'          => $data['nome'],
            'logica'        => $data['logica'],
            'acesso'        => $data['acesso'],
            'filtros'       => $conditions,
            'departamentos' => $request->input('departamentos', []),
            'users'         => $request->input('users', []),
        ]);

        AuditService::log('vista_updated', null, [
            'vista_id' => is_array($vistaAntes) ? ($vistaAntes['id'] ?? $vista) : $vista,
            'nome' => $data['nome'],
        ]);

        return redirect()->route('vistas.index')->with('success', 'Vista atualizada com sucesso.');
    }

    public function destroy(string $vista)
    {
        $vistaAntes = VistaRepo::findOrFail($vista);

        // ✅ AUDIT: apagar vista
        AuditService::log('vista_destroy', null, [
            'vista_id' => is_array($vistaAntes) ? ($vistaAntes['id'] ?? $vista) : $vista,
            'nome' => is_array($vistaAntes) ? ($vistaAntes['nome'] ?? null) : null,
        ]);

        VistaRepo::delete($vista);

        return redirect()->route('vistas.index')->with('success', 'Vista eliminada.');
    }

    /**
     * Normaliza e filtra conditions:
     * - suporta in / not in com value array
     * - ignora linhas incompletas
     */
    private function normalizeConditions(array $raw): array
    {
        $conditions = [];

        foreach ($raw as $cond) {
            $field = $cond['field'] ?? null;
            $op    = strtolower(trim((string)($cond['operator'] ?? '')));
            $value = $cond['value'] ?? null;

            if (!$field || !$op) continue;

            if ($op === 'in' || $op === 'not in') {
                if (!is_array($value)) continue;

                $arr = array_values(array_filter($value, fn($v) => $v !== '' && $v !== null));
                if (!$arr) continue;

                $conditions[] = [
                    'field'    => $field,
                    'operator' => $op,
                    'value'    => $arr,
                ];
                continue;
            }

            if (!isset($value) || $value === '') continue;

            $conditions[] = [
                'field'    => $field,
                'operator' => $op,
                'value'    => $value,
            ];
        }

        return $conditions;
    }
}
