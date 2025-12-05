@extends('layouts.app')

@section('content')

{{-- Modal para escolher local (apenas se o controller mandar mostrar e se for Telefonista) --}}
@if(isset($showPopup) && $showPopup && auth()->user()->grupos->contains('name','Telefonistas'))
<div class="modal fade show" id="popupLocal" tabindex="-1" style="display:block; background: rgba(0,0,0,0.6);">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Escolher Local de Trabalho</h5>
            </div>
            <div class="modal-body text-center">
                <p class="mb-4">Onde vai trabalhar agora?</p>

                <form method="POST" action="{{ route('recados.escolherLocal') }}">
                    @csrf
                    <button name="local" value="Central" class="btn btn-primary w-100 mb-3 p-2 fw-semibold">
                        🏢 Central
                    </button>
                    <button name="local" value="Call Center" class="btn btn-success w-100 p-2 fw-semibold">
                        ☎️ Call Center
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>

<style>
    body { overflow: hidden !important; }
</style>
@endif


<div class="container mt-4">

    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="fw-bold mb-0">📋 Recados</h2>

        {{-- Só aparece para Telefonistas que já escolheram local --}}
        @if(session()->has('local_trabalho') && auth()->user()->grupos->contains('name','Telefonistas'))
        <a href="{{ route('recados.create') }}" class="btn btn-primary">
            📄 Novo Recado ({{ session('local_trabalho') }})
        </a>
        @endif
    </div>

    {{-- Filtros --}}
    <div class="mb-4">
        <div class="p-2 mb-2 bg-light border rounded">
            <h5 class="mb-0">🔍 Filtros Avançados</h5>
        </div>

        <div class="p-3 border rounded">
            <form action="{{ route('recados.index') }}" method="GET" class="row g-3">
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
                    <button type="submit" class="btn btn-primary w-100">Filtrar</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Sucesso --}}
    @if(session('success'))
        <div class="alert alert-success shadow-sm">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Tabela --}}
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
                        <th>Contacto Cliente</th>
                        <th>Matrícula</th>
                        <th>Destinatários</th>
                        <th>Estado</th>
                        <th>Tipo</th>
                        <th class="text-nowrap">Criado em</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($recados as $recado)
                        <tr class="clickable-row" data-href="{{ route('recados.show', $recado->id) }}">
                            <td class="fw-semibold">#{{ $recado->id }}</td>
                            <td>{{ $recado->name }}</td>
                            <td>{{ $recado->contact_client }}</td>
                            <td>{{ $recado->plate ?? '—' }}</td>

                            {{-- Destinatários --}}
                            <td>
                                @php
                                    $destinatarios = collect();

                                    if($recado->destinatarios->count()) {
                                        $destinatarios = $destinatarios->merge($recado->destinatarios->pluck('name'));
                                    }

                                    if($recado->grupos->count()) {
                                        foreach($recado->grupos as $grupo) {
                                            $destinatarios = $destinatarios->merge($grupo->users->pluck('name'));
                                        }
                                    }

                                    if($recado->guestTokens->count()) {
                                        $destinatarios = $destinatarios->merge($recado->guestTokens->pluck('email'));
                                    }

                                    $destinatarios = $destinatarios->unique();
                                @endphp

                                {!! $destinatarios->implode('<br>') !!}
                            </td>

                            {{-- Estado --}}
                            <td>
                                @php
                                    $estadoNome = strtolower($recado->estado->name ?? '');
                                    $badgeEstado = match($estadoNome) {
                                        'pendente' => 'bg-warning text-dark',
                                        'tratado' => 'bg-purple text-white',
                                        default => 'bg-secondary text-white'
                                    };
                                @endphp
                                <span class="badge rounded-pill {{ $badgeEstado }}">
                                    {{ $estadoNome ? ucfirst($estadoNome) : '—' }}
                                </span>
                            </td>

                            {{-- Tipo --}}
                            <td>
                                @php
                                    $tipoNome = strtolower($recado->tipoFormulario->name ?? '');
                                    $badgeTipo = match($tipoNome) {
                                        'central' => 'bg-primary text-white',
                                        'call center' => 'bg-success text-white',
                                        default => 'bg-secondary text-white'
                                    };
                                @endphp
                                <span class="badge rounded-pill {{ $badgeTipo }}">
                                    {{ $tipoNome ? ucfirst($tipoNome) : '—' }}
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

            <div class="d-flex justify-content-center mt-4">
                {{ $recados->appends(request()->query())->links() }}
            </div>

        </div>
    </div>

</div>
@endsection

<style>
.bg-purple { background-color: #6f42c1 !important; }
.clickable-row { cursor: pointer; transition: background-color 0.2s ease; }
.clickable-row:hover { background-color: #f8f9fa; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.clickable-row').forEach(row =>
        row.addEventListener('click', () => window.location.href = row.dataset.href)
    );
});
</script>
