@extends('layouts.app')

@section('content')
<div class="container py-5" style="max-width: 900px;">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-5">
<div class="d-flex justify-content-between align-items-center mb-5">
    <h2 class="fw-bold m-0">📞 Criar Novo Recado - Call Center</h2>
    <a href="{{ url()->previous() }}" class="btn btn-light btn-sm rounded-circle border" title="Voltar">
        <i class="bi bi-x-lg"></i>
    </a>
</div>

            <form action="{{ route('recados.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="tipo_formulario_id" value="{{ $tipoFormularioId }}">

                {{-- Nome do Cliente --}}
                <div class="form-floating mb-4">
                    <input type="text" name="name" id="name" class="form-control rounded-3" placeholder="Nome do Cliente" required>
                    <label for="name">Nome do Cliente *</label>
                </div>

                {{-- Contacto do Cliente --}}
                <div class="form-floating mb-4">
                    <input type="text" name="contact_client" id="contact_client" class="form-control rounded-3" placeholder="Contacto" required>
                    <label for="contact_client">Contacto do Cliente *</label>
                </div>

                {{-- Matrícula --}}
                <div class="form-floating mb-4">
                    <input type="text" name="plate" id="plate" class="form-control rounded-3" placeholder="Matrícula">
                    <label for="plate">Matrícula</label>
                </div>

                 {{-- Email do Operador --}}
                <div class="form-floating mb-4">
                    <input type="email" name="operator_email" id="operator_email" class="form-control rounded-3 bg-light" value="{{ Auth::user()->email }}" readonly>
                    <label for="operator_email">Email do Operador *</label>
                </div>

                {{-- Campo WIP --}}
                <div class="form-floating mb-4">
                    <input type="text" name="wip" id="wip" class="form-control rounded-3" placeholder="WIP" required>
                    <label for="wip">WIP</label>
                </div>

                {{-- Relações exceto Setor --}}
                @foreach (['sla' => $slas, 'tipo' => $tipos, 'origem' => $origens, 'departamento' => $departamentos] as $field => $items)
                    <div class="mb-4">
                        <label class="form-label fw-semibold">{{ ucfirst($field) }} *</label>
                        <select name="{{ $field }}_id" id="{{ $field }}_id" class="form-select rounded-3" required>
                            <option value="">-- Selecione --</option>
                            @foreach ($items as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                @endforeach

                {{-- Setor filtrado --}}
                @php
                    $setoresPermitidos = [
                        'Novos VLP', 'Novos VCL', 'Novos Smart', 'Usados', 'Novos VCP',
                        'Colisão', 'APV - VLP', 'APV - VCL', 'APV - VCP',
                        'Peças', 'VCL', 'Marketing', 'Informática'
                    ];
                @endphp

                <div class="mb-4">
                    <label class="form-label fw-semibold">Chefias *</label>
                    <select name="setor_id" id="setor_id" class="form-select rounded-3" required>
                        <option value="">-- Selecione --</option>
                        @foreach ($setores as $setor)
                            @if(in_array($setor->name, $setoresPermitidos))
                                <option value="{{ $setor->id }}">{{ $setor->name }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                {{-- Destinatários Dinâmicos --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Destinatários </label>
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

                {{-- Abertura --}}
                <div class="form-floating mb-4">
                    <input type="datetime-local" name="abertura" id="abertura" class="form-control rounded-3"
                        value="{{ now()->format('Y-m-d\TH:i') }}">
                    <label for="abertura">Data de Abertura</label>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg rounded-pill">
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
        // =======================
        // Destinatários Dinâmicos
        // =======================
        const destinatarios = new Map();
        const select = document.getElementById('novoDestinatario');
        const addBtn = document.getElementById('adicionarDestinatario');
        const badgeContainer = document.getElementById('listaDestinatarios');
        const inputContainer = document.getElementById('destinatariosInputs');

        const atualizarBotao = () => {
            addBtn.disabled = !select.value;
        };

        select.addEventListener('change', atualizarBotao);
        select.addEventListener('input', atualizarBotao);

        addBtn.addEventListener('click', () => {
            const selectedOption = select.options[select.selectedIndex];
            const id = selectedOption.value;
            const name = selectedOption.dataset.name;

            if (!id || destinatarios.has(id)) return;

            destinatarios.set(id, name);

            const badge = document.createElement('span');
            badge.className = 'badge bg-primary d-flex align-items-center gap-2 px-2 py-1 rounded-pill';
            badge.innerHTML = `
                <span>${name}</span>
                <button type="button" class="btn-close btn-close-white btn-sm" aria-label="Remover"></button>
            `;

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
            select.selectedIndex = 0;
            atualizarBotao();
        });

        // =======================
        // Destinatários Livres
        // =======================
        const destinatariosLivres = new Map();
        const inputLivre = document.getElementById('novoDestinatarioLivre');
        const addBtnLivre = document.getElementById('adicionarDestinatarioLivre');
        const badgeContainerLivre = document.getElementById('listaDestinatariosLivres');
        const inputContainerLivre = document.getElementById('destinatariosLivresInputs');

        const atualizarBotaoLivre = () => {
            addBtnLivre.disabled = !inputLivre.value.trim();
        };

        inputLivre.addEventListener('input', atualizarBotaoLivre);

        addBtnLivre.addEventListener('click', () => {
            const valor = inputLivre.value.trim();
            if (!valor || destinatariosLivres.has(valor)) return;

            destinatariosLivres.set(valor, valor);

            const badge = document.createElement('span');
            badge.className = 'badge bg-secondary d-flex align-items-center gap-2 px-2 py-1 rounded-pill';
            badge.innerHTML = `
                <span>${valor}</span>
                <button type="button" class="btn-close btn-close-white btn-sm" aria-label="Remover"></button>
            `;

            badge.querySelector('button').addEventListener('click', () => {
                destinatariosLivres.delete(valor);
                badge.remove();
                document.getElementById(`destinatario-livre-input-${valor}`)?.remove();
            });

            badgeContainerLivre.appendChild(badge);

            const inputHidden = document.createElement('input');
            inputHidden.type = 'hidden';
            inputHidden.name = 'destinatarios_livres[]';
            inputHidden.value = valor;
            inputHidden.id = `destinatario-livre-input-${valor}`;
            inputContainerLivre.appendChild(inputHidden);

            inputLivre.value = '';
            atualizarBotaoLivre();
        });

        // =======================
        // Validação no Submit
        // =======================
        document.querySelector('form').addEventListener('submit', function (e) {
            const temUsers = document.querySelectorAll('input[name="destinatarios_users[]"]').length > 0;
            const temGrupos = document.querySelector('#destinatarios_grupos')?.selectedOptions.length > 0;
            const temLivres = document.querySelectorAll('input[name="destinatarios_livres[]"]').length > 0;

            if (!temUsers && !temGrupos && !temLivres) {
                e.preventDefault();
                alert('Por favor, selecione ao menos um destinatário (usuário, grupo ou livre).');
            }
        });
    });
</script>
@endpush
