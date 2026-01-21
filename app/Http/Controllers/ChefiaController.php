<?php

namespace App\Http\Controllers;

use App\Models\Chefia;
use App\Models\User;
use Illuminate\Http\Request;

class ChefiaController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $chefias = Chefia::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('chefias.index', compact('chefias', 'q'));
    }

    public function create()
{
    $users = User::orderBy('name')->get();
    return view('chefias.create', compact('users'));
}


    public function store(Request $request)
{
    $data = $request->validate([
        'name' => ['required','string','max:255'],
        'users' => ['array'],
        'users.*' => ['exists:users,id'],
    ]);

    $chefia = Chefia::create([
        'name' => $data['name'],
    ]);

    $chefia->users()->sync($data['users'] ?? []);

    return redirect()->route('chefias.index')->with('success', 'Chefia criada com sucesso.');
}


   public function show(Chefia $chefia)
{
    $chefia->load('users');
    return view('chefias.show', compact('chefia'));
}


   public function edit(Chefia $chefia)
{
    $users = User::orderBy('name')->get();
    $selectedUsers = $chefia->users()->pluck('users.id')->toArray();

    return view('chefias.edit', compact('chefia','users','selectedUsers'));
}


   public function update(Request $request, Chefia $chefia)
{
    $data = $request->validate([
        'name' => ['required','string','max:255'],
        'users' => ['array'],
        'users.*' => ['exists:users,id'],
    ]);

    $chefia->update([
        'name' => $data['name'],
    ]);

    $chefia->users()->sync($data['users'] ?? []);

    return redirect()->route('chefias.index')->with('success', 'Chefia atualizada com sucesso.');
}


    public function destroy(Chefia $chefia)
    {
        // Se quiseres bloquear apagar chefias que têm recados:
        // if ($chefia->recados()->exists()) {
        //     return back()->with('error', 'Não é possível apagar: existe recado associado a esta chefia.');
        // }

        $chefia->delete();

        return redirect()
            ->route('chefias.index')
            ->with('success', 'Chefia apagada com sucesso.');
    }
}
