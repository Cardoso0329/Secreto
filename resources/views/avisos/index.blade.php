@extends('layouts.app')

@section('title', 'Lista de Avisos')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Lista de Avisos</h2>
    <div>
        <a href="{{ route('avisos.create') }}" class="btn btn-primary">Criar Novo Aviso</a>
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
                @forelse ($avisos as $aviso)
                    <tr>
                        <td>{{ $aviso->name }}</td>
                        <td class="text-end">
                            <a href="{{ route('avisos.show', $aviso->id) }}" class="btn btn-sm btn-success">Ver</a>
                            <a href="{{ route('avisos.edit', $aviso->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('avisos.destroy', $aviso->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Tem certeza que deseja excluir este aviso?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center">Nenhum aviso encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
