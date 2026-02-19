<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Chefia</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>

<body class="bg-light">

<div class="container py-5" style="max-width: 750px;">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">

            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <div>
                    <h2 class="fw-bold mb-0">➕ Nova Chefia</h2>
                    <small class="text-muted">Criar uma nova chefia</small>
                </div>

                <a href="{{ route('chefias.index') }}" class="btn btn-light border">
                    ← Voltar
                </a>
            </div>

            {{-- Erros --}}
            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Há erros no formulário:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('chefias.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Nome *</label>
                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        value="{{ old('name') }}"
                        required
                        placeholder="Ex: Chefia Oficina"
                    >
                </div>

                <div class="mb-3">
    <label class="form-label fw-semibold">Utilizadores</label>
    <select name="users[]" class="form-select" multiple>
        @foreach($users as $user)
            <option value="{{ $user->id }}" {{ in_array($user->id, old('users', [])) ? 'selected' : '' }}>
                {{ $user->name }}
            </option>
        @endforeach
    </select>
    <small class="text-muted">Segure Ctrl (Windows)</small>
</div>


                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-lg me-1"></i> Guardar
                    </button>

                    <a href="{{ route('chefias.index') }}" class="btn btn-secondary">
                        Cancelar
                    </a>
                </div>
            </form>

        </div>
    </div>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
