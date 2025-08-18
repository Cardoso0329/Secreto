<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar Tipo</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container mt-5">
        <h2>Editar Tipo</h2>

        <form action="{{ route('tipos.update', $tipo->id) }}" method="POST" class="mt-4">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Nome:</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-control"
                    value="{{ old('name', $tipo->name) }}"
                    required
                />
            </div>

            <button type="submit" class="btn btn-success">Atualizar</button>
            <a href="{{ route('tipos.index') }}" class="btn btn-secondary ms-2">Voltar</a>
        </form>
    </div>

    <!-- Bootstrap JS (opcional) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
