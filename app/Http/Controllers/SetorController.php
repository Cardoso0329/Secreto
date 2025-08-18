<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setor;

class SetorController extends Controller
{
        public function index()
        {
            $setores = Setor::all();
            return view('setores.index', compact('setores'));
        }
    
        public function create()
        {
            return view('setores.create');
        }
    
        public function store(Request $request)
        {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);
    
            Setor::create($request->all());
    
            return redirect()->route('setores.index');
        }
    
        public function show(Setor $setor)
        {
            return view('setores.show', compact('setor'));
        }
    
        public function edit(Setor $setor)
        {
            return view('setores.edit', compact('setor'));
        }
    
        public function update(Request $request, Setor $setor)
        {
            $request->validate([
                'name' => 'required|string|max:255',
            ]);
    
            $setor->update($request->all());
    
            return redirect()->route('setores.index');
        }
    
        
        public function destroy(Setor $setor)
        {
            $setor->delete();
            return redirect()->route('setores.index');
        }

}
