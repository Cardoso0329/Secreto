<?php

namespace App\Http\Controllers;
use App\Models\Aviso;

use Illuminate\Http\Request;

class AvisoController extends Controller
{
    public function index()
    {
        $avisos = Aviso::orderBy('name', 'asc')->get();
        return view('avisos.index', compact('avisos'));
    }

    public function create()
    {
        return view('avisos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Aviso::create($request->all());

        return redirect()->route('avisos.index');
    }

    public function show(Aviso $aviso)
    {
        return view('avisos.show', compact('aviso'));
    }

    public function edit(Aviso $aviso)
    {
        return view('avisos.edit', compact('aviso'));
    }

    public function update(Request $request, Aviso $aviso)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $aviso->update($request->all());

        return redirect()->route('avisos.index');
    }

    public function destroy(Aviso $aviso)
    {
        $aviso->delete();
        return redirect()->route('avisos.index');
    }

}
