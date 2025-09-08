@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Cabeçalho com botão concluir --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="fw-bold mb-0">📌 Recado #{{ $recado->id }}</h2>

        @if ($recado->estado->name !== 'Tratado')
            <form action="{{ route('recados.concluir', $recado) }}" method="POST">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-success d-flex align-items-center gap-2">
                    <i class="bi bi-check-circle"></i> Concluir
                </button>
            </form>
        @endif
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
                    <p><strong>Abertura:</strong> {{ $recado->abertura ? $recado->abertura->format('d/m/Y H:i') : '—' }}</p>
                    <p><strong>Término:</strong> {{ $recado->termino ? $recado->termino->format('d/m/Y H:i') : '—' }}</p>
                    @if (!empty($recado->wip))
                        <p><strong>WIP:</strong> {{ $recado->wip }}</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Coluna 2 - Relações + Estado --}}
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

                    {{-- Estado editável --}}
                    <hr>
                    <form action="{{ route('recados.estado.update', $recado) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <label for="estado_id" class="form-label"><strong>Estado:</strong></label>
                        <select name="estado_id" id="estado_id" class="form-select" onchange="this.form.submit()">
                            @foreach($estados as $estado)
                                <option value="{{ $estado->id }}" {{ $recado->estado_id == $estado->id ? 'selected' : '' }}>
                                    {{ $estado->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </div>
        </div>

        {{-- Coluna 3 - Destinatários e Mensagem --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">👥 Destinatários & Mensagem</h5>
                    <p><strong>Destinatários:</strong>
                        @forelse($recado->destinatarios as $user)
                            {{ $user->name }}{{ !$loop->last ? ',' : '' }}
                        @empty
                            <span class="text-muted">Nenhum</span>
                        @endforelse
                    </p>

                    @if($recado->guestTokens->count())
    <p><strong>Destinatários Livres:</strong></p>
    <ul>
        @foreach($recado->guestTokens as $token)
            <li>{{ $token->email }}</li>
        @endforeach
    </ul>
@else
    <p><strong>Destinatários Livres:</strong> —</p>
@endif


                    <p><strong>Mensagem:</strong> {{ $recado->mensagem }}</p>

                    <p><strong>Ficheiro:</strong></p>
                    @if ($recado->ficheiro)
                        <div class="d-flex gap-2">
                            <a href="{{ asset('storage/recados/' . $recado->ficheiro) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                            <a href="{{ asset('storage/recados/' . $recado->ficheiro) }}" download class="btn btn-success btn-sm">
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

    {{-- Comentários --}}
    <div class="mt-5 mx-auto" style="max-width: 700px;">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">💬 Comentários</h5>

                {{-- Área estilo chat --}}
                <div class="p-3 rounded bg-light mb-3" style="max-height: 350px; overflow-y: auto;">
                    @php
                        $comentarios = array_filter(explode("\n", $recado->observacoes));
                    @endphp
                    @forelse($comentarios as $i => $linha)
                        <div class="d-flex mb-2 {{ $i % 2 === 0 ? 'justify-content-start' : 'justify-content-end' }}">
                            <div class="px-3 py-2 rounded-3 small 
                                {{ $i % 2 === 0 ? 'bg-white border text-dark' : 'bg-primary text-white' }}"
                                style="max-width: 75%;">
                                {{ trim($linha) }}
                            </div>
                        </div>
                    @empty
                        <p class="text-muted">Sem comentários ainda.</p>
                    @endforelse
                </div>

                {{-- Novo comentário --}}
                <form action="{{ route('recados.observacoes.update', $recado) }}" method="POST" class="d-flex gap-2">
                    @csrf
                    @method('PUT')
                    <input type="text" name="comentario" class="form-control rounded-pill" placeholder="Escreve um comentário..." required>
                    <button class="btn btn-primary rounded-pill d-flex align-items-center gap-2" type="submit">
                        <i class="bi bi-send"></i> Enviar
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
