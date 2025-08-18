<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Visualizar Destinatário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
    <h1>Visualizar Destinatário</h1>

    <div class="card mt-4">
        <div class="card-body">
            <p><strong>Nome:</strong> {{ $destinatario->name }}</p>
            <p><strong>Email:</strong> {{ $destinatario->email }}</p>
        </div>
    </div>

    <a href="{{ route('destinatarios.index') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
