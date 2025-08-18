@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Lista de Cargos</h2>
        <a href="{{ route('cargos.create') }}" class="btn btn-primary">Criar Novo Cargo</a>
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
                    @foreach($cargos as $cargo)
                    <tr>
                        <td>{{ $cargo->name }}</td>
                        <td class="text-end">
                            <a href="{{ route('cargos.show', $cargo) }}" class="btn btn-sm btn-success">Ver</a>
                            <a href="{{ route('cargos.edit', $cargo) }}" class="btn btn-sm btn-warning">Editar</a>
                            <form action="{{ route('cargos.destroy', $cargo) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Tem a certeza que deseja excluir?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Paginação --}}
            <div class="mt-3">
                {{ $cargos->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
