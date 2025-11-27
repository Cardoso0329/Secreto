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

        {{-- Nome --}}
        <div class="mb-3">
            <label for="name" class="form-label">Nome</label>
            <input type="text" name="name" id="name" class="form-control" value="{{ $user->name }}">
        </div>

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" id="email" class="form-control" value="{{ $user->email }}">
        </div>

        {{-- Cargo --}}
        <div class="mb-3">
            <label for="cargo_id" class="form-label">Cargo</label>
            <select name="cargo_id" id="cargo_id" class="form-select">
                @foreach($cargos as $cargo)
                    <option value="{{ $cargo->id }}" {{ $user->cargo_id == $cargo->id ? 'selected' : '' }}>
                        {{ $cargo->name }}
                    </option>
                @endforeach
            </select>
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
                       {{ $user->departamentos->contains($departamento->id) ? 'checked' : '' }}>
                <label class="form-check-label" for="departamento_{{ $departamento->id }}">
                    {{ $departamento->name }}
                </label>
            </div>
        @endforeach
    </div>
</div>


        {{-- Password (opcional) --}}
        <div class="mb-3">
            <label for="password" class="form-label">Nova Password</label>
            <input type="password" name="password" id="password" class="form-control">
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Confirmar Password</label>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Atualizar</button>
        <a href="{{ route('users.index') }}" class="btn btn-secondary ms-2">Cancelar</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
