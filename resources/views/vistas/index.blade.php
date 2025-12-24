<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vistas</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>📌 Vistas</h3>
        <a href="{{ route('vistas.create') }}" class="btn btn-primary">
            ➕ Nova Vista
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Lógica</th>
                        <th>Acesso</th>
                        <th>Criada por</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vistas as $vista)
                        <tr>
                            <td>{{ $vista->nome }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $vista->logica }}</span>
                            </td>
                            <td>{{ ucfirst($vista->acesso) }}</td>
                            <td>{{ $vista->user->name ?? '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('vistas.edit', $vista) }}" class="btn btn-sm btn-outline-primary">
                                    ✏️ Editar
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Nenhuma vista criada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Bootstrap JS (opcional, para componentes interativos) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
