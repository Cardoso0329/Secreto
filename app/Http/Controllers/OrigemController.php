<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Origem;


class OrigemController extends Controller
{
    // Exibe todos os setores
    public function index()
    {
        $origens = Origem::all();
    return view('origens.index', compact('origens'));
    }

// Exibe o formulário para criar um novo setor
public function create()
{
    return view('origens.create');
}

// Armazena um novo setor
public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255',
    ]);

    Origem::create($request->all());

    return redirect()->route('origens.index');
}

// Exibe um setor específico
public function show(Origem $origem)
{
    return view('origens.show', compact('origem'));
}

// Exibe o formulário para editar um setor
public function edit(Origem  $origem)
{
    return view('origens.edit', compact('origem'));
}

// Atualiza um setor
public function update(Request $request, Origem $origem)
{
    $request->validate([
        'name' => 'required|string|max:255',
    ]);

    $origem->update($request->all());

    return redirect()->route('origens.index');
}

// Exclui um setor
public function destroy(Origem $origem)
{
    $origem->delete();
    return redirect()->route('origens.index');
}

}
