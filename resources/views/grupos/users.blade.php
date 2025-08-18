@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Utilizadores no Grupo: {{ $grupo->name }}</h2>
        <a href="{{ route('grupos.index') }}" class="btn btn-secondary">← Voltar</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Formulário para atualizar os utilizadores --}}
    <div class="card mb-4">
        <div class="card-header">Adicionar Utilizadores do Grupo</div>
        <div class="card-body">
            <form method="POST" action="{{ route('grupos.updateUsers', $grupo->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="users" class="form-label">Selecionar Utilizadores</label>
                    <select name="users[]" id="users" class="form-select" multiple size="8">
                        @foreach (\App\Models\User::all() as $user)
                            <option value="{{ $user->id }}" {{ $grupo->users->contains($user->id) ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Use Ctrl para selecionar múltiplos.</div>
                </div>

                <button type="submit" class="btn btn-primary">Adicionar Utilizadores</button>
            </form>
        </div>
    </div>

    {{-- Lista dos utilizadores no grupo --}}
    <div class="card">
        <div class="card-header">Utilizadores no Grupo</div>
        <div class="card-body p-0">
            @if ($users->isEmpty())
                <p class="m-3 text-muted">Nenhum utilizador neste grupo.</p>
            @else
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    <form action="{{ route('grupos.users.remover', $grupo->id) }}" method="POST" onsubmit="return confirm('Tem a certeza que deseja remover este utilizador do grupo?');">
                                        @csrf
                                        <input type="hidden" name="users[]" value="{{ $user->id }}">
                                        <button type="submit" class="btn btn-sm btn-danger">Remover</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
</div>
@endsection
