@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h2 class="fw-bold mb-0">🧾 Audit Logs</h2>
            <div class="text-muted small">Registo de ações no sistema (exceto emails)</div>
        </div>
        <a href="{{ route('audit_logs.index') }}" class="btn btn-light border">
            Limpar filtros
        </a>
    </div>

    {{-- Filtros --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('audit_logs.index') }}" class="row g-2 align-items-end">

                <div class="col-md-2">
                    <label class="form-label small">Evento</label>
                    <select name="event" class="form-select">
                        <option value="">Todos</option>
                        @foreach($events as $ev)
                            <option value="{{ $ev }}" @selected(request('event')===$ev)>{{ $ev }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small">Utilizador</label>
                    <select name="user_id" class="form-select">
                        <option value="">Todos</option>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" @selected((string)request('user_id')===(string)$u->id)>
                                {{ $u->name }} ({{ $u->email }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small">Modelo (auditable_type)</label>
                    <select name="auditable_type" class="form-select">
                        <option value="">Todos</option>
                        @foreach($auditableTypes as $t)
                            <option value="{{ $t }}" @selected(request('auditable_type')===$t)>{{ $t }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small">ID do registo (auditable_id)</label>
                    <input type="number" name="auditable_id" class="form-control"
                           value="{{ request('auditable_id') }}" placeholder="ex: 123">
                </div>

                <div class="col-md-2">
                    <label class="form-label small">IP</label>
                    <input type="text" name="ip" class="form-control"
                           value="{{ request('ip') }}" placeholder="ex: 192.168">
                </div>

                <div class="col-md-2">
                    <label class="form-label small">Route</label>
                    <input type="text" name="route" class="form-control"
                           value="{{ request('route') }}" placeholder="ex: recados.index">
                </div>

                <div class="col-md-2">
                    <label class="form-label small">Método</label>
                    <select name="method" class="form-select">
                        <option value="">Todos</option>
                        @foreach(['GET','POST','PUT','PATCH','DELETE'] as $m)
                            <option value="{{ $m }}" @selected(strtoupper(request('method'))===$m)>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small">De</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label small">Até</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label small">Pesquisa</label>
                    <input type="text" name="search" class="form-control"
                           value="{{ request('search') }}"
                           placeholder="URL, user-agent, meta, old_values, new_values...">
                </div>

                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">
                        Filtrar
                    </button>
                </div>

            </form>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">

            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                <div class="text-muted small">
                    Total nesta página: <strong>{{ $logs->count() }}</strong> (total geral: {{ $logs->total() }})
                </div>
                <div class="text-muted small">
                    Página {{ $logs->currentPage() }} de {{ $logs->lastPage() }}
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width:80px">#</th>
                            <th>Evento</th>
                            <th>User</th>
                            <th>Modelo</th>
                            <th style="width:110px">ID</th>
                            <th>Route</th>
                            <th style="width:80px">Método</th>
                            <th style="width:140px">IP</th>
                            <th style="width:170px">Data</th>
                            <th style="width:90px"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="text-muted">#{{ $log->id }}</td>
                                <td>
                                    <span class="badge bg-dark">{{ $log->event }}</span>
                                </td>
                                <td>
                                    @if($log->user)
                                        <div class="fw-semibold">{{ $log->user->name }}</div>
                                        <div class="text-muted small">{{ $log->user->email }}</div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-muted">
                                    {{ $log->auditable_type ?? '—' }}
                                </td>
                                <td>
                                    @if($log->auditable_id)
                                        <span class="fw-semibold">{{ $log->auditable_id }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $log->route ?? '—' }}
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $log->method ?? '—' }}</span>
                                </td>
                                <td class="text-muted small">
                                    {{ $log->ip ?? '—' }}
                                </td>
                                <td class="text-muted small">
                                    {{ optional($log->created_at)->format('Y-m-d H:i') }}
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('audit_logs.show', $log) }}" class="btn btn-sm btn-outline-primary">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    Sem logs com estes filtros.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    <div class="mt-3">
        {{ $logs->links() }}
    </div>

</div>
@endsection
