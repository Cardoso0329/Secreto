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
                <div class="card-body d-flex flex-column gap-3">
                    <h5 class="fw-semibold mb-3">🔗 Informações Relacionadas</h5>
                    <p><strong>SLA:</strong> {{ $recado->sla->name ?? '—' }}</p>
                    <p><strong>Tipo:</strong> {{ $recado->tipo->name ?? '—' }}</p>
                    <p><strong>Origem:</strong> {{ $recado->origem->name ?? '—' }}</p>
                    <p><strong>Setor:</strong> {{ $recado->setor->name ?? '—' }}</p>
                    <p><strong>Departamento:</strong> {{ $recado->departamento->name ?? '—' }}</p>

                    {{-- Estado editável --}}
                    <form action="{{ route('recados.estado.update', $recado) }}" method="POST" class="mb-3">
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

                    {{-- Aviso --}}
                    <p><strong>Aviso:</strong> {{ $recado->aviso->name ?? '—' }}</p>
                </div>
            </div>
        </div>

        {{-- Coluna 3 - Destinatários & Mensagem --}}
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

                    @if($recado->grupos?->count())
                        <p><strong>Grupos:</strong>
                            @foreach($recado->grupos as $grupo)
                                {{ $grupo->name }}{{ !$loop->last ? ',' : '' }}
                            @endforeach
                        </p>
                    @endif

                    @if($recado->guestTokens?->count())
                        <p><strong>Destinatários Livres:</strong></p>
                        <ul>
                            @foreach($recado->guestTokens as $token)
                                <li>{{ $token->email }}</li>
                            @endforeach
                        </ul>
                    @else
                        <p><strong>Destinatários Livres:</strong> —</p>
                    @endif

                    <p><strong>Mensagem:</strong></p>
                    <p>{{ $recado->mensagem }}</p>

                    <p><strong>Ficheiro:</strong></p>
                    @if ($recado->ficheiro)
                        @php
                            $ext = pathinfo($recado->ficheiro, PATHINFO_EXTENSION);
                            $fileUrl = asset('storage/recados/' . $recado->ficheiro);
                            $imageExtensions = ['jpg','jpeg','png','gif','webp'];
                        @endphp

                        @if(in_array(strtolower($ext), $imageExtensions))
                            <img src="{{ $fileUrl }}" alt="Pré-visualização" class="img-fluid rounded mb-2" style="max-height: 250px;">
                        @elseif(strtolower($ext) === 'pdf')
                            <iframe src="{{ $fileUrl }}" class="w-100 rounded mb-2" style="height: 300px;"></iframe>
                        @else
                            <p class="text-muted">Pré-visualização indisponível.</p>
                        @endif

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

    {{-- Comentários estilo chat --}}
    <div class="mt-5 mx-auto" style="max-width: 700px;">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">💬 Comentários</h5>

                <div id="chatArea" class="p-3 rounded bg-light mb-3" style="max-height: 400px; overflow-y: auto;">
                    @php
                        $comentarios = array_filter(explode("\n", $recado->observacoes));
                        $ultimaData = null;
                    @endphp

                    @forelse($comentarios as $linha)
                        @php
                            if (preg_match('/^(\d{2}) - (.+?): (.+)$/', $linha, $matches)) {
                                $autor = trim($matches[2]);
                                $mensagem = trim($matches[3]);
                                $dataAtual = \Carbon\Carbon::now()->format('d/m/Y');
                            } else {
                                $autor = 'Convidado';
                                $mensagem = $linha;
                                $dataAtual = \Carbon\Carbon::now()->format('d/m/Y');
                            }
                        @endphp

                        @if ($ultimaData !== $dataAtual)
                            <div class="text-center text-muted my-3" style="font-size: 0.85rem;">
                                {{ $dataAtual }}
                            </div>
                            @php $ultimaData = $dataAtual; @endphp
                        @endif

                        <div class="d-flex flex-column mb-2 {{ $autor === auth()->user()->name ? 'align-items-end' : 'align-items-start' }}">
                            <small class="text-muted mb-1" style="font-size: 0.8rem;">{{ $autor }}</small>
                            <div class="px-3 py-2 rounded-4 shadow-sm"
                                 style="max-width: 75%;
                                        background-color: {{ $autor === auth()->user()->name ? '#007bff' : '#ffffff' }};
                                        color: {{ $autor === auth()->user()->name ? '#fff' : '#333' }};
                                        border: 1px solid #e0e0e0;">
                                {{ $mensagem }}
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

    <script>
        const chatArea = document.getElementById('chatArea');
        chatArea.scrollTop = chatArea.scrollHeight;
    </script>

</div>
@endsection
