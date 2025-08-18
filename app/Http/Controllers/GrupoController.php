<?php
namespace App\Http\Controllers;

use App\Models\Grupo;
use Illuminate\Http\Request;

class GrupoController extends Controller
{
    public function index()
    {
        $grupos = Grupo::all();
        return view('grupos.index', compact('grupos'));
    }

    public function create()
    {
        return view('grupos.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Criar o grupo (recomendo usar só os dados validados para evitar problemas)
        Grupo::create($request->only('name'));

        return redirect()->route('grupos.index')->with('success', 'Grupo criado com sucesso.');
    }

    public function show(Grupo $grupo)
    {
        // Carregar os users relacionados para evitar query extra na view
        $grupo->load('users');
        return view('grupos.show', compact('grupo'));
    }

    public function edit(Grupo $grupo)
    {
        return view('grupos.edit', compact('grupo'));
    }

    public function update(Request $request, Grupo $grupo)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $grupo->update($request->only('name'));

        return redirect()->route('grupos.index')->with('success', 'Grupo atualizado com sucesso.');
    }

    public function destroy(Grupo $grupo)
    {
        $grupo->delete();

        return redirect()->route('grupos.index')->with('success', 'Grupo eliminado com sucesso.');
    }

    // Página para listar/utilizadores e gerir membros do grupo
    public function users(Grupo $grupo)
    {
        // Carrega todos os utilizadores relacionados com o grupo
        $grupo->load('users');

        return view('grupos.users', [
            'grupo' => $grupo,
            'users' => $grupo->users,
        ]);
    }

    // Método para adicionar utilizadores ao grupo sem remover os existentes
    public function updateUsers(Request $request, Grupo $grupo)
    {
        // Validar input: users deve ser array de IDs existentes na tabela users
        $request->validate([
            'users' => 'array',
            'users.*' => 'exists:users,id',
        ]);

        $grupo->users()->syncWithoutDetaching($request->input('users', []));

        return redirect()->route('grupos.users', $grupo->id)->with('success', 'Utilizadores adicionados com sucesso.');
    }

    // Método para remover utilizadores do grupo
    public function removerUsers(Request $request, Grupo $grupo)
    {
        // Validar input
        $request->validate([
            'users' => 'array',
            'users.*' => 'exists:users,id',
        ]);

        $grupo->users()->detach($request->input('users', []));

        return redirect()->route('grupos.users', $grupo->id)->with('success', 'Utilizadores removidos com sucesso.');
    }
}
