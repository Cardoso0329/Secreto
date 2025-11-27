<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Recado;

class RecadosCampanhaController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Pegar o departamento(s) do usuário
        $departamentoId = $user->departamento_id;

        // Pegar recados que têm campanha associada a um departamento do usuário
        $recados = Recado::whereHas('campanha.departamentos', function($query) use ($departamentoId) {
            $query->where('departamentos.id', $departamentoId);
        })
        ->with(['campanha']) // carrega a campanha associada
        ->get();

        return view('recados_campanhas.index', compact('recados'));
    }
}
