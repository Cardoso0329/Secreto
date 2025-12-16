<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Vistas Guardadas</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold mb-0">📁 Vistas Guardadas</h3>
    </div>

    @if(session('success'))
        <div class="alert alert-success shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">

            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Nome</th>
                        <th>Acesso</th>
                        <th>Criada por</th>
                        <th>Filtros</th>
                        <th>Criada em</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($vistas as $vista)
                        <tr>
                            <td class="fw-semibold">
                                {{ $vista->nome }}
                            </td>

                            <td>
                                @php
                                    $badge = match($vista->acesso) {
                                        'publico' => 'bg-success',
                                        'privado' => 'bg-secondary',
                                        'especifico' => 'bg-warning text-dark',
                                        default => 'bg-secondary'
                                    };
                                @endphp
                                <span class="badge {{ $badge }}">
                                    {{ ucfirst($vista->acesso) }}
                                </span>
                            </td>

                            <td>
                                {{ $vista->user->name ?? '—' }}
                            </td>

                           <td>
    @php
        // Garantir que $vista->filtros é array
        $filtros = is_array($vista->filtros) ? $vista->filtros : json_decode($vista->filtros, true);
        $filtrosAtivos = collect($filtros)->filter(fn($v) => filled($v));
    @endphp
    <span class="badge bg-info text-dark">
        {{ $filtrosAtivos->count() }}
    </span>
</td>


                            <td class="text-nowrap">
                                {{ $vista->created_at->format('d/m/Y H:i') }}
                            </td>

                            <td class="text-end text-nowrap">

                                {{-- Eliminar --}}
                                @if(auth()->id() === $vista->user_id || auth()->user()->cargo?->name === 'admin')
                                    <form action="{{ route('vistas.destroy', $vista) }}"
                                          method="POST"
                                          class="d-inline"
                                          onsubmit="return confirm('Eliminar esta vista?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger">
                                            Eliminar
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Nenhuma vista encontrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
