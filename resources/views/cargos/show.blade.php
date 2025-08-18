<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Detalhes do Cargo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
    <h1>Detalhes do Cargo</h1>

    <div class="card mt-4">
        <div class="card-body">
            <p><strong>ID:</strong> {{ $cargo->id }}</p>
            <p><strong>Nome:</strong> {{ $cargo->name }}</p>
        </div>
    </div>

    <a href="{{ route('cargos.index') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
