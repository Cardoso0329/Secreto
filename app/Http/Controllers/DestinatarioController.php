<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Destinatario;


class DestinatarioController extends Controller
{
    
    public function index()
    {
        $destinatarios = Destinatario::with('user')->get();
        return view('destinatarios.index', compact('destinatarios'));

    }

    public function create()
    {
        return view('destinatarios.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Destinatario::create($request->all());

        return redirect()->route('destinatarios.index');
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

