<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Destinatario;
use Illuminate\Validation\Rule;



class DestinatarioController extends Controller
{
    
    public function index(Request $request)
{
    $query = Destinatario::query();

        $query->orderBy('name', 'asc');


    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    $destinatarios = $query->orderBy('name')->get();

    return view('destinatarios.index', compact('destinatarios'));
}


    public function create()
    {
        return view('destinatarios.create');
    }


public function store(Request $request)
{
    $request->validate(
        [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('destinatarios', 'name'),
            ],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('destinatarios', 'email'),
                function ($attribute, $value, $fail) {
                    if (!str_ends_with(strtolower($value), '@soccsantos.pt')) {
                        $fail('❌ O email tem de pertencer ao domínio soccsantos.pt.');
                    }
                },
            ],
        ],
        [
            'name.unique'  => '❌ Já existe um destinatário com este nome.',
            'email.unique' => '❌ Já existe um destinatário com este email.',
            'email.email'  => '❌ O email introduzido não é válido.',
        ]
    );

    Destinatario::create([
        'name'  => $request->name,
        'email' => strtolower($request->email),
    ]);

    return redirect()
        ->route('destinatarios.index')
        ->with('success', 'Destinatário criado com sucesso.');
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

