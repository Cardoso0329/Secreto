@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 1100px;">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h2 class="fw-bold mb-0">🔎 Log #{{ $auditLog->id }}</h2>
            <div class="text-muted small">{{ optional($auditLog->created_at)->format('Y-m-d H:i:s') }}</div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('audit_logs.index') }}" class="btn btn-light border">← Voltar</a>

            {{-- Atalho: se for Recado, tenta ir ao show --}}
            @if($auditLog->auditable_type === \App\Models\Recado::class && $auditLog->auditable_id)
                <a href="{{ route('recados.show', $auditLog->auditable_id) }}" class="btn btn-outline-success">
                    Abrir Recado
                </a>
            @endif
        </div>
    </div>

    <div class="card shadow-sm border-0 mb-3">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="text-muted small">Evento</div>
                    <div class="fw-semibold">{{ $auditLog->event }}</div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">Utilizador</div>
                    <div class="fw-semibold">
                        {{ $auditLog->user?->name ?? '—' }}
                    </div>
                    <div class="text-muted small">
                        {{ $auditLog->user?->email ?? '' }}
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">Auditable</div>
                    <div class="fw-semibold">{{ $auditLog->auditable_type ?? '—' }}</div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">Auditable ID</div>
                    <div class="fw-semibold">{{ $auditLog->auditable_id ?? '—' }}</div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">Route</div>
                    <div class="fw-semibold">{{ $auditLog->route ?? '—' }}</div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">Method</div>
                    <div class="fw-semibold">{{ $auditLog->method ?? '—' }}</div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">IP</div>
                    <div class="fw-semibold">{{ $auditLog->ip ?? '—' }}</div>
                </div>

                <div class="col-md-3">
                    <div class="text-muted small">URL</div>
                    <div class="fw-semibold text-truncate" title="{{ $auditLog->url }}">
                        {{ $auditLog->url ?? '—' }}
                    </div>
                </div>
            </div>

            <hr>

            <div class="text-muted small mb-1">User-Agent</div>
            <div class="small" style="word-break: break-word;">
                {{ $auditLog->user_agent ?? '—' }}
            </div>
        </div>
    </div>

    <div class="row g-3">

        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light fw-semibold">Old Values</div>
                <div class="card-body">
                    <pre class="mb-0 small">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light fw-semibold">New Values</div>
                <div class="card-body">
                    <pre class="mb-0 small">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>

        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light fw-semibold">Meta</div>
                <div class="card-body">
                    <pre class="mb-0 small">{{ json_encode($auditLog->meta, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>

    </div>

</div>
@endsection
