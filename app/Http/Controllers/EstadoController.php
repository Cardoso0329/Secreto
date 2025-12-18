<?php

namespace App\Http\Controllers;
use App\Models\Estado;

use Illuminate\Http\Request;

class EstadoController extends Controller
{
    public function index()
    {
        $estados = Estado::orderBy('name', 'asc')->get();
        return view('estados.index', compact('estados'));
    }

    public function create()
    {
        return view('estados.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Estado::create($request->all());

        return redirect()->route('estados.index');
    }

    public function show(Estado $estado)
    {
        return view('estados.show', compact('estado'));
    }

    public function edit(Estado $estado)
    {
        return view('estados.edit', compact('estado'));
    }

    public function update(Request $request, Estado $estado)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $estado->update($request->all());

        return redirect()->route('estados.index');
    }

    public function destroy(Estado $estado)
    {
        $estado->delete();
        return redirect()->route('estados.index');
    }


}
