<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campanhas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">

    {{-- Cabeçalho --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Campanhas</h2>
        <a href="{{ route('campanhas.create') }}" class="btn btn-success">Nova Campanha</a>
    </div>

    {{-- Mensagens --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Tabela --}}
    <div class="card shadow-sm">
        <div class="card-body">
            @if($campanhas->count())
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>Departamentos</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($campanhas as $campanha)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $campanha->name }}</td>
                                <td>
                                    @forelse($campanha->departamentos as $dep)
                                        <span class="badge bg-primary">{{ $dep->name }}</span>
                                    @empty
                                        <span class="text-muted">Sem departamento</span>
                                    @endforelse
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('campanhas.edit', $campanha->id) }}" class="btn btn-sm btn-outline-warning me-1">Editar</a>
                                    <form action="{{ route('campanhas.destroy', $campanha->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Tem a certeza que deseja excluir esta campanha?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">Apagar</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="text-center text-muted">Nenhuma campanha criada.</p>
            @endif
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
