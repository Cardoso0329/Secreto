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
use App\Models\Aviso;
use App\Services\VistaRepo;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VistaController extends Controller
{
    public function index()
    {
        $vistas = VistaRepo::all();
        return view('vistas.index', compact('vistas'));
    }

    public function create()
    {
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
            'destinatario_user_id',
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
            'conditions.*.value'    => 'nullable', // pode ser string ou array (para IN)

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

        VistaRepo::create([
            'nome'          => $data['nome'],
            'logica'        => $data['logica'],
            'acesso'        => $data['acesso'],
            'filtros'       => $conditions,
            'departamentos' => $request->input('departamentos', []),
            'users'         => $request->input('users', []),
        ], auth()->id());

        return redirect()->route('vistas.index')->with('success', 'Vista criada com sucesso.');
    }

    public function edit(string $vista)
    {
        $vistaData = VistaRepo::findOrFail($vista);

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
            'destinatario_user_id',
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

        VistaRepo::update($vista, [
            'nome'          => $data['nome'],
            'logica'        => $data['logica'],
            'acesso'        => $data['acesso'],
            'filtros'       => $conditions,
            'departamentos' => $request->input('departamentos', []),
            'users'         => $request->input('users', []),
        ]);

        return redirect()->route('vistas.index')->with('success', 'Vista atualizada com sucesso.');
    }

    public function destroy(string $vista)
    {
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

            // IN / NOT IN -> value deve ser array com pelo menos 1 item
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

            // restantes -> value tem de existir e não ser vazio
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
