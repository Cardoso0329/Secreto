<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campanhas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Campanhas</h1>
        <a href="{{ route('campanhas.create') }}" class="btn btn-success">Nova Campanha</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($campanhas->count())
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Departamentos</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($campanhas as $campanha)
                    <tr>
                        <td>{{ $campanha->name }}</td>
                        <td>
                            @foreach($campanha->departamentos as $dep)
                                <span class="badge bg-primary">{{ $dep->name }}</span>
                            @endforeach
                        </td>
                        <td>
                            <a href="{{ route('campanhas.edit', $campanha->id) }}" class="btn btn-sm btn-warning">Editar</a>

                            <form action="{{ route('campanhas.destroy', $campanha->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Tem a certeza?');">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Apagar</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Nenhuma campanha criada.</p>
    @endif
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
