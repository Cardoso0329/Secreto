<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar Origem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Editar Origem</h1>

        <form action="{{ route('origens.update', $origem->id) }}" method="POST" class="mb-3">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Nome:</label>
                <input type="text" id="name" name="name" class="form-control" value="{{ $origem->name }}" required>
            </div>

            <button type="submit" class="btn btn-primary">Atualizar</button>
            <a href="{{ route('origens.index') }}" class="btn btn-secondary ms-2">Voltar</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
