<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Destinatario;
use App\Models\SLA;
use App\Models\Departamento;
use App\Models\Origem;
use App\Models\Aviso;
use App\Models\Estado;
use App\Models\Setor;
use App\Models\Tipo;
use App\Models\Recado;
use App\Models\Grupo;
use App\Models\Campanha;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RecadosExport;
use App\Imports\RecadosImport;
use App\Models\TipoFormulario;


class PainelController extends Controller
{
    public function index()
    { 
        return view('painel.index', [
            'destinatarios' => Destinatario::all(),
            'slas' => SLA::all(),
            'departamentos' => Departamento::all(),
            'origens' => Origem::all(),
            'setores' => Setor::all(),
            'estados' => Estado::all(),
            'avisos' => Aviso::all(),
            'tipos' => Tipo::all(),
        ]);
    }

public function configuracoes(Request $request)
{
    $recados = Recado::query()
        ->when($request->id, fn($q) => $q->where('id', $request->id))
        ->when($request->contact_client, fn($q) => 
            $q->where('contact_client', 'like', "%{$request->contact_client}%"))
        ->when($request->plate, fn($q) => $q->where('plate', $request->plate))
        ->when($request->estado_id, fn($q) => $q->where('estado_id', $request->estado_id))
        ->when($request->tipo_formulario_id, fn($q) => 
            $q->where('tipo_formulario_id', $request->tipo_formulario_id))
        ->when($request->campanha_id, fn($q) => 
            $q->where('campanha_id', $request->campanha_id))
        ->when($request->tipo_id, fn($q) => 
            $q->where('tipo_id', $request->tipo_id))
        ->orderBy('id', 'desc')
        ->paginate(10);

    return view('configuracoes.index', [
        'recados' => $recados,
        'estados' => Estado::all(),
        'tiposFormulario' => TipoFormulario::all(),
        'campanhas' => Campanha::all(),
        'tipos' => Tipo::all(),
    ]);
}




public function exportFiltered(Request $request)
{
    $recados = Recado::query()
        ->when($request->id, fn($q) => $q->where('id', $request->id))
        ->when($request->contact_client, fn($q) => 
            $q->where('contact_client', 'like', "%{$request->contact_client}%"))
        ->when($request->plate, fn($q) => $q->where('plate', $request->plate))
        ->when($request->estado_id, fn($q) => $q->where('estado_id', $request->estado_id))
        ->when($request->tipo_formulario_id, fn($q) => 
            $q->where('tipo_formulario_id', $request->tipo_formulario_id))
        ->when($request->campanha_id, fn($q) => 
            $q->where('campanha_id', $request->campanha_id))
        ->when($request->tipo_id, fn($q) => 
            $q->where('tipo_id', $request->tipo_id))
        ->get();

    return Excel::download(
        new \App\Exports\RecadosExport($recados),
        'recados_filtrados.xlsx'
    );
}



    public function export()
    {
        $recados = Recado::with(['estado','tipoFormulario'])->get();
        return Excel::download(new RecadosExport($recados),'recados_todos.xlsx');
    }

public function indexConfiguracoes(Request $request)
{
    $recados = Recado::with(['estado', 'tipoFormulario'])
                      ->filter($request->all()) // se tiver scope de filtros
                      ->paginate(10);

    $estados = Estado::all();
    $tiposFormulario = TipoFormulario::all();

    return view('configuracoes.index', compact('recados', 'estados', 'tiposFormulario'));
}


    
}
