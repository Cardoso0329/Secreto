<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cargo;
use App\Models\Departamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UsersExport;
use App\Imports\UsersImport;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['cargo', 'departamentos'])
        ->orderBy('name', 'asc')
        ->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $cargos = Cargo::all();
        $departamentos = Departamento::all();

        return view('users.create', compact('cargos', 'departamentos'));
    }

    public function store(Request $request)
{
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => [
            'required',
            'email',
            'unique:users,email',
            'regex:/^[\w\.-]+@soccsantos\.pt$/'
        ],
        'cargo_id' => 'required|exists:cargos,id',
        'password' => 'required|string|min:6|confirmed',
    ], [
        'email.regex' => '⚠️ O email tem de ser do domínio @soccsantos.pt.',
        'email.unique' => '⚠️ Este email já está registado.',
    ]);

    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'cargo_id' => $request->cargo_id,
        'password' => Hash::make($request->password),
        'visibilidade_recados' => 'nenhum',
    ]);

    return redirect()->route('users.index')
        ->with('success', 'Utilizador criado.');
}



    public function edit(User $user)
    {
        $cargos = Cargo::all();
        return view('users.edit', compact('user', 'cargos'));
    }

    public function update(Request $request, User $user)
{
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => [
            'required',
            'email',
            'unique:users,email,' . $user->id,
            'regex:/^[\w\.-]+@soccsantos\.pt$/'
        ],
        'cargo_id' => 'required|exists:cargos,id',
        'visibilidade_recados' => 'required|in:todos,campanhas,nenhum',
        'password' => 'nullable|string|min:6|confirmed',
    ], [
        'email.regex' => '⚠️ O email tem de ser do domínio @soccsantos.pt.',
        'email.unique' => '⚠️ Este email já está registado.',
    ]);

    $data = $request->only('name', 'email', 'cargo_id', 'visibilidade_recados');

    if ($request->filled('password')) {
        $data['password'] = Hash::make($request->password);
    }

    $user->update($data);

    return redirect()->route('users.index')
        ->with('success', 'Utilizador atualizado.');
}


    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('users.index')->with('success', 'Utilizador removido.');
    }

    // Exportar utilizadores
    public function export()
    {
        return Excel::download(new UsersExport, 'users.xlsx');
    }

    // Importar utilizadores
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,csv'
        ]);

        try {
            Excel::import(new UsersImport, $request->file('file'));
            return redirect()->back()->with('success', 'Utilizadores importados com sucesso!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erro ao importar: ' . $e->getMessage());
        }
    }

    public function search(Request $request)
    {
        $query = $request->input('q', '');

        $users = User::with(['cargo', 'departamentos'])
            ->where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->get();

        return response()->json($users);
    }
}
