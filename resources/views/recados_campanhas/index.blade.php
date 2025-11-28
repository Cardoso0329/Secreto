<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recados de Campanhas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        .card-filter {
            border-radius: 12px;
            padding: 20px;
            background: #f8f9fa;
        }
        table thead th {
            background: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>

<div class="container mt-5">

    <h1 class="fw-bold mb-4">🎯 Recados de Campanhas do Meu Departamento</h1>

    {{-- FILTROS --}}
    <div class="card card-filter mb-4 shadow-sm">
        <form method="GET" action="{{ route('recados_campanhas.index') }}">
            <div class="row g-3">

                {{-- Filtro por Departamento --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Departamento</label>
                    <select name="departamento" class="form-select">
                        <option value="">Todos</option>

                        @foreach($departamentos as $dep)
                            <option value="{{ $dep->id }}" 
                                {{ request('departamento') == $dep->id ? 'selected' : '' }}>
                                {{ $dep->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Filtro por Campanha --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Campanha</label>
                    <select name="campanha" class="form-select">
                        <option value="">Todas</option>

                        @foreach($campanhas as $camp)
                            <option value="{{ $camp->id }}" 
                                {{ request('campanha') == $camp->id ? 'selected' : '' }}>
                                {{ $camp->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Botões --}}
                <div class="col-md-4 d-flex align-items-end">
                    <button class="btn btn-primary w-50 me-2">Filtrar</button>
                    <a href="{{ route('recados_campanhas.index') }}" class="btn btn-secondary w-50">Limpar</a>
                </div>
            </div>
        </form>
    </div>

    {{-- TABELA --}}
    @if($recados->count())
        <div class="table-responsive shadow-sm">
            <table class="table table-bordered table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Departamento</th>
                        <th>Campanha</th>
                        <th>Assunto</th>
                        <th style="width: 120px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recados as $recado)
                        <tr>
                            <td class="fw-bold">{{ $recado->id }}</td>

                            {{-- Departamento --}}
                            <td>
                                <span class="badge bg-dark">
                                    {{ $recado->departamento->name ?? 'N/A' }}
                                </span>
                            </td>

                            {{-- Campanha --}}
                            <td>
                                @if($recado->campanha)
                                    <span class="badge bg-primary">{{ $recado->campanha->name }}</span>
                                @else
                                    <span class="badge bg-secondary">Sem campanha</span>
                                @endif
                            </td>

                            {{-- Assunto --}}
                            <td>{{ $recado->assunto ?? '—' }}</td>

                            <td>
                                <a href="{{ route('recados.show', $recado->id) }}"
                                   class="btn btn-sm btn-info">
                                    Ver
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <p class="alert alert-info">Nenhum recado encontrado.</p>
    @endif

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
