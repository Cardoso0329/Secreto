<!DOCTYPE html>

<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Editar Cargo</title>
    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Editar Cargo</h1>


<form action="{{ route('cargos.update', $cargo) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label for="name" class="form-label">Nome:</label>
        <input type="text" name="name" id="name" 
               class="form-control @error('name') is-invalid @enderror"
               value="{{ old('name', $cargo->name) }}" required>
        @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <button class="btn btn-primary" type="submit">Atualizar</button>
    <a href="{{ route('cargos.index') }}" class="btn btn-secondary">Cancelar</a>
</form>


</div>

{{-- Bootstrap JS --}}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
