<?php

namespace App\Http\Controllers;

use App\Models\Cargo;
use Illuminate\Http\Request;

class CargoController extends Controller
{
    public function index()
    {
        $cargos = Cargo::orderBy('name')->paginate(10);
        return view('cargos.index', compact('cargos'));
    }

    public function create()
    {
        return view('cargos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:cargos,name|max:255',
        ]);

        Cargo::create($request->only('name'));

        return redirect()->route('cargos.index')->with('success', 'Cargo criado com sucesso.');
    }

    public function show(Cargo $cargo)
    {
        return view('cargos.show', compact('cargo'));
    }

    public function edit(Cargo $cargo)
    {
        return view('cargos.edit', compact('cargo'));
    }

    public function update(Request $request, Cargo $cargo)
    {
        $request->validate([
            'name' => 'required|unique:cargos,name,' . $cargo->id . '|max:255',
        ]);

        $cargo->update($request->only('name'));

        return redirect()->route('cargos.index')->with('success', 'Cargo atualizado com sucesso.');
    }

    public function destroy(Cargo $cargo)
    {
        $cargo->delete();
        return redirect()->route('cargos.index')->with('success', 'Cargo eliminado com sucesso.');
    }
}
