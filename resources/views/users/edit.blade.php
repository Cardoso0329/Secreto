<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar Utilizador</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />

    <style>
        body {
            background: #f5f7fa;
        }
        .card {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 25px rgba(0,0,0,0.08);
        }
        .section-title {
            font-weight: 600;
            font-size: 1.05rem;
            color: #555;
            margin-bottom: 10px;
            border-left: 4px solid #0d6efd;
            padding-left: 8px;
        }
        .form-check {
            padding: 6px 12px;
            background: #fff;
            border-radius: 6px;
            border: 1px solid #e1e1e1;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="mx-auto" style="max-width: 700px;">
        <div class="card p-4 p-md-5">

            <h1 class="text-center mb-4 fw-bold">✏️ Editar Utilizador</h1>

            <form method="POST" action="{{ route('users.update', $user->id) }}">
                @csrf
                @method('PUT')

                {{-- Nome --}}
                <div class="mb-4">
                    <label for="name" class="section-title">Nome</label>
                    <input type="text" name="name" id="name" class="form-control form-control-lg"
                           value="{{ $user->name }}">
                </div>

                {{-- Email --}}
                <div class="mb-4">
                    <label for="email" class="section-title">Email</label>
                    <input type="email" name="email" id="email" class="form-control form-control-lg"
                           value="{{ $user->email }}">
                </div>

                {{-- Cargo --}}
                <div class="mb-4">
                    <label for="cargo_id" class="section-title">Cargo</label>
                    <select name="cargo_id" id="cargo_id" class="form-select form-select-lg">
                        @foreach($cargos as $cargo)
                            <option value="{{ $cargo->id }}" {{ $user->cargo_id == $cargo->id ? 'selected' : '' }}>
                                {{ $cargo->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Departamentos --}}
                <div class="mb-4">
                    <label class="section-title">Departamentos</label>
                    <div class="d-flex flex-wrap gap-2">
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

                {{-- Password --}}
                <div class="mb-4">
                    <label for="password" class="section-title">Nova Password</label>
                    <input type="password" name="password" id="password" class="form-control form-control-lg">
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="section-title">Confirmar Password</label>
                    <input type="password" name="password_confirmation" id="password_confirmation"
                           class="form-control form-control-lg">
                </div>

                {{-- Botões --}}
                <div class="d-flex justify-content-between mt-4">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary btn-lg px-4">
                        Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary btn-lg px-4">
                        Guardar Alterações
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
