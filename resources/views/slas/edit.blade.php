<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar SLA</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container mt-5">
        <h1>Editar SLA</h1>

        <form action="{{ route('slas.update', $sla->id) }}" method="POST" class="mt-4">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="name" class="form-label">Nome:</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    class="form-control" 
                    value="{{ old('name', $sla->name) }}" 
                    required
                >
            </div>

            <button type="submit" class="btn btn-success">Atualizar</button>
            <a href="{{ route('slas.index') }}" class="btn btn-secondary ms-2">Voltar</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
