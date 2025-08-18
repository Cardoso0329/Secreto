<?php

namespace App\Http\Controllers;

use App\Models\SLA;

use Illuminate\Http\Request;

class SLAController extends Controller
{

    public function index()
    {
        
        $slas = SLA::all();
        return view('slas.index', compact('slas'));
    }

    public function create()
    {
        return view('slas.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        SLA::create($request->all());

        return redirect()->route('slas.index');
    }

    public function show(SLA $sla)
    {
        return view('slas.show', compact('sla'));
    }

    public function edit(SLA $sla)
    {
        return view('slas.edit', compact('sla'));
    }

    public function update(Request $request, SLA $sla)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $sla->update($request->all());

        return redirect()->route('slas.index');
    }

    public function destroy(SLA $sla)
    {
        $sla->delete();
        return redirect()->route('slas.index');
    }
}
