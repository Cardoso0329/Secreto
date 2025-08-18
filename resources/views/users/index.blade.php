@extends('layouts.app')

@section('content')
<div class="container mt-5">

    {{-- Cabeçalho --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Utilizadores</h2>

        <div class="d-flex flex-wrap gap-2 align-items-center">

            {{-- Botão: Novo Utilizador --}}
            <a href="{{ route('users.create') }}" class="btn btn-primary d-flex align-items-center gap-1">
                <i class="bi bi-person-plus-fill"></i> Criar
            </a>

            {{-- Formulário de importação --}}
            <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                @csrf
                <label class="btn btn-outline-secondary m-0">
                    <i class="bi bi-upload"></i> Importar
                    <input type="file" name="file" class="d-none" onchange="this.form.submit()" required>
                </label>
            </form>

            {{-- Botão: Exportar --}}
            <a href="{{ route('users.export') }}" class="btn btn-success d-flex align-items-center gap-1">
                <i class="bi bi-download"></i> Exportar
            </a>
        </div>
    </div>

    {{-- Mensagens de sessão --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @elseif(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tabela --}}
    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Cargo</th>
                            <th>Grupos</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->cargo->name ?? '-' }}</td>
                            <td>
                                @forelse ($user->grupos as $grupo)
                                    <span class="badge bg-dark">{{ $grupo->name }}</span>
                                @empty
                                    <span class="text-muted">Sem grupo</span>
                                @endforelse
                            </td>
                            <td class="text-end">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-warning me-1">
                                    <i class="bi bi-pencil-square"></i>
                                </a>
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="d-inline" onsubmit="return confirm('Apagar este utilizador?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">Nenhum utilizador encontrado.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
