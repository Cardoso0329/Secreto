@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Lista de SLAs</h2>
        <div>
            <a href="{{ route('slas.create') }}" class="btn btn-primary">Criar Novo SLA</a>
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
                    @foreach ($slas as $sla)
                        <tr>
                            <td>{{ $sla->name }}</td>
                            <td class="text-end">
                                <a href="{{ route('slas.show', $sla->id) }}" class="btn btn-sm btn-success">Ver</a>
                                <a href="{{ route('slas.edit', $sla->id) }}" class="btn btn-sm btn-warning">Editar</a>
                                <form action="{{ route('slas.destroy', $sla->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Tem a certeza que deseja excluir?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
