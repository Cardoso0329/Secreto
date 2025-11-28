<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recado;
use App\Models\Departamento;
use App\Models\Campanha;

class RecadosCampanhaController extends Controller
{
    public function index(Request $request)
{
    $user = auth()->user();
    // Departamentos onde o user pertence
    $departamentosIds = $user->departamentos->pluck('id')->toArray();

    // Criar query base
    $query = Recado::with(['campanha', 'departamento'])
        ->whereHas('campanha.departamentos', function ($q) use ($departamentosIds) {
            $q->whereIn('departamento_id', $departamentosIds);
        });

    
    // Filtro por departamento
    if ($request->filled('departamento')) {
        $query->where('departamento_id', $request->departamento);
    }

    // Filtro por campanha
    if ($request->filled('campanha')) {
        $query->where('campanha_id', $request->campanha);
    }
    // Obter resultados
    $recados = $query->get();

    // Variáveis para a view
    $departamentos = Departamento::all();
    $campanhas = Campanha::all();
    $vis = auth()->user()->visibilidade_recados;

if ($vis === 'nenhum') {
    $recados = collect();
}

if ($vis === 'todos') {
    return redirect()->route('recados.index');
}


    return view('recados_campanhas.index', compact('recados', 'departamentos', 'campanhas'));
}

}
