@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Título --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="fw-bold mb-0">
            📌 Recado #{{ $recado->id }} <span class="text-muted fs-6">(Acesso Convidado)</span>
        </h2>
    </div>

    <div class="row g-4">
        {{-- Coluna 1 - Dados principais --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">📑 Dados Principais</h5>
                    <p><strong>Nome:</strong> {{ $recado->name }}</p>
                    <p><strong>Contacto:</strong> {{ $recado->contact_client }}</p>
                    <p><strong>Matrícula:</strong> {{ $recado->plate ?? '—' }}</p>
                    <p><strong>Email Operador:</strong> {{ $recado->operator_email ?? '—' }}</p>
                    @if (!empty($recado->wip))
                        <p><strong>WIP:</strong> {{ $recado->wip }}</p>
                    @endif
                    <p><strong>Abertura:</strong> {{ $recado->abertura ? $recado->abertura->format('d/m/Y H:i') : '—' }}</p>
                    <p><strong>Término:</strong> {{ $recado->termino ? $recado->termino->format('d/m/Y H:i') : '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Coluna 2 - Relações + Formulário --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">🔗 Informações Relacionadas</h5>
                    <p><strong>SLA:</strong> {{ $recado->sla->name ?? '—' }}</p>
                    <p><strong>Tipo:</strong> {{ $recado->tipo->name ?? '—' }}</p>
                    <p><strong>Origem:</strong> {{ $recado->origem->name ?? '—' }}</p>
                    <p><strong>Setor:</strong> {{ $recado->setor->name ?? '—' }}</p>
                    <p><strong>Departamento:</strong> {{ $recado->departamento->name ?? '—' }}</p>
                    <p><strong>Aviso:</strong> {{ $recado->aviso->name ?? '—' }}</p>

                    {{-- Formulário --}}
                    <hr>
                    <p><strong>Estado:</strong> {{ $recado->estado->name ?? '—' }}</p>
                    <form action="{{ route('recados.guest.update', $token) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="mensagem" class="form-label"><strong>Mensagem:</strong></label>
                            <textarea id="mensagem" name="mensagem" class="form-control" rows="4" required>{{ old('mensagem', $recado->mensagem) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-save"></i> Atualizar Recado
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Coluna 3 - Destinatários + Arquivo --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">👥 Destinatários & Arquivos</h5>
                    <p><strong>Destinatários:</strong>
                        @forelse($recado->destinatarios as $user)
                            {{ $user->name }}{{ !$loop->last ? ',' : '' }}
                        @empty
                            <span class="text-muted">Nenhum</span>
                        @endforelse
                    </p>

                    <p><strong>Destinatário Livre:</strong> {{ $token->email ?? '—' }}</p>
                    
                    <p><strong>Ficheiro:</strong></p>
@if ($recado->ficheiro)
    @php
        $ext = pathinfo($recado->ficheiro, PATHINFO_EXTENSION);
        $fileUrl = asset('storage/recados/' . $recado->ficheiro);
        $imageExtensions = ['jpg','jpeg','png','gif','webp'];
    @endphp

    {{-- Pré-visualização para imagens --}}
    @if(in_array(strtolower($ext), $imageExtensions))
        <div class="mb-2">
            <img src="{{ $fileUrl }}" alt="Pré-visualização" class="img-fluid rounded" style="max-height: 300px;">
        </div>
    @elseif(strtolower($ext) === 'pdf')
        <div class="mb-2">
            <iframe src="{{ $fileUrl }}" class="w-100 rounded" style="height: 400px;"></iframe>
        </div>
    @else
        <p class="text-muted">Pré-visualização não disponível para este tipo de ficheiro.</p>
    @endif

    {{-- Botões de Ver e Download --}}
    <div class="d-flex gap-2">
        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-eye"></i> Abrir
        </a>
        <a href="{{ $fileUrl }}" download class="btn btn-success btn-sm">
            <i class="bi bi-download"></i> Download
        </a>
    </div>
@else
    <span class="text-muted">Sem ficheiro</span>
@endif

                </div>
            </div>
        </div>
    </div>


</div>
@endsection
