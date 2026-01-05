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
            'campanhas'      => Campanha::orderBy('name')->get(), // para compatibilidade com a view
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'        => 'required|string|max:255',
            'logica'      => 'required|in:AND,OR',
            'acesso'      => 'required|in:all,department,specific',
            'conditions'  => 'nullable|array',

            'departamentos'   => 'nullable|array',
            'departamentos.*' => 'integer',

            'users'   => 'nullable|array',
            'users.*' => 'integer',
        ]);

        // ✅ coerência de acesso
        if ($data['acesso'] === 'department' && empty($request->input('departamentos', []))) {
            return back()->withErrors(['departamentos' => 'Seleciona pelo menos 1 departamento.'])->withInput();
        }
        if ($data['acesso'] === 'specific' && empty($request->input('users', []))) {
            return back()->withErrors(['users' => 'Seleciona pelo menos 1 utilizador.'])->withInput();
        }

        $conditions = [];
        foreach ($request->input('conditions', []) as $cond) {
            if (!empty($cond['field']) && !empty($cond['operator']) && isset($cond['value']) && $cond['value'] !== '') {
                $conditions[] = [
                    'field'    => $cond['field'],
                    'operator' => $cond['operator'],
                    'value'    => $cond['value'],
                ];
            }
        }

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
            'campanhas'      => Campanha::orderBy('name')->get(), // para compatibilidade com a view
        ]);
    }

    public function update(Request $request, string $vista)
    {
        $data = $request->validate([
            'nome'        => 'required|string|max:255',
            'logica'      => 'required|in:AND,OR',
            'acesso'      => 'required|in:all,department,specific',
            'conditions'  => 'nullable|array',

            'departamentos'   => 'nullable|array',
            'departamentos.*' => 'integer',

            'users'   => 'nullable|array',
            'users.*' => 'integer',
        ]);

        // ✅ coerência de acesso
        if ($data['acesso'] === 'department' && empty($request->input('departamentos', []))) {
            return back()->withErrors(['departamentos' => 'Seleciona pelo menos 1 departamento.'])->withInput();
        }
        if ($data['acesso'] === 'specific' && empty($request->input('users', []))) {
            return back()->withErrors(['users' => 'Seleciona pelo menos 1 utilizador.'])->withInput();
        }

        $conditions = [];
        foreach ($request->input('conditions', []) as $cond) {
            if (!empty($cond['field']) && !empty($cond['operator']) && isset($cond['value']) && $cond['value'] !== '') {
                $conditions[] = [
                    'field'    => $cond['field'],
                    'operator' => $cond['operator'],
                    'value'    => $cond['value'],
                ];
            }
        }

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
}
