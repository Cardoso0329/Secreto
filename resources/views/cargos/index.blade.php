<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lista de Cargos</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">

    {{-- Cabeçalho --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Lista de Cargos</h2>
        <a href="{{ route('cargos.create') }}" class="btn btn-primary">Criar Novo Cargo</a>
    </div>

    {{-- Mensagens --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tabela --}}
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Nome</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($cargos as $index => $cargo)
                        <tr>
                            <td>{{ ($cargos->currentPage() - 1) * $cargos->perPage() + $index + 1 }}</td>
                            <td>{{ $cargo->name }}</td>
                            <td class="text-end">
                                <a href="{{ route('cargos.edit', $cargo) }}" class="btn btn-sm btn-outline-warning me-1">Editar</a>
                                <form action="{{ route('cargos.destroy', $cargo) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Tem a certeza que deseja excluir?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center text-muted">Nenhum cargo encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Paginação --}}
            <div class="mt-3">
                {{ $cargos->links() }}
            </div>
        </div>
    </div>

</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
