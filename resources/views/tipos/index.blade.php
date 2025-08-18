@extends('layouts.app')

@section('title', 'Lista de Tipos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Lista de Tipos</h2>
    <div>
        <a href="{{ route('tipos.create') }}" class="btn btn-primary">Criar Novo Tipo</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($tipos as $tipo)
                    <tr>
                        <td>{{ $tipo->name }}</td>
                        <td class="text-end">
                            <a href="{{ route('tipos.show', $tipo->id) }}" class="btn btn-sm btn-success">Ver</a>
                            <a href="{{ route('tipos.edit', $tipo->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('tipos.destroy', $tipo->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Tem certeza que deseja excluir este tipo?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center">Nenhum tipo encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
