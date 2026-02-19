<?php

namespace App\Http\Controllers;

use App\Models\Grupo;
use Illuminate\Http\Request;
use App\Services\AuditService; // ✅ AUDIT (tudo menos emails)

class GrupoController extends Controller
{
    public function index()
    {
        $grupos = Grupo::orderBy('name', 'asc')->get();

        // ✅ AUDIT: listar grupos
        AuditService::log('grupos_index', null, [
            'total' => $grupos->count(),
        ]);

        return view('grupos.index', compact('grupos'));
    }

    public function create()
    {
        // ✅ AUDIT: abrir form create
        AuditService::log('grupo_create_form');

        return view('grupos.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // ✅ AUDIT: tentativa de criar (antes de gravar)
        AuditService::log('grupo_store_request', null, [
            'name' => $validated['name'],
        ]);

        // Criar o grupo
        $grupo = Grupo::create([
            'name' => $validated['name'],
        ]);

        // ✅ AUDIT: criado (extra — para além do Auditable do Model, se tiveres)
        AuditService::log('grupo_created', $grupo, [
            'grupo_id' => $grupo->id,
        ]);

        return redirect()->route('grupos.index')->with('success', 'Grupo criado com sucesso.');
    }

    public function show(Grupo $grupo)
    {
        $grupo->load('users');

        // ✅ AUDIT: ver detalhe
        AuditService::log('grupo_show', $grupo, [
            'grupo_id' => $grupo->id,
            'users_total' => $grupo->users->count(),
        ]);

        return view('grupos.show', compact('grupo'));
    }

    public function edit(Grupo $grupo)
    {
        // ✅ AUDIT: abrir form edit
        AuditService::log('grupo_edit_form', $grupo, [
            'grupo_id' => $grupo->id,
        ]);

        return view('grupos.edit', compact('grupo'));
    }

    public function update(Request $request, Grupo $grupo)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $old = [
            'name' => $grupo->name,
        ];

        // ✅ AUDIT: update request (não depende do Auditable)
        AuditService::log('grupo_update_request', $grupo, [
            'grupo_id' => $grupo->id,
        ], $old, [
            'name' => $validated['name'],
        ]);

        $grupo->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('grupos.index')->with('success', 'Grupo atualizado com sucesso.');
    }

    public function destroy(Grupo $grupo)
    {
        // ✅ AUDIT: apagar grupo
        AuditService::log('grupo_destroy', $grupo, [
            'grupo_id' => $grupo->id,
            'name' => $grupo->name,
        ]);

        $grupo->delete();

        return redirect()->route('grupos.index')->with('success', 'Grupo eliminado com sucesso.');
    }

    // Página para listar/utilizadores e gerir membros do grupo
    public function users(Grupo $grupo)
    {
        $grupo->load('users');

        // ✅ AUDIT: ver membros do grupo
        AuditService::log('grupo_users_page', $grupo, [
            'grupo_id' => $grupo->id,
            'users_total' => $grupo->users->count(),
        ]);

        return view('grupos.users', [
            'grupo' => $grupo,
            'users' => $grupo->users,
        ]);
    }

    // Método para adicionar utilizadores ao grupo sem remover os existentes
    public function updateUsers(Request $request, Grupo $grupo)
    {
        $validated = $request->validate([
            'users' => 'array',
            'users.*' => 'exists:users,id',
        ]);

        $toAdd = collect($validated['users'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        // para saber o que foi realmente adicionado
        $before = $grupo->users()->pluck('users.id')->map(fn($v)=>(int)$v)->values();

        $grupo->users()->syncWithoutDetaching($toAdd->all());

        $after = $grupo->users()->pluck('users.id')->map(fn($v)=>(int)$v)->values();
        $added = $after->diff($before)->values();

        // ✅ AUDIT: adicionar membros
        AuditService::log('grupo_users_added', $grupo, [
            'grupo_id' => $grupo->id,
            'added_users' => $added->all(),
            'requested_users' => $toAdd->all(),
        ]);

        return redirect()->route('grupos.users', $grupo->id)->with('success', 'Utilizadores adicionados com sucesso.');
    }

    // Método para remover utilizadores do grupo
    public function removerUsers(Request $request, Grupo $grupo)
    {
        $validated = $request->validate([
            'users' => 'array',
            'users.*' => 'exists:users,id',
        ]);

        $toRemove = collect($validated['users'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        // ✅ AUDIT: remover membros (antes de remover)
        AuditService::log('grupo_users_removed', $grupo, [
            'grupo_id' => $grupo->id,
            'removed_users' => $toRemove->all(),
        ]);

        $grupo->users()->detach($toRemove->all());

        return redirect()->route('grupos.users', $grupo->id)->with('success', 'Utilizadores removidos com sucesso.');
    }
}
