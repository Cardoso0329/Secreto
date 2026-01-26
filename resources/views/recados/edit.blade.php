@extends('layouts.app')

@section('content')
<div class="container py-5" style="max-width: 1000px;">
    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                <h2 class="fw-bold mb-0">✏️ Editar Recado #{{ $recado->id }}</h2>
                <a href="{{ route('recados.index') }}" class="btn btn-light border">
                    ← Voltar
                </a>
            </div>

            {{-- Alerts --}}
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Há erros no formulário:</strong>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('recados.update', $recado->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                {{-- Nav tabs --}}
                <ul class="nav nav-tabs mb-4" id="recadoTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="geral-tab" data-bs-toggle="tab" data-bs-target="#geral" type="button" role="tab">
                            Informações Gerais
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="relacoes-tab" data-bs-toggle="tab" data-bs-target="#relacoes" type="button" role="tab">
                            Relações & Datas
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="destinatarios-tab" data-bs-toggle="tab" data-bs-target="#destinatarios" type="button" role="tab">
                            Destinatários
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="ficheiro-tab" data-bs-toggle="tab" data-bs-target="#ficheiro" type="button" role="tab">
                            Ficheiro
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="observacoes-tab" data-bs-toggle="tab" data-bs-target="#observacoes" type="button" role="tab">
                            Observações
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    {{-- Aba Geral --}}
                    <div class="tab-pane fade show active" id="geral" role="tabpanel" aria-labelledby="geral-tab">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nome</label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $recado->name) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Contacto Cliente</label>
                                <input type="text" name="contact_client" class="form-control" value="{{ old('contact_client', $recado->contact_client) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Matrícula</label>
                                <input type="text" name="plate" class="form-control" value="{{ old('plate', $recado->plate) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Mensagem</label>
                                <textarea name="mensagem" class="form-control" rows="3">{{ old('mensagem', $recado->mensagem) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Aba Relações & Datas --}}
                    <div class="tab-pane fade" id="relacoes" role="tabpanel" aria-labelledby="relacoes-tab">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <select name="estado_id" class="form-select">
                                    <option value="">-- Nenhum --</option>
                                    @foreach($estados as $estado)
                                        <option value="{{ $estado->id }}" {{ old('estado_id', $recado->estado_id) == $estado->id ? 'selected' : '' }}>
                                            {{ $estado->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tipo de Formulário</label>
                                <select name="tipo_formulario_id" class="form-select">
                                    <option value="">-- Nenhum --</option>
                                    @foreach($tiposFormulario as $tipo)
                                        <option value="{{ $tipo->id }}" {{ old('tipo_formulario_id', $recado->tipo_formulario_id) == $tipo->id ? 'selected' : '' }}>
                                            {{ $tipo->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">SLA</label>
                                <select name="sla_id" class="form-select">
                                    <option value="">-- Nenhum --</option>
                                    @foreach($slas as $sla)
                                        <option value="{{ $sla->id }}" {{ old('sla_id', $recado->sla_id) == $sla->id ? 'selected' : '' }}>
                                            {{ $sla->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tipo</label>
                                <select name="tipo_id" class="form-select">
                                    <option value="">-- Nenhum --</option>
                                    @foreach($tipos as $tipo)
                                        <option value="{{ $tipo->id }}" {{ old('tipo_id', $recado->tipo_id) == $tipo->id ? 'selected' : '' }}>
                                            {{ $tipo->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Origem</label>
                                <select name="origem_id" class="form-select">
                                    <option value="">-- Nenhum --</option>
                                    @foreach($origens as $origem)
                                        <option value="{{ $origem->id }}" {{ old('origem_id', $recado->origem_id) == $origem->id ? 'selected' : '' }}>
                                            {{ $origem->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Setor</label>
                                <select name="setor_id" class="form-select">
                                    <option value="">-- Nenhum --</option>
                                    @foreach($setores as $setor)
                                        <option value="{{ $setor->id }}" {{ old('setor_id', $recado->setor_id) == $setor->id ? 'selected' : '' }}>
                                            {{ $setor->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Departamento</label>
                                <select name="departamento_id" class="form-select">
                                    <option value="">-- Nenhum --</option>
                                    @foreach($departamentos as $departamento)
                                        <option value="{{ $departamento->id }}" {{ old('departamento_id', $recado->departamento_id) == $departamento->id ? 'selected' : '' }}>
                                            {{ $departamento->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Chefia</label>
                                <select name="chefia_id" class="form-select">
                                    <option value="">-- Nenhuma --</option>
                                    @foreach($chefias as $chefia)
                                        <option value="{{ $chefia->id }}" {{ old('chefia_id', $recado->chefia_id) == $chefia->id ? 'selected' : '' }}>
                                            {{ $chefia->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Só faz sentido no Call Center.</small>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Aviso</label>
                                <select name="aviso_id" class="form-select">
                                    <option value="">-- Nenhum --</option>
                                    @foreach($avisos as $aviso)
                                        <option value="{{ $aviso->id }}" {{ old('aviso_id', $recado->aviso_id) == $aviso->id ? 'selected' : '' }}>
                                            {{ $aviso->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Campanha</label>
                                <select name="campanha_id" class="form-select">
                                    <option value="">-- Nenhuma --</option>
                                    @foreach($campanhas as $campanha)
                                        <option value="{{ $campanha->id }}" {{ old('campanha_id', $recado->campanha_id) == $campanha->id ? 'selected' : '' }}>
                                            {{ $campanha->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Abertura</label>
                                <input type="datetime-local" name="abertura" class="form-control"
                                       value="{{ old('abertura', $recado->abertura?->format('Y-m-d\TH:i')) }}">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Término</label>
                                <input type="datetime-local" name="termino" class="form-control"
                                       value="{{ old('termino', $recado->termino?->format('Y-m-d\TH:i')) }}">
                            </div>
                        </div>
                    </div>

                    {{-- ✅ Aba Destinatários --}}
                    <div class="tab-pane fade" id="destinatarios" role="tabpanel" aria-labelledby="destinatarios-tab">
                        <div class="row g-3">

                            {{-- ✅ Destinatários Dinâmicos (Users) --}}
                            <div class="col-12">
                                <label class="form-label fw-semibold">Destinatários (Users)</label>

                                <div class="input-group">
                                    <select id="novoDestinatario" class="form-select rounded-start">
                                        <option value="">Selecione um destinatário</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}" data-name="{{ $user->name }}">
                                                {{ $user->name }}
                                            </option>
                                        @endforeach
                                    </select>

                                    <button type="button" id="adicionarDestinatario" class="btn btn-success rounded-end" disabled>
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>

                                <div id="listaDestinatarios" class="mt-3 d-flex flex-wrap gap-2"></div>
                                <div id="destinatariosInputs"></div>

                                {{-- ids existentes para o JS --}}
                                <input type="hidden" id="preselectedUsers" value='@json($recado->destinatariosUsers->pluck("id")->values())'>
                            </div>

                            {{-- Grupos --}}
                            <div class="col-md-6">
                                <label class="form-label">Destinatários (Grupos)</label>
                                <select name="destinatarios_grupos[]" class="form-select" multiple>
                                    @foreach($grupos as $grupo)
                                        <option value="{{ $grupo->id }}" {{ $recado->grupos->contains($grupo->id) ? 'selected' : '' }}>
                                            {{ $grupo->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">Segure Ctrl (Windows) / Cmd (Mac) para selecionar vários.</small>
                            </div>

                            {{-- Emails livres --}}
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Destinatários Livres (Emails)</label>
                                <div id="emails-livres">
                                    @if(count($guestEmails) > 0)
                                        @foreach($guestEmails as $email)
                                            <input type="email" name="destinatarios_livres[]" class="form-control mb-1" value="{{ $email }}">
                                        @endforeach
                                    @else
                                        <input type="email" name="destinatarios_livres[]" class="form-control mb-1" placeholder="email@example.com">
                                    @endif
                                </div>

                                <button type="button" class="btn btn-sm btn-outline-secondary mt-1" id="btnAddEmailLivre">
                                    + Adicionar Email
                                </button>

                                <small class="text-muted d-block mt-1">
                                    Podes deixar vazio. Emails inválidos serão ignorados no backend.
                                </small>
                            </div>

                        </div>
                    </div>

                    {{-- Aba Ficheiro --}}
                    <div class="tab-pane fade" id="ficheiro" role="tabpanel" aria-labelledby="ficheiro-tab">
                        <div class="mb-3">
                            <label class="form-label">Ficheiro</label>
                            <input type="file" name="ficheiro" class="form-control">
                        </div>

                        @if($recado->ficheiro)
                            <div class="small">
                                Ficheiro atual:
                                <a href="{{ asset('storage/recados/'.$recado->ficheiro) }}" target="_blank">
                                    {{ $recado->ficheiro }}
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- Aba Observações --}}
                    <div class="tab-pane fade" id="observacoes" role="tabpanel" aria-labelledby="observacoes-tab">
                        <label class="form-label">Observações</label>
                        <textarea name="observacoes" class="form-control" rows="8">{{ old('observacoes', $recado->observacoes) }}</textarea>
                    </div>
                </div>

                {{-- Botões --}}
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">💾 Atualizar Recado</button>
                    <a href="{{ route('recados.index') }}" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ✅ Scripts --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    // -----------------------------
    // ✅ Users dinâmicos (igual à criação) + carrega existentes
    // -----------------------------
    const select = document.getElementById('novoDestinatario');
    const addBtn = document.getElementById('adicionarDestinatario');
    const badgeContainer = document.getElementById('listaDestinatarios');
    const inputContainer = document.getElementById('destinatariosInputs');

    const preselectedRaw = document.getElementById('preselectedUsers')?.value || '[]';
    const preselected = JSON.parse(preselectedRaw);

    const selected = new Map(); // id -> name

    const toggleBtn = () => {
        const id = String(select.value || '');
        addBtn.disabled = !id || selected.has(id);
    };

    function createBadge(id, name) {
        const badge = document.createElement('span');
        badge.className = 'badge bg-primary d-flex align-items-center gap-2 px-2 py-2 rounded-pill';
        badge.style.fontSize = '0.95rem';
        badge.innerHTML = `
            <span>${name}</span>
            <button type="button" class="btn-close btn-close-white btn-sm" aria-label="Remover"></button>
        `;

        badge.querySelector('button').addEventListener('click', () => {
            selected.delete(String(id));
            badge.remove();
            document.getElementById(`destinatario-input-${id}`)?.remove();
            toggleBtn();
        });

        return badge;
    }

    function addHiddenInput(id) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'destinatarios_users[]';
        input.value = id;
        input.id = `destinatario-input-${id}`;
        inputContainer.appendChild(input);
    }

    function addUserById(id) {
        const opt = select.querySelector(`option[value="${id}"]`);
        const name = opt ? (opt.dataset.name || opt.textContent.trim()) : `User #${id}`;
        if (!id || selected.has(String(id))) return;

        selected.set(String(id), name);
        badgeContainer.appendChild(createBadge(id, name));
        addHiddenInput(id);
        toggleBtn();
    }

    // carregar existentes
    preselected.forEach((id) => addUserById(String(id)));

    // eventos
    select.addEventListener('change', toggleBtn);
    select.addEventListener('input', toggleBtn);

    addBtn.addEventListener('click', () => {
        const id = String(select.value || '');
        if (!id) return;
        addUserById(id);
        select.value = '';
        select.selectedIndex = 0;
        toggleBtn();
    });

    toggleBtn();

    // -----------------------------
    // ✅ Emails livres (adicionar campo)
    // -----------------------------
    document.getElementById('btnAddEmailLivre')?.addEventListener('click', () => {
        const container = document.getElementById('emails-livres');
        const input = document.createElement('input');
        input.type = 'email';
        input.name = 'destinatarios_livres[]';
        input.className = 'form-control mb-1';
        input.placeholder = 'email@example.com';
        container.appendChild(input);
        input.focus();
    });
});
</script>
@endsection
