@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- Cabeçalho --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="fw-bold mb-0">
            📋 Recados
        </h2>
    </div>

    {{-- Card para escolher tipo de formulário --}}
    <div class="mb-4">
        <h4 class="fw-semibold mb-3">Escolher Tipo de Formulário</h4>
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="card h-100 shadow-sm border-0 hover-shadow transition">
                    <div class="card-body text-center">
                        <i class="bi bi-building-gear fs-1 text-primary mb-2"></i>
                        <h5 class="card-title fw-bold">Central</h5>
                        <p class="card-text text-muted">Formulário para uso interno da Central.</p>
                        <a href="{{ route('recados.create', ['tipo_formulario' => 'Central']) }}" class="btn btn-primary w-100">Selecionar</a>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card h-100 shadow-sm border-0 hover-shadow transition">
                    <div class="card-body text-center">
                        <i class="bi bi-telephone-inbound fs-1 text-success mb-2"></i>
                        <h5 class="card-title fw-bold">Call Center</h5>
                        <p class="card-text text-muted">Formulário específico para Call Center.</p>
                        <a href="{{ route('recados.create', ['tipo_formulario' => 'Call Center']) }}" class="btn btn-success w-100">Selecionar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- Filtros em Accordion estático sem seta --}}
<div class="mb-4">
    <div class="p-2 mb-2 bg-light border rounded">
        <h5 class="mb-0">🔍 Filtros Avançados</h5>
    </div>
    <div class="p-3 border rounded">
        <form action="{{ route('configuracoes.index') }}" method="GET" class="row g-3">
            <div class="col-md-2">
                <input type="text" name="id" class="form-control" placeholder="ID..." value="{{ request('id') }}">
            </div>
            <div class="col-md-2">
                <input type="text" name="contact_client" class="form-control" placeholder="Contacto..." value="{{ request('contact_client') }}">
            </div>
            <div class="col-md-2">
                <input type="text" name="plate" class="form-control" placeholder="Matrícula..." value="{{ request('plate') }}">
            </div>
            <div class="col-md-3">
                <select name="estado_id" class="form-select">
                    <option value="">Todos os Estados</option>
                    @foreach($estados as $estado)
                        <option value="{{ $estado->id }}" {{ request('estado_id') == $estado->id ? 'selected' : '' }}>
                            {{ $estado->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="tipo_formulario_id" class="form-select">
                    <option value="">Todos os Tipos</option>
                    @foreach($tiposFormulario as $tipo_formulario)
                        <option value="{{ $tipo_formulario->id }}" {{ request('tipo_formulario_id') == $tipo_formulario->id ? 'selected' : '' }}>
                            {{ $tipo_formulario->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>
    </div>
</div>


    {{-- Mensagem de sucesso --}}
    @if(session('success'))
        <div class="alert alert-success shadow-sm">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Tabela de Recados --}}
    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        @php $sortDir = request('sort_dir', 'desc') === 'asc' ? 'desc' : 'asc'; @endphp
                        <th>
                            <a href="{{ route('recados.index', array_merge(request()->query(), ['sort_by' => 'id', 'sort_dir' => $sortDir])) }}" class="text-decoration-none">
                                ID
                                @if(request('sort_by') === 'id')
                                    <i class="bi {{ request('sort_dir') === 'asc' ? 'bi-sort-up' : 'bi-sort-down' }}"></i>
                                @endif
                            </a>
                        </th>
                        <th>Nome</th>
                        <th>Cliente</th>
                        <th>Matrícula</th>
                        <th>Email Operador</th>
                        <th>Estado</th>
                        <th>Tipo</th>
                        <th class="text-nowrap">Criado em</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recados as $recado)
                        <tr>
                            <td class="fw-semibold">#{{ $recado->id }}</td>
                            <td>{{ $recado->name }}</td>
                            <td>{{ $recado->contact_client }}</td>
                            <td>{{ $recado->plate ?? '—' }}</td>
                            <td>{{ $recado->operator_email ?? '—' }}</td>
                            <td>
                                @php
                                    $estadoNome = strtolower($recado->estado->name ?? '');
                                    $badgeEstado = match($estadoNome) {
                                        'pendente' => 'bg-warning text-dark',
                                        'tratado' => 'bg-purple text-white',
                                    };
                                @endphp
                                <span class="badge rounded-pill {{ $badgeEstado }}">
                                    {{ ucfirst($estadoNome) ?: '—' }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $tipoNome = strtolower($recado->tipoFormulario->name ?? '');
                                    $badgeTipo = match($tipoNome) {
                                        'central' => 'bg-primary text-white',
                                        'call center' => 'bg-success text-white',
                                    };
                                @endphp
                                <span class="badge rounded-pill {{ $badgeTipo }}">
                                    {{ ucfirst($tipoNome) ?: '—' }}
                                </span>
                            </td>
                            <td class="text-nowrap">{{ $recado->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Nenhum recado encontrado.</td>
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

<style>
.bg-purple {
    background-color: #6f42c1 !important;
}
</style>
