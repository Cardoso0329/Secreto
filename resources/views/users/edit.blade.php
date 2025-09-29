<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar Utilizador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
    <h1>Editar Utilizador</h1>

    <form method="POST" action="{{ route('users.update', $user->id) }}" class="mt-4">
        @csrf
        @method('PUT')

        @include('users.partials.form')

        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary ms-2">Cancelar</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
