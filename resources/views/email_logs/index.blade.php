@extends('layouts.app')

@section('content')
<div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <h2 class="fw-bold mb-0">📨 Logs de Emails</h2>
        <span class="text-muted small">Total nesta página: {{ $logs->count() }}</span>
    </div>

    {{-- Filtros --}}
    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('email_logs.index') }}" class="row g-2 align-items-end">

                <div class="col-md-2">
                    <label class="form-label small">Estado</label>
                    <select name="status" class="form-select">
                        <option value="">Todos</option>
                        @foreach($statuses as $st)
                            <option value="{{ $st }}" @selected(request('status')===$st)>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label small">Tipo (mail_type)</label>
                    <input type="text" name="mail_type" value="{{ request('mail_type') }}" class="form-control" placeholder="RecadoCriadoMail">
                </div>

                <div class="col-md-3">
                    <label class="form-label small">Destinatário (email)</label>
                    <input type="text" name="to" value="{{ request('to') }}" class="form-control" placeholder="ex: joao@...">
                </div>

                <div class="col-md-2">
                    <label class="form-label small">Recado ID</label>
                    <input type="number" name="recado_id" value="{{ request('recado_id') }}" class="form-control" placeholder="123">
                </div>

                <div class="col-md-1">
                    <label class="form-label small">De</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
                </div>

                <div class="col-md-1">
                    <label class="form-label small">Até</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
                </div>

                <div class="col-12 d-flex gap-2 mt-2">
                    <button class="btn btn-primary">
                        🔎 Filtrar
                    </button>

                    <a href="{{ route('email_logs.index') }}" class="btn btn-light border">
                        Limpar
                    </a>
                </div>

            </form>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th>Tipo</th>
                        <th>Recado</th>
                        <th>Para</th>
                        <th>Assunto</th>
                        <th class="text-end">Ações</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($logs as $log)
                    @php
                        $to = collect($log->to ?? [])->pluck('email')->filter()->implode(', ');
                    @endphp
                    <tr>
                        <td class="fw-semibold">{{ $log->id }}</td>
                        <td class="text-muted small">{{ optional($log->created_at)->format('d/m/Y H:i') }}</td>

                        <td>
                            @php
                                $badge = match($log->status) {
                                    'sent' => 'success',
                                    'failed' => 'danger',
                                    'sending' => 'warning',
                                    default => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $badge }}">{{ $log->status }}</span>
                        </td>

                        <td class="small">{{ class_basename($log->mail_type) }}</td>

                        <td>
                            @if($log->recado_id)
                                <span class="badge bg-light text-dark border">#{{ $log->recado_id }}</span>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>

                        <td class="small" style="max-width: 260px;">
                            <div class="text-truncate" title="{{ $to }}">{{ $to ?: '—' }}</div>
                        </td>

                        <td class="small" style="max-width: 280px;">
                            <div class="text-truncate" title="{{ $log->subject }}">{{ $log->subject ?: '—' }}</div>
                        </td>

                        <td class="text-end">
                            <a href="{{ route('email_logs.show', $log) }}" class="btn btn-sm btn-light border">
                                Ver
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Sem logs com estes filtros.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-body border-top">
            {{ $logs->links() }}
        </div>
    </div>

</div>
@endsection
