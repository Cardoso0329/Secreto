<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>👥 Utilizadores no Grupo</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">

   <!-- Título + botão voltar -->
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="fw-bold mb-0">
        👥 Utilizadores no Grupo: 
        <span class="text-primary">{{ $grupo->name }}</span>
    </h2>
    <a href="{{ route('grupos.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<!-- Formulário para editar o nome do grupo -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-warning text-dark fw-semibold">
        ✏️ Editar Nome do Grupo
    </div>
    <div class="card-body">
        <form action="{{ route('grupos.update', $grupo->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="group_name" class="form-label">Nome do Grupo</label>
                <input type="text" class="form-control" id="group_name" name="name" value="{{ $grupo->name }}" required>
            </div>

            <button type="submit" class="btn btn-warning">
                <i class="bi bi-pencil-square"></i> Atualizar Nome
            </button>
        </form>
    </div>
</div>


    <!-- Alertas -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Formulário de adicionar utilizadores -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-primary text-white fw-semibold">
            ➕ Adicionar Utilizadores ao Grupo
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('grupos.updateUsers', $grupo->id) }}">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="users" class="form-label">Selecionar Utilizadores</label>
                    <select name="users[]" id="users" class="form-select" multiple size="8">
                        @foreach (\App\Models\User::all() as $user)
                            <option value="{{ $user->id }}" {{ $grupo->users->contains($user->id) ? 'selected' : '' }}>
                                {{ $user->name }} — {{ $user->email }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">💡 Segure <kbd>Ctrl</kbd> para selecionar vários.</div>
                </div>

                <button type="submit" class="btn btn-success">
                    <i class="bi bi-person-plus"></i> Guardar Alterações
                </button>
            </form>
        </div>
    </div>

    <!-- Lista de utilizadores -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-light fw-semibold">
            📋 Utilizadores neste Grupo
        </div>
        <div class="card-body p-0">
            @if ($users->isEmpty())
                <p class="m-3 text-muted fst-italic">Nenhum utilizador neste grupo.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>👤 Nome</th>
                                <th>📧 Email</th>
                                <th class="text-center">⚙️ Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td class="fw-medium">{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td class="text-center">
                                        <form action="{{ route('grupos.users.remover', $grupo->id) }}" method="POST" 
                                              onsubmit="return confirm('Tem a certeza que deseja remover este utilizador do grupo?');"
                                              class="d-inline">
                                            @csrf
                                            <input type="hidden" name="users[]" value="{{ $user->id }}">
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-person-dash"></i> Remover
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
