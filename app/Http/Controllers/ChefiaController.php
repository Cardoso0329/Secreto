<?php

namespace App\Http\Controllers;

use App\Models\Chefia;
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
        return view('chefias.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Chefia::create($data);

        return redirect()
            ->route('chefias.index')
            ->with('success', 'Chefia criada com sucesso.');
    }

    public function show(Chefia $chefia)
    {
        return view('chefias.show', compact('chefia'));
    }

    public function edit(Chefia $chefia)
    {
        return view('chefias.edit', compact('chefia'));
    }

    public function update(Request $request, Chefia $chefia)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $chefia->update($data);

        return redirect()
            ->route('chefias.index')
            ->with('success', 'Chefia atualizada com sucesso.');
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
