<?php

namespace App\Http\Controllers;

use App\Models\Tipo;
use Illuminate\Http\Request;

class TipoController extends Controller
{
    public function index()
    {
        $tipos = Tipo::all();
        return view('tipos.index', compact('tipos'));
    }

    public function create()
    {
        return view('tipos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Tipo::create($request->all());

        return redirect()->route('tipos.index');
    }

    public function show(Tipo $tipo)
    {
        return view('tipos.show', compact('tipo'));
    }

    public function edit(Tipo $tipo)
    {
        return view('tipos.edit', compact('tipo'));
    }

    public function update(Request $request, Tipo $tipo)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $tipo->update($request->all());

        return redirect()->route('tipos.index');
    }

    public function destroy(Tipo $tipo)
    {
        $tipo->delete();
        return redirect()->route('tipos.index');
    }
}
