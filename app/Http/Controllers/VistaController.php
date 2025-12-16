<?php

namespace App\Http\Controllers;

use App\Models\Vista;
use Illuminate\Http\Request;

class VistaController extends Controller
{
    /**
     * Listar vistas
     */
    public function index()
    {
        $user = auth()->user();

        $vistas = Vista::query()
            ->with('user')
            ->where(function ($q) use ($user) {
                $q->where('acesso', 'publico')
                  ->orWhere('user_id', $user->id)
                  ->orWhere(function ($q2) use ($user) {
                      $q2->where('acesso', 'especifico')
                         ->whereJsonContains('usuarios_acesso', $user->id);
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
            'acesso'  => 'required|in:privado,publico,especifico',
        ]);

{
    Vista::create([
        'nome' => $request->nome,
        'user_id' => auth()->id(),
        'filtros' => $request->filtros, // garante que é JSON
        'acesso' => $request->acesso,
    ]);
}



        return back()->with('success', 'Vista guardada com sucesso!');
    }

    /**
     * Apagar vista
     */
    public function destroy(Vista $vista)
    {
        $user = auth()->user();

        if ($vista->user_id !== $user->id && $user->cargo?->name !== 'admin') {
            abort(403);
        }

        $vista->delete();

        return back()->with('success', 'Vista eliminada.');
    }
}
