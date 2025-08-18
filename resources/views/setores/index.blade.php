@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Lista de Setores</h2>
    <div>
        <a href="{{ route('setores.create') }}" class="btn btn-primary">Criar Novo Setor</a>
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
                @forelse ($setores as $setor)
                    <tr>
                        <td>{{ $setor->name }}</td>
                        <td class="text-end">
                            <a href="{{ route('setores.show', $setor->id) }}" class="btn btn-sm btn-success">Ver</a>
                            <a href="{{ route('setores.edit', $setor->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('setores.destroy', $setor->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Tem a certeza que deseja excluir?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center">Nenhum setor encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
