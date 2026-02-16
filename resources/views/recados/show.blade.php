@extends('layouts.app')

@section('content')
<div class="container py-4">

    {{-- Cabeçalho --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h2 class="fw-bold mb-0">📌 Recado #{{ $recado->id }}</h2>
            <div class="text-muted small">Detalhe do recado</div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            {{-- ✅ Voltar ao Painel --}}
            <a href="{{ route('recados.index') }}"
               class="btn btn-outline-secondary d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Voltar ao Painel
            </a>

            {{-- Concluir --}}
            @if($recado->estado?->name && $recado->estado->name !== 'Tratado')
                <form action="{{ route('recados.concluir', $recado) }}" method="POST" class="m-0">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-success d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle"></i> Concluir
                    </button>
                </form>
            @endif
        </div>
    </div>

    @php
        // IDs dos avisos já enviados (pivot recado_aviso)
        $avisosEnviadosIds = $recado->relationLoaded('avisosEnviados')
            ? $recado->avisosEnviados->pluck('id')->toArray()
            : ($recado->avisosEnviados()->pluck('avisos.id')->toArray());

        $podeEnviarAvisos =
            optional(auth()->user()->cargo)->name === 'admin'
            || auth()->user()->grupos->contains('name', 'Telefonistas');

        // ✅ Só o departamento "Colisão" pode adicionar/encaminhar destinatários
        $podeEncaminhar = auth()->user()
            ->departamentos
            ->contains(fn($d) => mb_strtolower(trim($d->name)) === 'colisão');
    @endphp

    <div class="row g-4">

        {{-- Coluna 1 - Dados principais --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">📑 Dados Principais</h5>

                    @if(trim((string)$recado->name) !== '')
                        <p><strong>Nome:</strong> {{ $recado->name }}</p>
                    @endif

                    @if(trim((string)$recado->contact_client) !== '')
                        <p><strong>Contacto:</strong> {{ $recado->contact_client }}</p>
                    @endif

                    @if(trim((string)$recado->plate) !== '')
                        <p><strong>Matrícula:</strong> {{ $recado->plate }}</p>
                    @endif

                    @if(trim((string)$recado->operator_email) !== '')
                        <p><strong>Email Operador:</strong> {{ $recado->operator_email }}</p>
                    @endif

                    @if($recado->abertura)
                        <p><strong>Abertura:</strong> {{ $recado->abertura->format('d/m/Y H:i') }}</p>
                    @endif

                    @if($recado->termino)
                        <p><strong>Término:</strong> {{ $recado->termino->format('d/m/Y H:i') }}</p>
                    @endif

                    @if(trim((string)$recado->wip) !== '')
                        <p><strong>WIP:</strong> {{ $recado->wip }}</p>
                    @endif

                    @if(
                        trim((string)$recado->name) === '' &&
                        trim((string)$recado->contact_client) === '' &&
                        trim((string)$recado->plate) === '' &&
                        trim((string)$recado->operator_email) === '' &&
                        !$recado->abertura &&
                        !$recado->termino &&
                        trim((string)$recado->wip) === ''
                    )
                        <div class="text-muted small">Sem dados principais para mostrar.</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Coluna 2 - Relações + Avisos + Estado --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">🔗 Informações Relacionadas</h5>

                    @if($recado->sla?->name)
                        <p><strong>SLA:</strong> {{ $recado->sla->name }}</p>
                    @endif

                    @if($recado->tipo?->name)
                        <p><strong>Tipo:</strong> {{ $recado->tipo->name }}</p>
                    @endif
                    
                    @if($recado->campanha?->name)
                        <p class="mb-2"><strong>Campanha:</strong> {{ $recado->campanha->name }}</p>
                    @endif

                    @if($recado->origem?->name)
                        <p><strong>Origem:</strong> {{ $recado->origem->name }}</p>
                    @endif

                    @if($recado->chefia?->name)
                        <p><strong>Chefia:</strong> {{ $recado->chefia->name }}</p>
                    @endif

                    @if($recado->departamento?->name)
                        <p><strong>Departamento:</strong> {{ $recado->departamento->name }}</p>
                    @endif

                    {{-- Avisos --}}
                    @if(isset($avisos) && $avisos->count())
                        <div class="mt-4">
                            <h5 class="fw-semibold mb-2">📣 Avisos</h5>

                            <div class="d-flex flex-wrap gap-2">
                                @foreach($avisos as $aviso)
                                    @php $enviado = in_array($aviso->id, $avisosEnviadosIds); @endphp

                                    @if($podeEnviarAvisos)
                                        @if($enviado)
                                            <button type="button" class="btn btn-secondary btn-sm" disabled title="Aviso já enviado">
                                                {{ $aviso->name }} ✅
                                            </button>
                                        @else
                                            <form action="{{ route('recados.enviarAviso', $recado) }}"
                                                  method="POST"
                                                  class="m-0 aviso-form"
                                                  id="avisoForm{{ $aviso->id }}">
                                                @csrf
                                                <input type="hidden" name="aviso_id" value="{{ $aviso->id }}">
                                                <button type="button"
                                                        class="btn btn-outline-primary btn-sm btn-aviso-confirm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#confirmAvisoModal"
                                                        data-form-id="avisoForm{{ $aviso->id }}"
                                                        data-aviso-nome="{{ $aviso->name }}">
                                                    {{ $aviso->name }}
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        @if($enviado)
                                            <span class="badge bg-secondary px-3 py-2">{{ $aviso->name }}</span>
                                        @endif
                                    @endif
                                @endforeach
                            </div>

                            @if($podeEnviarAvisos)
                                <div class="small text-muted mt-2">
                                    Ao clicar num aviso vai aparecer uma confirmação antes de enviar.
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Estado --}}
                    @if(isset($estados) && $estados->count())
                        <hr>
                        <form action="{{ route('recados.estado.update', $recado) }}" method="POST" class="mt-2">
                            @csrf
                            @method('PUT')

                            <label for="estado_id" class="form-label"><strong>Estado:</strong></label>
                            <select name="estado_id" id="estado_id" class="form-select" onchange="this.form.submit()">
                                @foreach($estados as $estado)
                                    <option value="{{ $estado->id }}" @selected($recado->estado_id == $estado->id)>
                                        {{ $estado->name }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        {{-- Coluna 3 - Destinatários e Mensagem --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">👥 Destinatários & Mensagem</h5>

                    {{-- ✅ Destinatários (ver / remover / adicionar / encaminhar) --}}
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <strong>Destinatários:</strong>

                            @if($podeEncaminhar)
                                <button type="button"
                                        class="btn btn-sm btn-outline-primary"
                                        onclick="toggleDestinatarios(true)">
                                    <i class="bi bi-person-plus"></i> Adicionar / Encaminhar
                                </button>
                            @endif
                        </div>

                        @if($recado->destinatarios?->count())
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                @foreach($recado->destinatarios as $u)
                                    <span class="badge bg-secondary d-flex align-items-center gap-2">
                                        {{ $u->name }}

                                        @if($podeEncaminhar)
                                            <form action="{{ route('recados.destinatarios.remove', [$recado, $u]) }}"
                                                  method="POST" class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm p-0 text-white"
                                                        style="line-height:1"
                                                        title="Remover"
                                                        onclick="return confirm('Remover {{ $u->name }} deste recado?')">
                                                    <i class="bi bi-x-lg"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted mt-2">Nenhum destinatário.</div>
                        @endif

                        {{-- Painel adicionar/encaminhar --}}
                        @if($podeEncaminhar)
                            <div id="destinatariosEdit" class="d-none mt-3 p-3 border rounded bg-light">
                                <form action="{{ route('recados.destinatarios.update', $recado) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    <label class="form-label small fw-semibold">Adicionar destinatários</label>

                                    <select name="user_ids[]" class="form-select" multiple size="6">
                                        @foreach($users as $u)
                                            <option value="{{ $u->id }}"
                                                @if($recado->destinatarios->contains('id', $u->id)) disabled @endif>
                                                {{ $u->name }} ({{ $u->email }})
                                            </option>
                                        @endforeach
                                    </select>

                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="encaminhar" value="1" id="chkEncaminhar" checked>
                                        <label class="form-check-label" for="chkEncaminhar">
                                            Encaminhar por email para os novos destinatários
                                        </label>
                                    </div>

                                    @error('user_ids') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                    @error('user_ids.*') <div class="text-danger small mt-1">{{ $message }}</div> @enderror

                                    <div class="d-flex gap-2 mt-3">
                                        <button class="btn btn-primary btn-sm" type="submit">
                                            <i class="bi bi-check2-circle"></i> Guardar
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleDestinatarios(false)">
                                            Cancelar
                                        </button>
                                    </div>

                                    <div class="small text-muted mt-2">
                                        Os utilizadores já existentes ficam desativados na lista.
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>

                    {{-- Outros campos --}}
                    @if($recado->grupos?->count())
                        <p class="mb-2">
                            <strong>Grupos:</strong>
                            @foreach($recado->grupos as $grupo)
                                {{ $grupo->name }}{{ !$loop->last ? ',' : '' }}
                            @endforeach
                        </p>
                    @endif

                    @if($recado->guestTokens?->count())
                        <div class="mb-2">
                            <strong>Destinatários Livres:</strong>
                            <ul class="mb-0 mt-1">
                                @foreach($recado->guestTokens as $token)
                                    <li>{{ $token->email }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif


                    @if(trim((string)$recado->assunto) !== '')
                        <p class="mb-2"><strong>Assunto:</strong> {{ $recado->assunto }}</p>
                    @endif

                    @if(trim((string)$recado->mensagem) !== '')
                        <p class="mb-2"><strong>Mensagem:</strong> {{ $recado->mensagem }}</p>
                    @endif

                    {{-- Ficheiro (visualizar / editar / remover) --}}
                    <div class="mt-3">
                        <p class="mb-2"><strong>Ficheiro:</strong></p>

                        @php
                            $fileUrl = $recado->ficheiro ? asset('storage/recados/' . $recado->ficheiro) : null;
                            $ext = $recado->ficheiro ? strtolower(pathinfo($recado->ficheiro, PATHINFO_EXTENSION)) : null;
                            $imageExtensions = ['jpg','jpeg','png','gif','webp'];
                        @endphp

                        @if($recado->ficheiro)
                            @if(in_array($ext, $imageExtensions))
                                <div class="mb-2">
                                    <img src="{{ $fileUrl }}" class="img-fluid rounded" style="max-height:300px">
                                </div>
                            @elseif($ext === 'pdf')
                                <div class="mb-2">
                                    <iframe src="{{ $fileUrl }}" class="w-100 rounded" style="height:400px"></iframe>
                                </div>
                            @else
                                <p class="text-muted">Pré-visualização não disponível.</p>
                            @endif

                            <div class="d-flex gap-2 flex-wrap mb-2" id="fileActions">
                                <a href="{{ $fileUrl }}" target="_blank" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-eye"></i> Abrir
                                </a>

                                <a href="{{ $fileUrl }}" download class="btn btn-success btn-sm">
                                    <i class="bi bi-download"></i> Download
                                </a>

                                <button type="button" class="btn btn-warning btn-sm" onclick="toggleFileEdit(true)">
                                    <i class="bi bi-pencil"></i> Editar ficheiro
                                </button>
                            </div>
                        @else
                            <span class="text-muted">Sem ficheiro</span>
                            <div class="mt-2" id="fileActions">
                                <button type="button" class="btn btn-primary btn-sm" onclick="toggleFileEdit(true)">
                                    <i class="bi bi-upload"></i> Adicionar ficheiro
                                </button>
                            </div>
                        @endif

                        {{-- Modo edição --}}
                        <div id="fileEdit" class="d-none">
                            <form action="{{ route('recados.ficheiro.update', $recado) }}"
                                  method="POST"
                                  enctype="multipart/form-data"
                                  class="mb-2">
                                @csrf
                                @method('PUT')

                                <div class="d-flex gap-2 align-items-center flex-wrap">
                                    <input type="file"
                                           name="ficheiro"
                                           class="form-control"
                                           accept=".pdf,.jpg,.jpeg,.png,.gif,.webp">

                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="bi bi-upload"></i> Guardar
                                    </button>
                                </div>

                                @error('ficheiro')
                                    <div class="text-danger small mt-1">{{ $message }}</div>
                                @enderror
                            </form>

                            @if($recado->ficheiro)
                                <form action="{{ route('recados.ficheiro.destroy', $recado) }}"
                                      method="POST"
                                      class="mb-2"
                                      onsubmit="return confirm('Remover definitivamente o ficheiro?')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                        <i class="bi bi-trash"></i> Remover ficheiro
                                    </button>
                                </form>
                            @endif

                            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="toggleFileEdit(false)">
                                Cancelar
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div> {{-- /row --}}

    {{-- Comentários (embaixo das colunas) --}}
    <div class="mt-5 mx-auto" style="max-width: 700px;">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5 class="fw-semibold mb-3">💬 Comentários</h5>

                <div class="p-3 rounded bg-light mb-3" style="max-height: 350px; overflow-y: auto;">
                    @php
                        $linhas = preg_split("/\r\n|\n|\r/", (string)($recado->observacoes ?? ''), -1, PREG_SPLIT_NO_EMPTY);
                        $meuNome = auth()->user()->name ?? '';

                        $comentarios = collect($linhas)
                            ->reverse()
                            ->values()
                            ->map(function($linha) {
                                $linha = trim($linha);

                                if (preg_match('/^(\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2})\s+-\s+(.+?):\s*(.+)$/u', $linha, $m)) {
                                    return [
                                        'data' => $m[1],
                                        'autor' => trim($m[2]),
                                        'msg' => trim($m[3]),
                                        'raw' => $linha,
                                    ];
                                }

                                return [
                                    'data' => null,
                                    'autor' => null,
                                    'msg' => $linha,
                                    'raw' => $linha,
                                ];
                            });
                    @endphp

                    @forelse($comentarios as $c)
                        @php
                            $isMine = $c['autor'] && $meuNome && mb_strtolower($c['autor']) === mb_strtolower($meuNome);
                        @endphp

                        <div class="d-flex mb-2 {{ $isMine ? 'justify-content-end' : 'justify-content-start' }}">
                            <div class="px-3 py-2 rounded-3 small {{ $isMine ? 'bg-primary text-white' : 'bg-white border text-dark' }}"
                                 style="max-width: 75%;">
                                @if($c['data'] && $c['autor'])
                                    <div class="small {{ $isMine ? 'text-white-50' : 'text-muted' }}">
                                        {{ $c['data'] }} - {{ $c['autor'] }}
                                    </div>
                                    <div class="comentario-html">{!! $c['msg'] !!}</div>
                                @else
                                    <div style="white-space: pre-wrap;">{{ trim($c['raw']) }}</div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-muted mb-0">Sem comentários ainda.</p>
                    @endforelse
                </div>

                <form action="{{ route('recados.observacoes.update', $recado) }}" method="POST" id="comentarioForm">
                    @csrf
                    @method('PUT')

                    <div class="mb-2">
                        <div id="quillToolbar" class="rounded-top border bg-white">
                            <span class="ql-formats">
                                <button class="ql-bold"></button>
                                <button class="ql-italic"></button>
                                <button class="ql-underline"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-list" value="ordered"></button>
                                <button class="ql-list" value="bullet"></button>
                            </span>
                            <span class="ql-formats">
                                <button class="ql-link"></button>
                                <button class="ql-clean"></button>
                            </span>
                        </div>

                        <div id="quillEditor" class="border border-top-0 rounded-bottom bg-white" style="min-height: 90px;"></div>
                    </div>

                    <input type="hidden" name="comentario" id="comentarioHtml" required>

                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary rounded-pill d-flex align-items-center gap-2" type="submit">
                            <i class="bi bi-send"></i> Enviar
                        </button>
                    </div>

                    <div class="small text-muted mt-2">
                        Atalhos: <strong>Ctrl+B</strong> (negrito), <strong>Ctrl+I</strong> (itálico), listas pelo botão.
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

{{-- Modal confirmação aviso --}}
<div class="modal fade" id="confirmAvisoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar envio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body">
                <p class="mb-0">
                    Tens a certeza que queres enviar o aviso <strong id="confirmAvisoNome">—</strong>?
                </p>
                <div class="small text-muted mt-2">
                    Esta ação envia email aos destinatários deste recado.
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmAvisoBtn">
                    <i class="bi bi-send"></i> Confirmar envio
                </button>
            </div>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // ===== Modal Avisos =====
    let formIdToSubmit = null;

    document.querySelectorAll('.btn-aviso-confirm').forEach(btn => {
        btn.addEventListener('click', function () {
            formIdToSubmit = this.getAttribute('data-form-id');
            const avisoNome = this.getAttribute('data-aviso-nome') || '—';
            const target = document.getElementById('confirmAvisoNome');
            if (target) target.textContent = avisoNome;
        });
    });

    const confirmBtn = document.getElementById('confirmAvisoBtn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (!formIdToSubmit) return;
            const form = document.getElementById(formIdToSubmit);
            if (!form) return;
            this.disabled = true;
            form.submit();
        });
    }

    const modalEl = document.getElementById('confirmAvisoModal');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function () {
            formIdToSubmit = null;
            const btn = document.getElementById('confirmAvisoBtn');
            if (btn) btn.disabled = false;
            const target = document.getElementById('confirmAvisoNome');
            if (target) target.textContent = '—';
        });
    }

    // ===== Toggle edição ficheiro =====
    window.toggleFileEdit = function(show) {
        const edit = document.getElementById('fileEdit');
        const actions = document.getElementById('fileActions');

        if (!edit) return;

        if (show) {
            edit.classList.remove('d-none');
            if (actions) actions.classList.add('d-none');
        } else {
            edit.classList.add('d-none');
            if (actions) actions.classList.remove('d-none');
        }
    };

    // ===== Toggle destinatários =====
    window.toggleDestinatarios = function(show) {
        const box = document.getElementById('destinatariosEdit');
        if (!box) return;
        box.classList.toggle('d-none', !show);
    };

    // ===== Quill Editor =====
    const quill = new Quill('#quillEditor', {
        theme: 'snow',
        modules: { toolbar: '#quillToolbar' }
    });

    const form = document.getElementById('comentarioForm');
    form.addEventListener('submit', function (e) {
        const plain = quill.getText().trim();
        if (!plain) {
            e.preventDefault();
            alert('Escreve um comentário antes de enviar.');
            return;
        }
        document.getElementById('comentarioHtml').value = quill.root.innerHTML.trim();
    });
});
</script>
@endsection
