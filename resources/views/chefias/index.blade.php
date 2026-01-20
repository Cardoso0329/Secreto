<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chefias</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">

<div class="container py-5" style="max-width: 950px;">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h2 class="fw-bold mb-0">👔 Chefias</h2>
            <small class="text-muted">Gerir lista de chefias</small>
        </div>

        <a href="{{ route('chefias.create') }}" class="btn btn-success">
            <i class="bi bi-plus-lg me-1"></i> Nova Chefia
        </a>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">

            {{-- Pesquisa --}}
            <form method="GET" class="row g-2 align-items-end mb-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold">Pesquisar</label>
                    <input type="text" name="q" class="form-control" value="{{ $q ?? '' }}" placeholder="Ex: Chefia Oficina">
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search me-1"></i> Procurar
                    </button>
                    <a href="{{ route('chefias.index') }}" class="btn btn-outline-secondary w-100">
                        Limpar
                    </a>
                </div>
            </form>

            {{-- Tabela --}}
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 90px;">#</th>
                            <th>Nome</th>
                            <th class="text-end" style="width: 230px;">Ações</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($chefias as $chefia)
                            <tr>
                                <td class="text-muted">{{ $chefia->id }}</td>
                                <td class="fw-semibold">{{ $chefia->name }}</td>
                                <td class="text-end">
                                    <div class="btn-group">
                                        <a href="{{ route('chefias.show', $chefia) }}" class="btn btn-outline-primary btn-sm" title="Ver">
                                            <i class="bi bi-eye"></i>
                                        </a>

                                        <a href="{{ route('chefias.edit', $chefia) }}" class="btn btn-outline-warning btn-sm" title="Editar">
                                            <i class="bi bi-pencil"></i>
                                        </a>

                                        <form action="{{ route('chefias.destroy', $chefia) }}" method="POST" class="d-inline"
                                              onsubmit="return confirm('Tens a certeza que queres apagar esta chefia?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm" title="Apagar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    Nenhuma chefia encontrada.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Paginação --}}
            <div class="mt-3">
                {{ $chefias->links() }}
            </div>

        </div>
    </div>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
