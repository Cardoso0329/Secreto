<?php

namespace App\Http\Controllers;

use App\Models\Campanha;
use App\Models\Departamento;
use Illuminate\Http\Request;

class CampanhaController extends Controller
{
    // ============================
    // Listagem de campanhas
    // ============================
    public function index()
    {
        $campanhas = Campanha::with('departamentos')->get();
        return view('campanhas.index', compact('campanhas'));
    }

    // ============================
    // Formulário de criação
    // ============================
    public function create()
    {
        $departamentos = Departamento::all();
        return view('campanhas.create', compact('departamentos'));
    }

    // ============================
    // Guardar nova campanha
    // ============================
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'departamentos' => 'nullable|array',
            'departamentos.*' => 'exists:departamentos,id',
        ]);

        $campanha = Campanha::create($request->only('name'));

        if ($request->departamentos) {
            $campanha->departamentos()->sync($request->departamentos);
        }

        return redirect()->route('campanhas.index')->with('success', 'Campanha criada com sucesso.');
    }

    // ============================
    // Formulário de edição
    // ============================
    public function edit(Campanha $campanha)
    {
        $departamentos = Departamento::all();
        $campanha->load('departamentos'); // carrega os departamentos relacionados
        return view('campanhas.edit', compact('campanha', 'departamentos'));
    }

    // ============================
    // Atualizar campanha
    // ============================
    public function update(Request $request, Campanha $campanha)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'departamentos' => 'nullable|array',
            'departamentos.*' => 'exists:departamentos,id',
        ]);

        $campanha->update($request->only('name'));

        $campanha->departamentos()->sync($request->departamentos ?? []);

        return redirect()->route('campanhas.index')->with('success', 'Campanha atualizada com sucesso.');
    }

    // ============================
    // Remover campanha
    // ============================
    public function destroy(Campanha $campanha)
    {
        $campanha->delete();
        return redirect()->route('campanhas.index')->with('success', 'Campanha removida.');
    }
}
