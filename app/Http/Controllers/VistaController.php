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
    $user = auth()->user();

    $vistas = Vista::where(function ($q) use ($user) {
        $q->where('acesso', 'publico')
          ->orWhere(function ($q2) use ($user) {
              $q2->where('acesso', 'privado')
                 ->where('user_id', $user->id);
          });
    })
    ->orderBy('created_at', 'desc')
    ->get();

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
        'acesso'  => 'required|in:privado,publico',
    ]);

    Vista::create([
        'nome'    => $request->nome,
        'filtros' => $request->filtros,
        'acesso'  => $request->acesso,
        'user_id' => auth()->id(),
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
