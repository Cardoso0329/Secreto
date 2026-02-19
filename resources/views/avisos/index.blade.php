<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lista de Avisos</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">

    {{-- Cabeçalho --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">Lista de Avisos</h2>
        <div>
            <a href="{{ route('avisos.create') }}" class="btn btn-primary">Criar Novo Aviso</a>
        </div>
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
        <div class="card-body p-0">
            @if($avisos->count())
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($avisos as $aviso)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $aviso->name }}</td>
                            <td class="text-end">
                                <a href="{{ route('avisos.edit', $aviso->id) }}" class="btn btn-sm btn-outline-warning me-1">Editar</a>
                                <form action="{{ route('avisos.destroy', $aviso->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Tem certeza que deseja excluir este aviso?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            @else
                <p class="text-center text-muted m-3">Nenhum aviso encontrado.</p>
            @endif
        </div>
    </div>

</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
