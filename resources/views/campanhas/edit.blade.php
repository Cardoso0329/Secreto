<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Campanha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Editar Campanha</h1>

    <form action="{{ route('campanhas.update', $campanha->id) }}" method="POST" class="mt-4">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Nome da Campanha</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $campanha->name }}" required>
        </div>

        {{-- Departamentos --}}
        <div class="mb-3">
            <label class="form-label">Departamentos</label>
            <div class="d-flex flex-wrap gap-3">
                @foreach($departamentos as $departamento)
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" 
                               name="departamentos[]" 
                               value="{{ $departamento->id }}" 
                               id="departamento_{{ $departamento->id }}"
                               {{ $campanha->departamentos->contains($departamento->id) ? 'checked' : '' }}>
                        <label class="form-check-label" for="departamento_{{ $departamento->id }}">
                            {{ $departamento->name }}
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('campanhas.index') }}" class="btn btn-secondary ms-2">Cancelar</a>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
