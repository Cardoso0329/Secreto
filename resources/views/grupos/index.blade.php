<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Grupos</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">

    {{-- Cabeçalho --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Grupos</h2>
        <a href="{{ route('grupos.create') }}" class="btn btn-primary">Novo Grupo</a>
    </div>

    {{-- Mensagens --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Lista de grupos --}}
    <ul class="list-group">
        @forelse($grupos as $index => $grupo)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>{{ $index + 1 }}. {{ $grupo->name }}</span>
                <div>
                    <a href="{{ route('grupos.users', $grupo->id) }}" class="btn btn-sm btn-outline-primary me-1">Ver utilizadores</a>
                    <form action="{{ route('grupos.destroy', $grupo) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Tem certeza que deseja excluir este grupo?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                    </form>
                </div>
            </li>
        @empty
            <li class="list-group-item text-center text-muted">Nenhum grupo encontrado.</li>
        @endforelse
    </ul>

</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
