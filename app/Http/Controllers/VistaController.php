<?php
namespace App\Http\Controllers;

use App\Models\Vista;
use App\Models\Departamento;
use App\Models\User;
use App\Models\Estado;
use App\Models\TipoFormulario;
use App\Models\SLA;
use App\Models\Tipo;
use App\Models\Origem;
use App\Models\Setor;
use App\Models\Aviso;
use Illuminate\Http\Request;

class VistaController extends Controller
{
    // Lista todas as vistas visíveis para o utilizador
    public function index()
    {
        $vistas = Vista::visiveisPara(auth()->user())->get();
        return view('vistas.index', compact('vistas'));
    }

    // Mostra formulário de criação
    public function create()
    {
        return view('vistas.create', [
            'estados' => Estado::all(),
            'tiposFormulario' => TipoFormulario::all(),
            'slas' => SLA::all(),
            'tipos' => Tipo::all(),
            'origens' => Origem::all(),
            'setores' => Setor::all(),
            'departamentos' => Departamento::all(),
            'avisos' => Aviso::all(),
        ]);
    }

    // Armazena nova vista
    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => 'required|string',
            'logica' => 'required|in:AND,OR',
            'access_type' => 'required|in:all,department,owner,specific',
            'conditions' => 'required|array'
        ]);

       $conditions = [];
foreach ($request->conditions ?? [] as $cond) {
    if (!empty($cond['field']) && !empty($cond['operator']) && isset($cond['value'])) {
        $conditions[] = [
            'field' => $cond['field'],
            'operator' => $cond['operator'],
            'value' => $cond['value'],
        ];
    }
}

$vista = Vista::create([
    'nome' => $data['nome'],
    'logica' => $data['logica'],
    'access_type' => $data['access_type'],
    'filtros' => ['conditions' => $conditions],
    'user_id' => auth()->id(),
]);


        // Sincroniza departamentos e utilizadores se enviados
        if ($request->filled('departamentos')) {
            $vista->departamentos()->sync($request->departamentos);
        }

        if ($request->filled('users')) {
            $vista->users()->sync($request->users);
        }

        return redirect()->route('vistas.index')
            ->with('success', 'Vista criada com sucesso');
    }

    // Mostra formulário de edição
    public function edit(Vista $vista)
    {
        return view('vistas.edit', [
            'vista' => $vista,
            'estados' => Estado::all(),
            'tiposFormulario' => TipoFormulario::all(),
            'slas' => SLA::all(),
            'tipos' => Tipo::all(),
            'origens' => Origem::all(),
            'setores' => Setor::all(),
            'departamentos' => Departamento::all(),
            'avisos' => Aviso::all(),
        ]);
    }

    // Atualiza uma vista existente
    public function update(Request $request, Vista $vista)
    {
        $data = $request->validate([
            'nome' => 'nullable|string|max:255',
            'access_type' => 'required|in:all,department,owner,specific',
            'conditions' => 'nullable|array',
        ]);

        // Nome (só se enviado)
        if (!empty($data['nome'])) {
            $vista->nome = $data['nome'];
        }

        $vista->access_type = $data['access_type'];

        // Mantém estrutura correta dos filtros
       if (isset($data['conditions'])) {
    $conditions = [];
    foreach ($data['conditions'] as $cond) {
        if (!empty($cond['field']) && !empty($cond['operator']) && isset($cond['value'])) {
            $conditions[] = $cond;
        }
    }
    $vista->filtros = ['conditions' => $conditions];
}


        // Sincronizações
        if ($request->has('departamentos')) {
            $vista->departamentos()->sync($request->departamentos ?? []);
        }

        if ($request->has('users')) {
            $vista->users()->sync($request->users ?? []);
        }

        $vista->save();

        return redirect()
            ->route('vistas.index')
            ->with('success', 'Vista atualizada com sucesso!');
    }

}
