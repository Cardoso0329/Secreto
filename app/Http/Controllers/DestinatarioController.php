<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Destinatario;


class DestinatarioController extends Controller
{
    
   public function index(Request $request)
{
    $query = $request->input('search');

    // Busca todos os destinatários filtrando por nome ou email
    $destinatarios = Destinatario::query()
        ->when($query, function($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
              ->orWhere('email', 'like', "%{$query}%");
        })
        ->orderBy('name')
        ->get(); // <- aqui usamos get() em vez de paginate()

    return view('destinatarios.index', compact('destinatarios'));
}



    public function create()
    {
        return view('destinatarios.create');
    }

public function store(Request $request)
{
    $request->validate([
        'name' => 'required|string|max:255|unique:destinatarios,name',
        'email' => [
            'required',
            'email',
            'max:255',
            'unique:destinatarios,email',
            'regex:/^[\w\.-]+@soccsantos\.pt$/'
        ],
    ], [
        'name.unique' => '⚠️ Já existe um destinatário com este nome.',
        'email.unique' => '⚠️ Já existe um destinatário com este email.',
        'email.regex' => '⚠️ confirme o domínio do email (@soccsantos.pt).',
    ]);

    Destinatario::create($request->all());

    return redirect()->route('destinatarios.index')
        ->with('success', 'Destinatário criado com sucesso!');
}



    public function show(Destinatario $destinatario)
    {
        return view('destinatarios.show', compact('destinatario'));
    }

    public function edit(Destinatario $destinatario)
    {
        return view('destinatarios.edit', compact('destinatario'));
    }

    public function update(Request $request, Destinatario $destinatario)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $destinatario->update($request->all());

        return redirect()->route('destinatarios.index');
    }

    public function destroy(Destinatario $destinatario)
    {
        $destinatario->delete();
        return redirect()->route('destinatarios.index');
    }

}

