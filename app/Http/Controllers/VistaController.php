<?php

namespace App\Http\Controllers;

use App\Models\Vista;
use App\Models\User;
use App\Models\Estado;
use App\Models\Setor;
use App\Models\TipoFormulario;
use App\Models\Departamento;
use App\Models\Origem;
use App\Models\Tipo;
use App\Models\SLA;
use App\Models\Aviso;
use Illuminate\Http\Request;

class VistaController extends Controller
{
    /* ================= LISTAGEM ================= */

    public function index()
    {
        $vistas = Vista::visiveisPara(auth()->user())->get();

        return view('vistas.index', compact('vistas'));
    }

    /* ================= CREATE ================= */

    public function create()
    {
        return view('vistas.create', [
            'estados'       => Estado::orderBy('name')->get(),
            'setores'       => Setor::orderBy('name')->get(),
                'tiposFormulario'  => TipoFormulario::orderBy('name')->get(), // ✅
            'departamentos' => Departamento::orderBy('name')->get(),
            'origens'       => Origem::orderBy('name')->get(),
            'tipos'         => Tipo::orderBy('name')->get(),
            'slas'          => SLA::orderBy('name')->get(),
            'avisos'        => Aviso::orderBy('name')->get(),
            'users'         => User::orderBy('name')->get(),
        ]);
    }

    /* ================= STORE ================= */

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome'        => 'required|string|max:255',
            'logica'      => 'required|in:AND,OR',
            'acesso'      => 'required|in:all,owner,department,specific',
            'conditions'  => 'nullable|array',
        ]);

        $conditions = [];

        foreach ($request->conditions ?? [] as $cond) {
            if (
                !empty($cond['field']) &&
                !empty($cond['operator']) &&
                isset($cond['value']) &&
                $cond['value'] !== ''
            ) {
                $conditions[] = [
                    'field'    => $cond['field'],
                    'operator' => $cond['operator'],
                    'value'    => $cond['value'],
                ];
            }
        }

        $vista = Vista::create([
            'nome'    => $data['nome'],
            'logica'  => $data['logica'],
            'acesso'  => $data['acesso'],
            'filtros' => ['conditions' => $conditions],
            'user_id' => auth()->id(),
        ]);

        // relações opcionais
        if ($request->filled('departamentos')) {
            $vista->departamentos()->sync($request->departamentos);
        }

        if ($request->filled('users')) {
            $vista->users()->sync($request->users);
        }

        return redirect()
            ->route('vistas.index')
            ->with('success', 'Vista criada com sucesso.');
    }

    /* ================= EDIT ================= */

    public function edit(Vista $vista)
    {
        return view('vistas.edit', [
            'vista'        => $vista,
            'estados'      => Estado::orderBy('name')->get(),
                'tiposFormulario'  => TipoFormulario::orderBy('name')->get(), // ✅
            'setores'      => Setor::orderBy('name')->get(),
            'departamentos'=> Departamento::orderBy('name')->get(),
            'origens'      => Origem::orderBy('name')->get(),
            'tipos'        => Tipo::orderBy('name')->get(),
            'slas'         => SLA::orderBy('name')->get(),
            'avisos'       => Aviso::orderBy('name')->get(),
            'users'        => User::orderBy('name')->get(),
        ]);
    }

    /* ================= UPDATE ================= */

    public function update(Request $request, Vista $vista)
    {
        $data = $request->validate([
            'nome'       => 'required|string|max:255',
            'logica'     => 'required|in:AND,OR',
            'acesso'     => 'required|in:all,owner,department,specific',
            'conditions' => 'nullable|array',
        ]);

        $conditions = [];

        foreach ($request->conditions ?? [] as $cond) {
            if (
                !empty($cond['field']) &&
                !empty($cond['operator']) &&
                isset($cond['value']) &&
                $cond['value'] !== ''
            ) {
                $conditions[] = [
                    'field'    => $cond['field'],
                    'operator' => $cond['operator'],
                    'value'    => $cond['value'],
                ];
            }
        }

        $vista->update([
            'nome'    => $data['nome'],
            'logica'  => $data['logica'],
            'acesso'  => $data['acesso'],
            'filtros' => ['conditions' => $conditions],
        ]);

        if ($request->has('departamentos')) {
            $vista->departamentos()->sync($request->departamentos ?? []);
        }

        if ($request->has('users')) {
            $vista->users()->sync($request->users ?? []);
        }

        return redirect()
            ->route('vistas.index')
            ->with('success', 'Vista atualizada com sucesso.');
    }

    /* ================= DELETE ================= */

    public function destroy(Vista $vista)
    {
        $vista->delete();

        return redirect()
            ->route('vistas.index')
            ->with('success', 'Vista eliminada.');
    }
}
