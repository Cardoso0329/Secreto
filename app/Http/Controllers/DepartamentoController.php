<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departamento;
use App\Models\User;



class DepartamentoController extends Controller
{
    
    public function index()
    {
        $departamentos = Departamento::orderBy('name', 'asc')->get();
        return view('departamentos.index', compact('departamentos'));
    }

    public function create()
    {
        return view('departamentos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Departamento::create($request->all());

        return redirect()->route('departamentos.index');
    }

    public function show(Departamento $departamento)
    {
        return view('departamentos.show', compact('departamento'));
    }

   public function edit(Departamento $departamento)
{
    $users = User::orderBy('name')->get();
    $departamento->load('users'); // carrega a relação

    return view('departamentos.edit', compact('departamento', 'users'));
}



  public function update(Request $request, Departamento $departamento)
{
    $request->validate([
        'name' => 'required|string|max:255',
        'users' => 'array'
    ]);

    // Atualiza o nome do departamento
    $departamento->update(['name' => $request->name]);

    // Sincroniza os utilizadores selecionados na pivot
    $departamento->users()->sync($request->users ?? []);

    return redirect()->route('departamentos.index')
                     ->with('success', 'Departamento atualizado com sucesso.');
}



    public function destroy(Departamento $departamento)
    {
        $departamento->delete();
        return redirect()->route('departamentos.index');
    }

}
