@extends('layouts.app')

@section('title', 'Lista de Departamentos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Lista de Departamentos</h2>
    <div>
        <a href="{{ route('departamentos.create') }}" class="btn btn-primary">Criar Novo Departamento</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($departamentos as $departamento)
                    <tr>
                        <td>{{ $departamento->name }}</td>
                        <td class="text-end">
                            <a href="{{ route('departamentos.show', $departamento->id) }}" class="btn btn-sm btn-success">Ver</a>
                            <a href="{{ route('departamentos.edit', $departamento->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('departamentos.destroy', $departamento->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Tem certeza que deseja excluir?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center">Nenhum departamento encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
