@extends('layouts.app')

@section('content')
<div class="container mt-4">

    <h1 class="mb-4">Recado #{{ $recado->id }} (Acesso Convidado)</h1>

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

            {{-- Estado editável pelo convidado --}}
            <form action="{{ route('recados.guest.update', $token) }}" method="POST" class="mb-3">
                @csrf
                <div class="mb-2">
                    <label for="estado_id"><strong>Estado:</strong></label>
                    <select name="estado_id" id="estado_id" class="form-select" required>
                        @foreach(\App\Models\Estado::all() as $estado)
                            <option value="{{ $estado->id }}" {{ $recado->estado_id == $estado->id ? 'selected' : '' }}>
                                {{ $estado->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label for="mensagem"><strong>Mensagem:</strong></label>
                    <textarea id="mensagem" name="mensagem" class="form-control" rows="5" required>{{ old('mensagem', $recado->mensagem) }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary">Atualizar Recado</button>
            </form>
        </div>

        {{-- Coluna 3 - Destinatários e arquivo --}}
        <div class="col-md-4">
            <p><strong>Destinatários:</strong>
                @foreach($recado->destinatarios as $user)
                    {{ $user->name }}{{ !$loop->last ? ',' : '' }}
                @endforeach
            </p>

            <p><strong>Destinatário Livre:</strong> {{ $recado->destinatario_livre ?? '-' }}</p>
            
            <p><strong>Ficheiro:</strong>
                @if ($recado->ficheiro)
                    <div class="d-flex gap-2">
                        <a href="{{ asset('storage/recados/' . $recado->ficheiro) }}" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye"></i> Ver Ficheiro
                        </a>
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

    {{-- Comentários --}}
    <div class="mt-5 mx-auto" style="max-width: 700px;">
        <h5 class="mb-3"><strong>Comentários</strong></h5>
        
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
    </div>

</div>
@endsection
