@extends('layouts.app')

@section('title', 'Lista de Destinatários')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 class="mb-0">Lista de Destinatários</h2>
    <div>
        <a href="{{ route('destinatarios.create') }}" class="btn btn-primary">Criar Novo Destinatário</a>
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
                    <th>Email</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($destinatarios as $destinatario)
                    <tr>
                        <td>{{ $destinatario->name }}</td>
                        <td>{{ $destinatario->email }}</td>
                        <td class="text-end">
                            <a href="{{ route('destinatarios.show', $destinatario->id) }}" class="btn btn-sm btn-success">Ver</a>
                            <a href="{{ route('destinatarios.edit', $destinatario->id) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('destinatarios.destroy', $destinatario->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Tem certeza que deseja excluir?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center">Nenhum destinatário encontrado.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
