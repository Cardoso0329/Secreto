<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes da Chefia</title>

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
                    <h2 class="fw-bold mb-0">👔 Chefia #{{ $chefia->id }}</h2>
                    <small class="text-muted">Detalhes da chefia</small>
                </div>

                <a href="{{ route('chefias.index') }}" class="btn btn-light border">
                    ← Voltar
                </a>
            </div>

            <div class="mb-3">
                <div class="text-muted small">Nome</div>
                <div class="fs-5 fw-semibold">{{ $chefia->name }}</div>
            </div>

            <hr>

            <div class="d-flex gap-2">
                <a href="{{ route('chefias.edit', $chefia) }}" class="btn btn-warning">
                    <i class="bi bi-pencil me-1"></i> Editar
                </a>

                <form action="{{ route('chefias.destroy', $chefia) }}" method="POST"
                      onsubmit="return confirm('Tens a certeza que queres apagar esta chefia?');">
                    @csrf
                    @method('DELETE')

                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i> Apagar
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

{{-- Bootstrap JS --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
