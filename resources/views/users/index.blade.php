<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Utilizadores</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">

    {{-- Cabeçalho --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Utilizadores</h2>

        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="{{ route('users.create') }}" class="btn btn-primary d-flex align-items-center gap-1">
                <i class="bi bi-person-plus-fill"></i> Criar
            </a>

            <form action="{{ route('users.import') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center gap-2">
                @csrf
                <label class="btn btn-outline-secondary m-0">
                    <i class="bi bi-upload"></i> Importar
                    <input type="file" name="file" class="d-none" onchange="this.form.submit()" required>
                </label>
            </form>

            <a href="{{ route('users.export') }}" class="btn btn-success d-flex align-items-center gap-1">
                <i class="bi bi-download"></i> Exportar
            </a>
        </div>
    </div>

    {{-- Barra de Pesquisa --}}
    <div class="mb-3">
        <input type="text" id="search" class="form-control" placeholder="Pesquisar pelo nome ou email..." value="{{ $q ?? '' }}">
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
                <table class="table table-striped align-middle" id="users-table">
                    <thead class="table-light">
                        <tr>
                            <th style="width:90px;">ID</th>
                            <th>Nome</th>
                            <th>Email</th>
                            <th>Cargo</th>
                            <th>Grupos</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody">
                        @forelse($users as $user)
                        <tr>
                            <td class="text-muted">{{ $user->id }}</td> {{-- ✅ ID real --}}
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

            {{-- Paginação --}}
            <div class="mt-3">
                {{ $users->links() }}
            </div>
        </div>
    </div>

</div>

{{-- JS pesquisa dinâmica com debounce --}}
<script>
function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

const searchInput = document.getElementById('search');

searchInput.addEventListener('input', debounce(function() {
    const query = this.value.trim();

    fetch(`{{ route('users.search') }}?q=${encodeURIComponent(query)}`)
        .then(res => res.json())
        .then(users => {
            const tbody = document.querySelector('#users-table tbody');
            tbody.innerHTML = '';

            if(users.length === 0){
                tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">Nenhum utilizador encontrado.</td></tr>';
                return;
            }

            // ✅ já vem ordenado por id asc do backend, não precisas ordenar aqui

            users.forEach((user) => {
                const grupos = user.grupos && user.grupos.length
                    ? user.grupos.map(g => `<span class="badge bg-dark">${g.name}</span>`).join(' ')
                    : '<span class="text-muted">Sem grupo</span>';

                tbody.innerHTML += `
                    <tr>
                        <td class="text-muted">${user.id}</td>
                        <td>${user.name ?? ''}</td>
                        <td>${user.email ?? ''}</td>
                        <td>${user.cargo?.name ?? '-'}</td>
                        <td>${grupos}</td>
                        <td class="text-end">
                            <a href="/users/${user.id}/edit" class="btn btn-sm btn-outline-warning me-1">
                                <i class="bi bi-pencil-square"></i>
                            </a>
                            <form action="/users/${user.id}" method="POST" class="d-inline" onsubmit="return confirm('Apagar este utilizador?')">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <input type="hidden" name="_method" value="DELETE">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                `;
            });
        });
}, 300));
</script>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
