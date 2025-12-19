<?php

namespace App\Http\Controllers;

use App\Models\Vista;
use Illuminate\Http\Request;

class VistaController extends Controller
{
    /**
     * Listar vistas (globais)
     */
    public function index()
    {
        $vistas = Vista::orderBy('created_at', 'desc')->get();
        return view('vistas.index', compact('vistas'));
    }

    /**
     * Guardar nova vista
     */
    public function store(Request $request)
    {
        $request->validate([
            'nome'    => 'required|string|max:255',
            'filtros' => 'required|json',
        ]);

        Vista::create([
            'nome'    => $request->nome,
            'filtros' => $request->filtros,
        ]);

        return back()->with('success', 'Vista guardada com sucesso!');
    }

    /**
     * Apagar vista
     */
    public function destroy(Vista $vista)
    {
        $vista->delete();
        return back()->with('success', 'Vista eliminada.');
    }
}
