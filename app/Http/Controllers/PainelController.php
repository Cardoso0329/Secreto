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
        ->when($request->contact_client, fn($q) => $q->where('contact_client', 'like', "%{$request->contact_client}%"))
        ->when($request->plate, fn($q) => $q->where('plate', $request->plate))
        ->when($request->estado_id, fn($q) => $q->where('estado_id', $request->estado_id))
        ->when($request->tipo_formulario_id, fn($q) => $q->where('tipo_formulario_id', $request->tipo_formulario_id))
        ->orderBy('id', 'desc')
        ->paginate(10);

    $estados = Estado::all();
    $tiposFormulario = TipoFormulario::all();

    return view('configuracoes.index', compact('recados', 'estados', 'tiposFormulario'));
}



public function exportFiltered(Request $request)
{
    $query = Recado::query()
        ->when($request->id, fn($q) => $q->where('id', $request->id))
        ->when($request->contact_client, fn($q) => $q->where('contact_client', 'like', "%{$request->contact_client}%"))
        ->when($request->plate, fn($q) => $q->where('plate', $request->plate))
        ->when($request->estado_id, fn($q) => $q->where('estado_id', $request->estado_id))
        ->when($request->tipo_formulario_id, fn($q) => $q->where('tipo_formulario_id', $request->tipo_formulario_id));

    $recados = $query->get();

    return \Excel::download(new \App\Exports\RecadosExport($recados), 'recados_filtrados.xlsx');
}



    public function importar(Request $request)
{
    $request->validate([
        'file' => 'required|mimes:xlsx,csv'
    ]);

    Excel::import(new RecadosImport, $request->file('file'));

    return redirect()->back()->with('success', 'Recados importados com sucesso!');
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
