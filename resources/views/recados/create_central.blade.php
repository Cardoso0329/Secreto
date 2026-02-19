@extends('layouts.app')

@section('content')
<div class="container py-5" style="max-width: 900px;">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-5">
<<<<<<< HEAD
            <div class="d-flex justify-content-between align-items-center mb-5">
                <h2 class="fw-bold m-0">📨 Criar Novo Recado</h2>
                <a href="{{ route('recados.index') }}" class="btn btn-light btn-sm rounded-circle border" title="Voltar">
                    <i class="bi bi-x-lg"></i>
                </a>
            </div>
=======

<div class="d-flex justify-content-between align-items-center mb-5">
    <h2 class="fw-bold m-0">📨 Criar Novo Recado - Central</h2>
    <a href="{{ route('recados.index') }}" class="btn btn-light btn-sm rounded-circle border" title="Voltar">
        <i class="bi bi-x-lg"></i>
    </a>
</div>
>>>>>>> main

<form action="{{ route('recados.store') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="tipo_formulario_id" value="{{ $tipoFormularioId }}">

    {{-- Nome do Cliente --}}
    <div class="form-floating mb-4">
        <input type="text" name="name" id="name" class="form-control rounded-3" required>
        <label for="name">Nome do Cliente *</label>
    </div>

    {{-- Contacto do Cliente --}}
    <div class="form-floating mb-4">
        <input type="text" name="contact_client" id="contact_client" class="form-control rounded-3" required>
        <label for="contact_client">Contacto do Cliente *</label>
    </div>

    {{-- Matrícula --}}
    <div class="form-floating mb-4">
        <input type="text" name="plate" id="plate" class="form-control rounded-3">
        <label for="plate">Matrícula</label>
    </div>

    {{-- Email do Operador --}}
    <div class="form-floating mb-4">
        <input type="email" name="operator_email" id="operator_email" class="form-control rounded-3 bg-light"
               value="{{ Auth::user()->email }}" readonly>
        <label for="operator_email">Email do Operador *</label>
    </div>

<<<<<<< HEAD
                {{-- SLA (predefinido como "A resolver - 12h") --}}
                @php
                    $slaDefault = $slas->firstWhere('name', 'A resolver - 12h');
                @endphp
                <div class="mb-4">
                    <label class="form-label fw-semibold">SLA *</label>
                    <select name="sla_id" id="sla_id" class="form-select rounded-3" required>
                        <option value="">-- Selecione --</option>
                        @foreach ($slas as $item)
                            <option value="{{ $item->id }}" {{ $slaDefault && $item->id == $slaDefault->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tipo --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Tipo *</label>
                    <select name="tipo_id" id="tipo_id" class="form-select rounded-3" required>
                        <option value="">-- Selecione --</option>
                        @foreach ($tipos as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Origem (predefinido como "Telefone" e visível) --}}
                @php
                    $origemTelefone = $origens->firstWhere('name', 'Telefone');
                @endphp
                <div class="mb-4">
                    <label class="form-label fw-semibold">Origem *</label>
                    <select name="origem_id" id="origem_id" class="form-select rounded-3" required>
                        <option value="">-- Selecione --</option>
                        @foreach ($origens as $item)
                            <option value="{{ $item->id }}" {{ $origemTelefone && $item->id == $origemTelefone->id ? 'selected' : '' }}>
                                {{ $item->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Setor --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Setor *</label>
                    <select name="setor_id" id="setor_id" class="form-select rounded-3" required>
                        <option value="">-- Selecione --</option>
                        @foreach ($setores as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Departamento --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Departamento *</label>
                    <select name="departamento_id" id="departamento_id" class="form-select rounded-3" required>
                        <option value="">-- Selecione --</option>
                        @foreach ($departamentos as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Destinatários Dinâmicos --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Destinatários</label>
                    <div class="input-group">
                        <select id="novoDestinatario" class="form-select rounded-start">
                            <option value="">Selecione um destinatário</option>
                            @foreach (\App\Models\User::all() as $user)
                                <option value="{{ $user->id }}" data-name="{{ $user->name }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <button type="button" id="adicionarDestinatario" class="btn btn-success rounded-end" disabled>
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div id="listaDestinatarios" class="mt-3 d-flex flex-wrap gap-2"></div>
                    <div id="destinatariosInputs"></div>
                </div>

                {{-- Grupos --}}
                <div class="mb-4">
                    <label for="destinatarios_grupos" class="form-label fw-semibold">Grupos Destinatários</label>
                    <select name="destinatarios_grupos[]" id="destinatarios_grupos" class="form-select rounded-3" multiple size="5">
                        @foreach (\App\Models\Grupo::all() as $grupo)
                            <option value="{{ $grupo->id }}">{{ $grupo->name }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Todos os membros dos grupos selecionados serão notificados.</div>
                </div>

                {{-- Destinatários Livres --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Destinatários Livres</label>
                    <div class="input-group">
                        <input type="text" id="novoDestinatarioLivre" class="form-control rounded-start" placeholder="Adicionar destinatário livre">
                        <button type="button" id="adicionarDestinatarioLivre" class="btn btn-success rounded-end" disabled>
                            <i class="bi bi-plus-lg"></i>
                        </button>
                    </div>
                    <div id="listaDestinatariosLivres" class="mt-3 d-flex flex-wrap gap-2"></div>
                    <div id="destinatariosLivresInputs"></div>
                </div>

                {{-- Mensagem --}}
                <div class="mb-4">
                    <label for="mensagem" class="form-label fw-semibold">Mensagem *</label>
                    <textarea name="mensagem" id="mensagem" class="form-control rounded-3" rows="4" required></textarea>
                </div>

                {{-- Ficheiro --}}
                <div class="mb-4">
                    <label for="ficheiro" class="form-label fw-semibold">Ficheiro</label>
                    <input type="file" name="ficheiro" id="ficheiro" class="form-control rounded-3">
                </div>

                {{-- Aviso --}}
                <div class="mb-4">
                    <label for="aviso_id" class="form-label fw-semibold">Aviso</label>
                    <select name="aviso_id" id="aviso_id" class="form-select rounded-3">
                        <option value="">-- Selecione --</option>
                        @foreach ($avisos as $aviso)
                            <option value="{{ $aviso->id }}">{{ $aviso->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Estado fixo em Pendente --}}
                @php
                    $estadoPendente = $estados->firstWhere('name', 'Pendente');
                @endphp
                <input type="hidden" name="estado_id" value="{{ $estadoPendente?->id }}">
=======
    {{-- SLA --}}
    <div class="mb-4">
        <label class="form-label fw-semibold">SLA *</label>
        <select name="sla_id" id="sla_id" class="form-select rounded-3" required>
            @foreach ($slas as $item)
                <option value="{{ $item->id }}"
                    {{ $item->name === 'A resolver - 12h' ? 'selected' : '' }}>
                    {{ $item->name }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Tipo --}}
    <div class="mb-4">
        <label class="form-label fw-semibold">Tipo *</label>
        <select name="tipo_id" id="tipo_id" class="form-select rounded-3" required>
            <option value="">-- Selecione --</option>
            @foreach ($tipos as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Campanha (Só na CENTRAL) --}}
<div class="mb-4">
    <label class="form-label">Campanha</label>
<select name="campanha_id" class="form-select">
  <option value="">—</option>
  @foreach($campanhas as $c)
    <option value="{{ $c->id }}" @selected(old('campanha_id', $recado->campanha_id ?? null) == $c->id)>
      {{ $c->name }}
    </option>
  @endforeach
</select>

</div>

    {{-- Origem --}}
    <div class="mb-4">
        <label class="form-label fw-semibold">Origem *</label>
        <select class="form-select rounded-3" disabled>
            @foreach ($origens as $item)
                <option value="{{ $item->id }}" {{ $item->name === 'Telefone' ? 'selected' : '' }}>
                    {{ $item->name }}
                </option>
            @endforeach
        </select>

        <input type="hidden" name="origem_id"
               value="{{ $origens->firstWhere('name','Telefone')->id }}">
    </div>

    


    {{-- Departamento --}}
    <div class="mb-4">
        <label class="form-label fw-semibold">Departamento </label>
        <select name="departamento_id" id="departamento_id" class="form-select rounded-3">
            <option value="">-- Selecione --</option>
            @foreach ($departamentos as $item)
                <option value="{{ $item->id }}">{{ $item->name }}</option>
            @endforeach
        </select>
    </div>
     {{-- ✅ NOVO: Chefia (Call Center) --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Chefia </label>
                    <select name="chefia_id" id="chefia_id" class="form-select rounded-3" >
                        <option value="">-- Selecione --</option>
                        @foreach ($chefias as $chefia)
                            <option value="{{ $chefia->id }}">{{ $chefia->name }}</option>
                        @endforeach
                    </select>
                </div>

    {{-- GRUPOS DESTINATÁRIOS OCULTO --}}

>>>>>>> main

    {{-- Destinatários Dinâmicos --}}
    <div class="mb-4">
        <label class="form-label fw-semibold">Destinatários</label>
        <div class="input-group">
            <select id="novoDestinatario" class="form-select rounded-start">
                <option value="">Selecione um destinatário</option>
                @foreach (\App\Models\User::orderBy('name')->get() as $user)
                    <option value="{{ $user->id }}" data-name="{{ $user->name }}">{{ $user->name }}</option>
                @endforeach
            </select>
            <button type="button" id="adicionarDestinatario" class="btn btn-success rounded-end" disabled>
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>

        <div id="listaDestinatarios" class="mt-3 d-flex flex-wrap gap-2"></div>
        <div id="destinatariosInputs"></div>
    </div>

    <div class="mb-4">

    {{-- Grupo Destinatário (pré-seleciona Telefonistas, mas editável) --}}
<div class="mb-4">
    <label class="form-label fw-semibold">Grupo Destinatário</label>
    <select name="destinatarios_grupos[]" id="destinatarios_grupos" class="form-select rounded-3" multiple>
        @foreach (\App\Models\Grupo::all() as $grupo)
            <option value="{{ $grupo->id }}" 
                {{ $grupo->name === 'Telefonistas' ? 'selected' : '' }}>
                {{ $grupo->name }}
            </option>
        @endforeach
    </select>
    <small class="form-text text-muted">Segure Ctrl (Windows).</small>
</div>


    {{-- Hidden real para enviar no formulário --}}
    <input type="hidden" name="destinatarios_grupos[]"
           value="{{ \App\Models\Grupo::where('name','Telefonistas')->first()->id }}">
</div>


    {{-- Destinatários Livres --}}
    <div class="mb-4">
        <label class="form-label fw-semibold">Destinatários Livres</label>
        <div class="input-group">
            <input type="text" id="novoDestinatarioLivre" class="form-control rounded-start"
                   placeholder="Adicionar destinatário livre">
            <button type="button" id="adicionarDestinatarioLivre" class="btn btn-success rounded-end" disabled>
                <i class="bi bi-plus-lg"></i>
            </button>
        </div>

        <div id="listaDestinatariosLivres" class="mt-3 d-flex flex-wrap gap-2"></div>
        <div id="destinatariosLivresInputs"></div>
    </div>

    {{-- Mensagem --}}
    <div class="mb-4">
        <label for="mensagem" class="form-label fw-semibold">Mensagem *</label>
        <textarea name="mensagem" id="mensagem" class="form-control rounded-3" rows="4" required></textarea>
    </div>

    {{-- Ficheiro --}}
    <div class="mb-4">
        <label for="ficheiro" class="form-label fw-semibold">Ficheiro</label>
        <input type="file" name="ficheiro" id="ficheiro" class="form-control rounded-3">
    </div>

    {{-- Aviso --}}
    <div class="mb-4">
        <label for="aviso_id" class="form-label fw-semibold">Aviso</label>
        <select name="aviso_id" id="aviso_id" class="form-select rounded-3">
            <option value="">-- Selecione --</option>
            @foreach ($avisos as $aviso)
                <option value="{{ $aviso->id }}">{{ $aviso->name }}</option>
            @endforeach
        </select>
    </div>

    @php
        $estadoPendente = $estados->firstWhere('name', 'Pendente');
    @endphp
    <input type="hidden" name="estado_id" value="{{ $estadoPendente?->id }}">

    {{-- Abertura --}}
    <div class="form-floating mb-4">
        <input type="datetime-local" name="abertura" id="abertura" class="form-control rounded-3"
               value="{{ now()->format('Y-m-d\TH:i') }}">
        <label for="abertura">Data de Abertura</label>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg rounded-pill">
            <i class="bi bi-send me-1"></i> Enviar Recado
        </button>
    </div>

</form>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {

    // ============================
    // Destinatários Users
    // ============================
    const destinatarios = new Map();
    const select = document.getElementById('novoDestinatario');
    const addBtn = document.getElementById('adicionarDestinatario');
    const badgeContainer = document.getElementById('listaDestinatarios');
    const inputContainer = document.getElementById('destinatariosInputs');

    const atualizarBotao = () => addBtn.disabled = !select.value;
    select.addEventListener('change', atualizarBotao);

    addBtn.addEventListener('click', () => {
        const option = select.options[select.selectedIndex];
        const id = option.value;
        const name = option.dataset.name;
<<<<<<< HEAD
=======

>>>>>>> main
        if (!id || destinatarios.has(id)) return;
        destinatarios.set(id, name);

        const badge = document.createElement('span');
        badge.className = 'badge bg-primary d-flex align-items-center gap-2 px-2 py-1 rounded-pill';
<<<<<<< HEAD
        badge.innerHTML = `<span>${name}</span><button type="button" class="btn-close btn-close-white btn-sm"></button>`;
=======
        badge.innerHTML = `<span>${name}</span>
                           <button type="button" class="btn-close btn-close-white btn-sm"></button>`;

>>>>>>> main
        badge.querySelector('button').addEventListener('click', () => {
            destinatarios.delete(id);
            badge.remove();
            document.getElementById(`destinatario-input-${id}`)?.remove();
        });
        badgeContainer.appendChild(badge);

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'destinatarios_users[]';
        input.value = id;
        input.id = `destinatario-input-${id}`;
        inputContainer.appendChild(input);

        select.value = '';
        atualizarBotao();
    });


    // ============================
    // Destinatários Livres
<<<<<<< HEAD
    // =======================
    const livres = new Map();
=======
    // ============================
    const destinatariosLivres = new Map();
>>>>>>> main
    const inputLivre = document.getElementById('novoDestinatarioLivre');
    const addLivre = document.getElementById('adicionarDestinatarioLivre');
    const containerLivre = document.getElementById('listaDestinatariosLivres');
    const inputsLivre = document.getElementById('destinatariosLivresInputs');

<<<<<<< HEAD
    const updateLivre = () => addLivre.disabled = !inputLivre.value.trim();
    inputLivre.addEventListener('input', updateLivre);
=======
    const atualizarBotaoLivre = () =>
        addBtnLivre.disabled = inputLivre.value.trim().length === 0;
>>>>>>> main

    addLivre.addEventListener('click', () => {
        const val = inputLivre.value.trim();
        if (!val || livres.has(val)) return;
        livres.set(val, val);

        const badge = document.createElement('span');
        badge.className = 'badge bg-secondary d-flex align-items-center gap-2 px-2 py-1 rounded-pill';
<<<<<<< HEAD
        badge.innerHTML = `<span>${val}</span><button type="button" class="btn-close btn-close-white btn-sm"></button>`;
=======
        badge.innerHTML = `<span>${valor}</span>
                           <button type="button" class="btn-close btn-close-white btn-sm"></button>`;

>>>>>>> main
        badge.querySelector('button').addEventListener('click', () => {
            livres.delete(val);
            badge.remove();
            document.getElementById(`destinatario-livre-input-${val}`)?.remove();
        });
        containerLivre.appendChild(badge);

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'destinatarios_livres[]';
        input.value = val;
        input.id = `destinatario-livre-input-${val}`;
        inputsLivre.appendChild(input);

        inputLivre.value = '';
        updateLivre();
    });

<<<<<<< HEAD
    // =======================
    // Validação no Submit
    // =======================
    document.querySelector('form').addEventListener('submit', (e) => {
        const temUsers = document.querySelectorAll('input[name="destinatarios_users[]"]').length > 0;
        const temGrupos = document.querySelector('#destinatarios_grupos')?.selectedOptions.length > 0;
        const temLivres = document.querySelectorAll('input[name="destinatarios_livres[]"]').length > 0;
        if (!temUsers && !temGrupos && !temLivres) {
=======

    // ============================
    // Validação Antes do Submit
    // ============================
    document.querySelector('form').addEventListener('submit', function (e) {

        const temUsers =
            document.querySelectorAll('input[name="destinatarios_users[]"]').length > 0;

        const temLivres =
            document.querySelectorAll('input[name="destinatarios_livres[]"]').length > 0;

       const temGrupos = document.querySelectorAll('input[name="destinatarios_grupos[]"]').length > 0;

        if (!temUsers && !temLivres && !temGrupos) {
>>>>>>> main
            e.preventDefault();
            alert('Por favor, selecione ao menos um destinatário (usuário, grupo ou livre).');
        }
    });

});
</script>
@endpush
