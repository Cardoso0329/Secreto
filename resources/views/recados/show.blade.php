@extends('layouts.app')

@section('content')
<div class="container mt-4">

    {{-- Cabeçalho com botão concluir --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mb-0">Recado #{{ $recado->id }}</h1>

        @if ($recado->estado->name !== 'Tratado')
            <form action="{{ route('recados.concluir', $recado) }}" method="POST" class="ms-3">
                @csrf
                @method('PUT')
                <button type="submit" class="btn btn-success">Concluir</button>
            </form>
        @endif
    </div>

    {{-- Linha com as 3 colunas principais --}}
    <div class="row">
        {{-- Coluna 1 - Dados principais --}}
        <div class="col-md-4">
            <p><strong>Nome:</strong> {{ $recado->name }}</p>
            <p><strong>Contacto:</strong> {{ $recado->contact_client }}</p>
            <p><strong>Matrícula:</strong> {{ $recado->plate }}</p>
            <p><strong>Email Operador:</strong> {{ $recado->operator_email ?? '-' }}</p>
            <p><strong>Abertura:</strong> {{ $recado->abertura ? $recado->abertura->format('d/m/Y H:i') : '-' }}</p>
            <p><strong>Término:</strong> {{ $recado->termino ? $recado->termino->format('d/m/Y H:i') : '-' }}</p>

            @if (!empty($recado->wip))
                <p><strong>WIP:</strong> {{ $recado->wip }}</p>
            @endif
        </div>

        {{-- Coluna 2 - Relações --}}
        <div class="col-md-4">
            <p><strong>SLA:</strong> {{ $recado->sla->name ?? '-' }}</p>
            <p><strong>Tipo:</strong> {{ $recado->tipo->name ?? '-' }}</p>
            <p><strong>Origem:</strong> {{ $recado->origem->name ?? '-' }}</p>
            <p><strong>Setor:</strong> {{ $recado->setor->name ?? '-' }}</p>
            <p><strong>Departamento:</strong> {{ $recado->departamento->name ?? '-' }}</p>
            <p><strong>Aviso:</strong> {{ $recado->aviso->name ?? '-' }}</p>

            {{-- Estado editável --}}
            <form action="{{ route('recados.estado.update', $recado) }}" method="POST" class="mb-3">
                @csrf
                @method('PUT')
                <label for="estado_id"><strong>Estado:</strong></label>
                <select name="estado_id" id="estado_id" class="form-select" onchange="this.form.submit()">
                    @foreach($estados as $estado)
                        <option value="{{ $estado->id }}" {{ $recado->estado_id == $estado->id ? 'selected' : '' }}>
                            {{ $estado->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        {{-- Coluna 3 - Destinatários e mensagem --}}
        <div class="col-md-4">
            <p><strong>Destinatários:</strong>
                @foreach($recado->destinatarios as $user)
                    {{ $user->name }}{{ !$loop->last ? ',' : '' }}
                @endforeach
            </p>

            <p><strong>Destinatário Livre:</strong> {{ $recado->destinatario_livre ?? '-' }}</p>
            <p><strong>Mensagem:</strong> {{ $recado->mensagem }}</p>
            <p><strong>Ficheiro:</strong>
            @if ($recado->ficheiro)
                <div class="d-flex gap-2">
                    {{-- Botão Ver --}}
                    <a href="{{ asset('storage/recados/' . $recado->ficheiro) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-eye"></i> Ver Ficheiro
                    </a>

                    {{-- Botão Download --}}
                    <a href="{{ asset('storage/recados/' . $recado->ficheiro) }}" download class="btn btn-success btn-sm">
                        <i class="bi bi-download"></i> Download
                    </a>
                </div>
            @else
                <span class="text-muted">Sem ficheiro</span>
            @endif
            </p>
        </div>
    </div>

    {{-- Comentários abaixo das 3 colunas --}}
    <div class="mt-5 mx-auto" style="max-width: 700px;">
        <h5 class="mb-3"><strong>Comentários</strong></h5>
        
        {{-- Área de mensagens tipo chat --}}
        <div class="bg-white p-3 rounded shadow-sm mb-4" style="max-height: 350px; overflow-y: auto; border: 1px solid #dee2e6;">
            @forelse(explode("\n", $recado->observacoes) as $linha)
                @if(trim($linha) !== '')
                    <div class="d-flex mb-2">
                        <div class="bg-light border rounded-pill px-3 py-2 text-dark small" style="max-width: 80%;">
                            {{ $linha }}
                        </div>
                    </div>
                @endif
            @empty
                <p class="text-muted">Sem comentários ainda.</p>
            @endforelse
        </div>

        {{-- Campo para novo comentário --}}
        <form action="{{ route('recados.observacoes.update', $recado) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="input-group">
                <input type="text" name="comentario" class="form-control rounded-start-pill" placeholder="Escreve um comentário..." required>
                <button class="btn btn-primary rounded-end-pill" type="submit">Enviar</button>
            </div>
        </form>
    </div>

</div>
@endsection
