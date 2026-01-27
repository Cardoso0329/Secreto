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

@php
    // ✅ vista ativa: GET ou sessão
    $vistaAtivaId = request('vista_id') ?? session('recados_vista_id');

    $vistaSelecionada = null;
    $vistaConditions = [];

    if($vistaAtivaId) {
        $vistaSelecionada = $vistas->firstWhere('id', $vistaAtivaId);
        $vistaConditions = $vistaSelecionada['filtros'] ?? [];
    }

    $getFiltro = fn($field) =>
        collect($vistaConditions)->firstWhere('field', $field)['value'] ?? request($field);
@endphp

<div class="container py-4">

    {{-- HEADER --}}
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
        <div>
            <h2 class="fw-bold mb-1">📋 Recados - {{ session('local_trabalho') }}</h2>
            <div class="text-muted small">Gerir e acompanhar recados</div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            {{-- Só aparece para Telefonistas que já escolheram local --}}
            @if(session()->has('local_trabalho') && auth()->user()->grupos->contains('name','Telefonistas'))
                <a href="{{ route('recados.create') }}" class="btn btn-primary">
                    <i class="bi bi-file-earmark-plus me-1"></i>
                    Novo Recado
                    <span class="badge bg-light text-dark ms-2">{{ session('local_trabalho') }}</span>
                </a>
            @endif
        </div>
    </div>

    {{-- ALERTA SUCESSO --}}
    @if(session('success'))
        <div class="alert alert-success shadow-sm border-0 d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill"></i>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    {{-- ✅ TOPO: VISTA + FILTROS (em cima) --}}
    <div class="row g-4 mb-4">

        {{-- VISTA --}}
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <div class="fw-semibold">
                        <i class="bi bi-layout-sidebar-inset me-1"></i> Vista
                    </div>
                    @if($vistaAtivaId)
                        <span class="badge bg-info text-dark">Vista aplicada</span>
                    @else
                        <span class="badge bg-light text-muted">Sem vista</span>
                    @endif
                </div>

                <div class="card-body">
                    <form action="{{ route('recados.index') }}" method="GET" class="row g-3 align-items-center">

                        {{-- manter todos os filtros já aplicados --}}
                        @foreach(request()->except('vista_id','page') as $key => $value)
                            @if(is_array($value))
                                @foreach($value as $v)
                                    <input type="hidden" name="{{ $key }}[]" value="{{ $v }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                            @endif
                        @endforeach

                        <div class="col-12">
                            <label class="form-label small text-muted mb-1">Selecionar vista</label>
                            <select name="vista_id" class="form-select" onchange="this.form.submit()">
                                <option value="">Nenhuma</option>

                                @foreach($vistas as $vista)
                                    <option
                                        value="{{ $vista['id'] }}"
                                        {{ (string)$vistaAtivaId === (string)$vista['id'] ? 'selected' : '' }}>
                                        {{ $vista['nome'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        @if($vistaAtivaId)
                            <div class="col-12">
                                <div class="p-2 rounded bg-light border small">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Esta vista será aplicada automaticamente quando não estiveres a usar filtros manuais.
                                </div>
                            </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>

        {{-- FILTROS AVANÇADOS --}}
        <div class="col-12 col-lg-8">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <div class="fw-semibold">
                        <i class="bi bi-funnel me-1"></i> Filtros Avançados
                    </div>
                    <span class="badge bg-light text-muted">Pesquisa</span>
                </div>

                <div class="card-body">
                    <form action="{{ route('recados.index') }}" method="GET" class="row g-3">

                        {{-- ✅ manter vista_id --}}
                        @if($vistaAtivaId)
                            <input type="hidden" name="vista_id" value="{{ $vistaAtivaId }}">
                        @endif

                        {{-- ID --}}
                        <div class="col-12 col-md-3">
                            <label class="form-label small text-muted mb-1">ID</label>
                            <input type="text" name="id" class="form-control"
                                   placeholder="Ex: 123"
                                   value="{{ $getFiltro('id') }}">
                        </div>

                        {{-- Contacto --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label small text-muted mb-1">Contacto Cliente</label>
                            <input type="text" name="contact_client" class="form-control"
                                   placeholder="Ex: 9xx xxx xxx"
                                   value="{{ $getFiltro('contact_client') }}">
                        </div>

                        {{-- Matrícula --}}
                        <div class="col-12 col-md-5">
                            <label class="form-label small text-muted mb-1">Matrícula</label>
                            <input type="text" name="plate" class="form-control"
                                   placeholder="Ex: AA-00-AA"
                                   value="{{ $getFiltro('plate') }}">
                        </div>

                        {{-- 📅 Intervalo de datas (abertura) --}}
                        <div class="col-12 col-md-3">
                            <label class="form-label small text-muted mb-1">Data início</label>
                            <input type="date" name="date_from" class="form-control"
                                   value="{{ request('date_from') }}">
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label small text-muted mb-1">Data fim</label>
                            <input type="date" name="date_to" class="form-control"
                                   value="{{ request('date_to') }}">
                        </div>

                        {{-- BOTÕES --}}
                        <div class="col-12 d-flex gap-2 flex-wrap pt-1">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search me-1"></i> Filtrar
                            </button>

                            {{-- ✅ Exporta já com TODOS os filtros + intervalo --}}
                            @if(
                                optional(auth()->user()->cargo)->name === 'admin'
                                || auth()->user()->grupos->contains('name', 'Telefonistas')
                            )
                                <a href="{{ route('configuracoes.recados.export.filtered', request()->query()) }}"
                                   class="btn btn-success">
                                    <i class="bi bi-download me-1"></i> Exportar Excel
                                </a>
                            @endif

                            <a href="{{ route('recados.index') }}"
                               class="btn btn-outline-secondary ms-auto">
                                <i class="bi bi-x-circle me-1"></i> Limpar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    {{-- ✅ EM BAIXO: TABELA --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <div class="fw-semibold">
                <i class="bi bi-table me-1"></i> Lista
            </div>

            {{-- ✅ COLUNAS VISÍVEIS (frontend) + paginação --}}
            <div class="d-flex align-items-center gap-2">
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-columns-gap me-1"></i> Colunas
                    </button>

                    <div class="dropdown-menu dropdown-menu-end p-3 shadow" style="min-width: 240px;">
                        <div class="fw-semibold mb-2">Mostrar colunas</div>

                        <div class="d-grid gap-2" id="colsMenu">
                            {{-- checkboxes gerados por JS --}}
                        </div>

                        <hr class="my-3">

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary w-50" id="colsReset">
                                Reset
                            </button>
                            <button type="button" class="btn btn-sm btn-primary w-50" id="colsAll">
                                Mostrar tudo
                            </button>
                        </div>

                        <div class="small text-muted mt-2">
                            A escolha fica guardada neste browser.
                        </div>
                    </div>
                </div>

                <div class="text-muted small">
                    Página {{ $recados->currentPage() }} de {{ $recados->lastPage() }}
                </div>
            </div>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        @php $sortDir = request('sort_dir', 'desc') === 'asc' ? 'desc' : 'asc'; @endphp

                        <th data-col="id" style="width: 90px;">
                            <a href="{{ route('recados.index', array_merge(request()->query(), ['sort_by' => 'id', 'sort_dir' => $sortDir])) }}"
                               class="text-decoration-none d-inline-flex align-items-center gap-1">
                                ID
                                @if(request('sort_by') === 'id')
                                    <i class="bi {{ request('sort_dir') === 'asc' ? 'bi-sort-up' : 'bi-sort-down' }}"></i>
                                @else
                                    <i class="bi bi-arrow-down-up text-muted"></i>
                                @endif
                            </a>
                        </th>

                        <th data-col="nome">Nome</th>
                        <th data-col="contacto">Contacto</th>
                        <th data-col="matricula">Matrícula</th>

                        <th data-col="chefia">Chefia</th>
                        <th data-col="departamento">Departamento</th>
                        <th data-col="origem">Origem</th>
                        <th data-col="sla">SLA</th>
                        <th data-col="operador">Email do Operador</th>

                        <th data-col="destinatarios">Destinatários</th>
                        <th data-col="estado">Estado</th>

                        {{-- ✅ NOVO: Tipo (tipo_id -> tabela tipos) --}}
                        <th data-col="tipo_recado">Tipo</th>

                        {{-- Já tinhas: TipoFormulário --}}
                        <th data-col="tipo">TipoFormulário</th>

                        <th data-col="abertura" class="text-nowrap">Abertura</th>
                        <th data-col="acoes" class="text-center" style="width: 90px;">Ações</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($recados as $recado)
                        <tr class="clickable-row" data-href="{{ route('recados.show', $recado->id) }}">
                            <td data-col="id" class="fw-semibold">#{{ $recado->id }}</td>
                            <td data-col="nome" class="fw-semibold">{{ $recado->name }}</td>
                            <td data-col="contacto">{{ $recado->contact_client }}</td>
                            <td data-col="matricula">{{ $recado->plate ?? '—' }}</td>

                            <td data-col="chefia" class="small">
                                {{ $recado->chefia->name ?? '—' }}
                            </td>

                            <td data-col="departamento" class="small">
                                {{ $recado->departamento->name ?? '—' }}
                            </td>

                            <td data-col="origem" class="small">
                                {{ $recado->origem->name ?? '—' }}
                            </td>

                            <td data-col="sla" class="small">
                                {{ $recado->sla->name ?? '—' }}
                            </td>

                            <td data-col="operador" class="small text-truncate" style="max-width: 220px;">
                                <span title="{{ $recado->operator_email ?? '' }}">
                                    {{ $recado->operator_email ?? '—' }}
                                </span>
                            </td>

                            {{-- Destinatários --}}
                            <td data-col="destinatarios" class="small">
                                @php
                                    $destinatarios = collect();
                                    if($recado->destinatarios->count()) {
                                        $destinatarios = $destinatarios->merge($recado->destinatarios->pluck('name'));
                                    }
                                    if($recado->grupos->count()) {
                                        $destinatarios = $destinatarios->merge($recado->grupos->pluck('name'));
                                    }
                                    if($recado->guestTokens->count()) {
                                        $destinatarios = $destinatarios->merge($recado->guestTokens->pluck('email'));
                                    }
                                    $destinatarios = $destinatarios->unique();
                                @endphp
                                {!! $destinatarios->implode('<br>') !!}
                            </td>

                            {{-- Estado --}}
                            <td data-col="estado">
                                @php
                                    $estadoNome = strtolower($recado->estado->name ?? '');
                                    $badgeEstado = match($estadoNome) {
                                        'novo' => 'bg-info text-white',
                                        'pendente' => 'bg-warning text-dark',
                                        'tratado' => 'bg-purple text-white',
                                        default => 'bg-secondary text-white'
                                    };
                                @endphp
                                <span class="badge rounded-pill {{ $badgeEstado }}">
                                    {{ $estadoNome ? ucfirst($estadoNome) : '—' }}
                                </span>
                            </td>

                            {{-- ✅ NOVO: Tipo (tabela tipos) --}}
                            <td data-col="tipo_recado" class="small">
                                @php
                                    $tipoRecadoNome = strtolower($recado->tipo->name ?? '');
                                    $badgeTipoRecado = match($tipoRecadoNome) {
                                        default => 'bg-dark text-white'
                                    };
                                @endphp
                                <span class="badge rounded-pill {{ $badgeTipoRecado }}">
                                    {{ $recado->tipo->name ?? '—' }}
                                </span>
                            </td>

                            {{-- TipoFormulário --}}
                            <td data-col="tipo">
                                @php
                                    $tipoFormularioNome = strtolower($recado->tipoFormulario->name ?? '');
                                    $badgeTipoFormulario = match($tipoFormularioNome) {
                                        'central' => 'bg-primary text-white',
                                        'call center' => 'bg-success text-white',
                                        default => 'bg-secondary text-white'
                                    };
                                @endphp
                                <span class="badge rounded-pill {{ $badgeTipoFormulario }}">
                                    {{ $tipoFormularioNome ? ucfirst($tipoFormularioNome) : '—' }}
                                </span>
                            </td>

                            <td data-col="abertura" class="text-nowrap">
                                {{ $recado->abertura ? \Carbon\Carbon::parse($recado->abertura)->format('d/m/Y H:i') : '—' }}
                            </td>

                            <td data-col="acoes" class="text-center" onclick="event.stopPropagation();">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-sm btn-light border"
                                        type="button"
                                        data-bs-toggle="dropdown"
                                        aria-expanded="false"
                                        onclick="event.stopPropagation();"
                                    >
                                        <i class="bi bi-three-dots-vertical"></i>
                                    </button>

                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                        <li>
                                            <a class="dropdown-item" href="{{ route('recados.edit', $recado->id) }}">
                                                ✏️ Editar
                                            </a>
                                        </li>

                                        @if(optional(auth()->user()->cargo)->name === 'admin')
                                            <li><hr class="dropdown-divider"></li>

                                            <li>
                                                <form action="{{ route('recados.destroy', $recado->id) }}" method="POST"
                                                      onsubmit="return confirm('Tem a certeza que deseja eliminar este recado?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        🗑️ Apagar
                                                    </button>
                                                </form>
                                            </li>
                                        @endif
                                    </ul>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="16" class="text-center text-muted py-4">
                                <div class="d-flex flex-column align-items-center gap-2">
                                    <i class="bi bi-inbox fs-2"></i>
                                    <div>Nenhum recado encontrado.</div>
                                </div>
                            </td>
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
    .bg-info { background-color: #17a2b8 !important; }

    .clickable-row { cursor: pointer; transition: background-color 0.15s ease, transform 0.05s ease; }
    .clickable-row:hover { background-color: #f8f9fa; }
    .clickable-row:active { transform: scale(0.999); }

    .card { border-radius: 16px; }
    .card-header { border-top-left-radius: 16px; border-top-right-radius: 16px; }

    .table > :not(caption) > * > * { padding-top: .85rem; padding-bottom: .85rem; }

    /* ✅ esconder colunas (frontend only) */
    .col-hidden { display: none !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // clique na linha
    document.querySelectorAll('.clickable-row').forEach(row =>
        row.addEventListener('click', () => window.location.href = row.dataset.href)
    );

    // ---- Colunas (frontend only) ----
    const table = document.querySelector('table');
    if (!table) return;

    const storageKey = 'recados_cols_v1';
    const colsMenu = document.getElementById('colsMenu');
    const btnReset = document.getElementById('colsReset');
    const btnAll = document.getElementById('colsAll');

    // lista de colunas a partir do thead
    const ths = Array.from(table.querySelectorAll('thead th[data-col]'));
    const colDefs = ths.map(th => ({
        key: th.dataset.col,
        label: (th.innerText || th.textContent || th.dataset.col).trim()
    }));

    // default: tudo visível
    const defaultState = Object.fromEntries(colDefs.map(c => [c.key, true]));

    // ler estado guardado
    let state;
    try {
        state = JSON.parse(localStorage.getItem(storageKey)) || defaultState;
    } catch (e) {
        state = defaultState;
    }

    // garantir que novas colunas entram como true
    colDefs.forEach(c => {
        if (typeof state[c.key] !== 'boolean') state[c.key] = true;
    });

    function applyState() {
        colDefs.forEach(c => {
            const visible = !!state[c.key];

            // TH
            table.querySelectorAll(`thead th[data-col="${c.key}"]`)
                .forEach(el => el.classList.toggle('col-hidden', !visible));

            // TD
            table.querySelectorAll(`tbody td[data-col="${c.key}"]`)
                .forEach(el => el.classList.toggle('col-hidden', !visible));
        });

        localStorage.setItem(storageKey, JSON.stringify(state));
    }

    function renderMenu() {
        if (!colsMenu) return;
        colsMenu.innerHTML = '';

        colDefs.forEach(c => {
            // ✅ impedir esconder "Ações"
            if (c.key === 'acoes') return;

            const id = `col_${c.key}`;
            const wrapper = document.createElement('label');
            wrapper.className = 'form-check d-flex align-items-center gap-2 mb-0';

            const input = document.createElement('input');
            input.type = 'checkbox';
            input.className = 'form-check-input';
            input.id = id;
            input.checked = !!state[c.key];

            input.addEventListener('change', () => {
                state[c.key] = input.checked;
                applyState();
            });

            const span = document.createElement('span');
            span.className = 'form-check-label';
            span.textContent = c.label;

            wrapper.appendChild(input);
            wrapper.appendChild(span);
            colsMenu.appendChild(wrapper);
        });
    }

    // botões
    if (btnReset) {
        btnReset.addEventListener('click', () => {
            state = { ...defaultState };
            renderMenu();
            applyState();
        });
    }

    if (btnAll) {
        btnAll.addEventListener('click', () => {
            colDefs.forEach(c => state[c.key] = true);
            renderMenu();
            applyState();
        });
    }

    renderMenu();
    applyState();
});
</script>
