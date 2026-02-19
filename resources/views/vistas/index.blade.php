<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vistas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">📌 Vistas</h3>
        <a href="{{ route('vistas.create') }}" class="btn btn-primary">➕ Nova Vista</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Lógica</th>
                        <th>Acesso</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($vistas as $vista)
                    <tr>
                        <td class="fw-semibold">{{ $vista['nome'] ?? '—' }}</td>

                        <td>
                            <span class="badge bg-secondary">{{ $vista['logica'] ?? 'AND' }}</span>
                        </td>

                        <td>
                            @php $acesso = $vista['acesso'] ?? 'all'; @endphp
                            <span class="badge {{ $acesso === 'all' ? 'bg-success' : ($acesso === 'department' ? 'bg-info text-dark' : 'bg-warning text-dark') }}">
                                {{ $acesso === 'all' ? 'Todos' : ($acesso === 'department' ? 'Departamento' : 'Específico') }}
                            </span>
                        </td>


                        <td class="text-end">
                            <a href="{{ route('vistas.edit', $vista['id']) }}" class="btn btn-sm btn-outline-primary me-1">
                                ✏️ Editar
                            </a>

                            <form action="{{ route('vistas.destroy', $vista['id']) }}"
                                  method="POST"
                                  class="d-inline"
                                  onsubmit="return confirm('Tens a certeza que queres eliminar esta vista?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    🗑️ Eliminar
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            Nenhuma vista criada.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
