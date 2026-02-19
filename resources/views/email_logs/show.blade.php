@extends('layouts.app')

@section('content')
<div class="container py-4" style="max-width: 1100px;">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-1">📨 Email Log #{{ $emailLog->id }}</h2>
            <div class="text-muted small">
                Criado em: {{ optional($emailLog->created_at)->format('d/m/Y H:i:s') }}
            </div>
        </div>

        <a href="{{ route('email_logs.index') }}" class="btn btn-light border">← Voltar</a>
    </div>

    @php
        $badge = match($emailLog->status) {
            'sent' => 'success',
            'failed' => 'danger',
            'sending' => 'warning',
            default => 'secondary',
        };
        $to = collect($emailLog->to ?? [])->pluck('email')->filter()->implode(', ');
        $cc = collect($emailLog->cc ?? [])->pluck('email')->filter()->implode(', ');
        $bcc = collect($emailLog->bcc ?? [])->pluck('email')->filter()->implode(', ');
    @endphp

    <div class="card shadow-sm border-0">
        <div class="card-body">

            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-{{ $badge }}">{{ $emailLog->status }}</span>
                @if($emailLog->recado_id)
                    <span class="badge bg-light text-dark border">Recado #{{ $emailLog->recado_id }}</span>
                @endif
                <span class="badge bg-light text-dark border">Mailer: {{ $emailLog->mailer ?: '—' }}</span>
                <span class="badge bg-light text-dark border">Env: {{ $emailLog->app_env ?: '—' }}</span>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="small text-muted">Tipo</div>
                    <div class="fw-semibold">{{ $emailLog->mail_type }}</div>
                </div>

                <div class="col-md-6">
                    <div class="small text-muted">Assunto</div>
                    <div class="fw-semibold">{{ $emailLog->subject ?: '—' }}</div>
                </div>

                <div class="col-md-12">
                    <div class="small text-muted">Para</div>
                    <div class="fw-semibold">{{ $to ?: '—' }}</div>
                </div>

                @if($cc)
                <div class="col-md-12">
                    <div class="small text-muted">CC</div>
                    <div class="fw-semibold">{{ $cc }}</div>
                </div>
                @endif

                @if($bcc)
                <div class="col-md-12">
                    <div class="small text-muted">BCC</div>
                    <div class="fw-semibold">{{ $bcc }}</div>
                </div>
                @endif

                <div class="col-md-4">
                    <div class="small text-muted">Sent at</div>
                    <div class="fw-semibold">{{ $emailLog->sent_at ? $emailLog->sent_at->format('d/m/Y H:i:s') : '—' }}</div>
                </div>

                <div class="col-md-4">
                    <div class="small text-muted">Failed at</div>
                    <div class="fw-semibold">{{ $emailLog->failed_at ? $emailLog->failed_at->format('d/m/Y H:i:s') : '—' }}</div>
                </div>

                <div class="col-md-4">
                    <div class="small text-muted">Duration</div>
                    <div class="fw-semibold">{{ $emailLog->duration_ms ? $emailLog->duration_ms.' ms' : '—' }}</div>
                </div>

                <div class="col-md-12">
                    <hr>
                    <div class="small text-muted mb-1">Body (HTML guardado)</div>

                    @if($emailLog->body)
                        <div class="border rounded p-2 bg-light" style="max-height: 420px; overflow:auto;">
                            {!! $emailLog->body !!}
                        </div>
                    @else
                        <div class="text-muted">Sem body guardado.</div>
                    @endif
                </div>

                @if($emailLog->error_message)
                <div class="col-md-12">
                    <hr>
                    <div class="small text-muted mb-1">Erro</div>
                    <div class="alert alert-danger mb-2">
                        <strong>{{ $emailLog->error_message }}</strong>
                    </div>

                    @if($emailLog->error_trace)
                        <details>
                            <summary class="text-muted">Ver trace</summary>
                            <pre class="mt-2 small bg-dark text-light p-2 rounded" style="max-height: 300px; overflow:auto;">{{ $emailLog->error_trace }}</pre>
                        </details>
                    @endif
                </div>
                @endif

            </div>

        </div>
    </div>

</div>
@endsection
