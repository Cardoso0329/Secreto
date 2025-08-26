@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- Cabeçalho --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <h2 class="mb-0">Recados</h2>

    <div class="d-flex gap-2">
        {{-- Exportar TODOS --}}
        <a href="{{ route('recados.export') }}" class="btn btn-outline-success">
            Exportar Todos
        </a>

        {{-- Exportar FILTRADOS --}}
        <a href="{{ route('recados.export.filtered', request()->query()) }}" class="btn btn-success">
            Exportar Filtrados
        </a>
    </div>
</div>

    {{-- Card para escolher tipo de formulário --}}
    <div class="mb-4">
        <h4>Escolher Tipo de Formulário</h4>
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Central</h5>
                        <p class="card-text">Formulário para uso interno da Central.</p>
                        <a href="{{ route('recados.create', ['tipo_formulario' => 'Central']) }}" class="btn btn-primary">Selecionar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card text-center">
                    <div class="card-body">
                        <h5 class="card-title">Call Center</h5>
                        <p class="card-text">Formulário específico para Call Center.</p>
                        <a href="{{ route('recados.create', ['tipo_formulario' => 'Call Center']) }}" class="btn btn-success">Selecionar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- Filtros Combinados --}}
<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('recados.index') }}" method="GET" id="filtersForm" class="row g-3">
            

            {{-- ID --}}
            <div class="col-md-2">
                <input
                    type="text"
                    name="id"
                    class="form-control"
                    placeholder="ID..."
                    value="{{ request('id') }}"
                    oninput="document.getElementById('filtersForm').submit()">
            </div>

            {{-- Contacto do Cliente --}}
            <div class="col-md-2">
                <input
                    type="text"
                    name="contact_client"
                    class="form-control"
                    placeholder="Contacto..."
                    value="{{ request('contact_client') }}"
                    oninput="document.getElementById('filtersForm').submit()">
            </div>

            {{-- Matrícula --}}
            <div class="col-md-2">
                <input
                    type="text"
                    name="plate"
                    class="form-control"
                    placeholder="Matrícula..."
                    value="{{ request('plate') }}"
                    oninput="document.getElementById('filtersForm').submit()">
            </div>

             {{-- Estado --}}
            <div class="col-md-3">
                <select name="estado_id" class="form-select" onchange="document.getElementById('filtersForm').submit()">
                    <option value="">Todos os Estados</option>
                    @foreach($estados as $estado)
                        <option value="{{ $estado->id }}" {{ request('estado_id') == $estado->id ? 'selected' : '' }}>
                            {{ $estado->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Tipo de Formulário --}}
            <div class="col-md-3">
                <select name="tipo_formulario_id" class="form-select" onchange="document.getElementById('filtersForm').submit()">
                    <option value="">Todos os Tipos</option>
                    @foreach($tiposFormulario as $tipo_formulario)
                        <option value="{{ $tipo_formulario->id }}" {{ request('tipo_formulario_id') == $tipo_formulario->id ? 'selected' : '' }}>
                            {{ $tipo_formulario->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
</div>


    {{-- Mensagem de sucesso --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    {{-- Tabela de recados responsiva --}}
    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        @php
                            $sortDir = request('sort_dir', 'desc') === 'asc' ? 'desc' : 'asc';
                        @endphp
                        <th>
                            <a href="{{ route('recados.index', array_merge(request()->query(), ['sort_by' => 'id', 'sort_dir' => $sortDir])) }}">
                                ID
                                @if(request('sort_by') === 'id')
                                    @if(request('sort_dir') === 'asc')
                                        🔼
                                    @else
                                        🔽
                                    @endif
                                @endif
                            </a>
                        </th>
                        <th>Nome</th>
                        <th>Contato Cliente</th>
                        <th>Matrícula</th>
                        <th>Email do Operador</th>
                        <th>Estado</th>
                        <th>Tipo de Formulário</th>
                        <th class="text-nowrap">Data de Criação</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recados as $recado)
                        <tr>
                            <td>{{ $recado->id }}</td>
                            <td>{{ $recado->name }}</td>
                            <td>{{ $recado->contact_client }}</td>
                            <td>{{ $recado->plate ?? '—' }}</td>
                            <td>{{ $recado->operator_email ?? '—' }}</td>
                            <td>
                                @php
                                    $estadoNome = strtolower($recado->estado->name ?? '');
                                    $badgeClass = match($estadoNome) {
                                        'aguardar' => 'bg-warning text-dark',
                                        'pendente' => 'bg-warning-subtle text-dark',
                                        'tratado' => 'bg-success text-white',
                                        default => 'bg-secondary text-white'
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }}">
                                    {{ ucfirst($estadoNome) ?: '—' }}
                                </span>
                            </td>
                            <td>{{ $recado->tipoFormulario->name ?? '—' }}</td>
                            <td class="text-nowrap">{{ $recado->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('recados.show', $recado) }}" class="btn btn-sm btn-info me-1">Ver</a>
                                @auth
                                    @if(auth()->user()->cargo_id === 1)
                                        <form action="{{ route('recados.destroy', $recado) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Apagar?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Apagar</button>
                                        </form>
                                    @endif
                                @endauth
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center">Nenhum recado encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Paginação --}}
            <div class="d-flex justify-content-center mt-4">
                {{ $recados->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
