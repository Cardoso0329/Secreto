@extends('layouts.app')

@section('content')
<div class="container py-5" style="max-width: 900px;">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-5">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <h2 class="fw-bold m-0">📞 Criar Novo Recado - Call Center</h2>
                
                {{-- Botão fechar --}}
                <button type="button" class="btn btn-light btn-sm rounded-circle border" title="Cancelar"
                        onclick="window.history.back();">
                    <i class="bi bi-x-lg"></i>
                </button>
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

                {{-- SLA --}}
                @php
                    $slaDefault = $slas->firstWhere('name', 'A resolver - 12h');
                @endphp
                <div class="mb-4">
                    <label class="form-label fw-semibold">SLA *</label>
                    <select name="sla_id" id="sla_id" class="form-select rounded-3" required>
                        <option value="">-- Selecione --</option>
                        @foreach ($slas as $sla)
                            <option value="{{ $sla->id }}" {{ $slaDefault && $sla->id == $slaDefault->id ? 'selected' : '' }}>
                                {{ $sla->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Tipo --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Tipo *</label>
                    <select name="tipo_id" id="tipo_id" class="form-select rounded-3" required>
                        <option value="">-- Selecione --</option>
                        @foreach ($tipos as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Origem --}}
                @php
                    $origemTelefone = $origens->firstWhere('name', 'Telefone');
                @endphp
                <div class="mb-4">
                    <label class="form-label fw-semibold">Origem *</label>
                    <select name="origem_id" id="origem_id" class="form-select rounded-3" required>
                        <option value="">-- Selecione --</option>
                        @foreach ($origens as $origem)
                            <option value="{{ $origem->id }}" {{ $origemTelefone && $origem->id == $origemTelefone->id ? 'selected' : '' }}>
                                {{ $origem->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Departamento --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Departamento *</label>
                    <select name="departamento_id" id="departamento_id" class="form-select rounded-3" required>
                        <option value="">-- Selecione --</option>
                        @foreach ($departamentos as $departamento)
                            <option value="{{ $departamento->id }}">{{ $departamento->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Chefias (Setor) --}}
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

                {{-- Destinatários --}}
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
                    <label class="form-label fw-semibold">Grupos Destinatários</label>
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
                        <input type="email" id="novoDestinatarioLivre" class="form-control rounded-start" placeholder="Adicionar destinatário livre (email)">
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

                {{-- Estado --}}
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

{{-- ✅ Scripts JS --}}
<script>
document.addEventListener('DOMContentLoaded', () => {
    const novoDestinatario = document.getElementById('novoDestinatario');
    const btnAddDest = document.getElementById('adicionarDestinatario');
    const listaDest = document.getElementById('listaDestinatarios');
    const hiddenInputs = document.getElementById('destinatariosInputs');

    const novoLivre = document.getElementById('novoDestinatarioLivre');
    const btnAddLivre = document.getElementById('adicionarDestinatarioLivre');
    const listaLivre = document.getElementById('listaDestinatariosLivres');
    const hiddenLivreInputs = document.getElementById('destinatariosLivresInputs');

    // Ativa/desativa botões
    novoDestinatario.addEventListener('change', () => {
        btnAddDest.disabled = !novoDestinatario.value;
    });
    novoLivre.addEventListener('input', () => {
        btnAddLivre.disabled = !novoLivre.value.trim();
    });

    // Adicionar destinatário
    btnAddDest.addEventListener('click', () => {
        const id = novoDestinatario.value;
        const nome = novoDestinatario.options[novoDestinatario.selectedIndex].dataset.name;
        if (!id) return;

        const tag = document.createElement('span');
        tag.className = 'badge bg-primary d-flex align-items-center gap-1 p-2';
        tag.innerHTML = `${nome} <i class="bi bi-x" style="cursor:pointer"></i>`;

        // Criar input hidden
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'destinatarios_users[]';
        input.value = id;
        input.id = `destinatario-${id}`;
        hiddenInputs.appendChild(input);

        // Remover badge e input
        tag.querySelector('i').onclick = () => {
            tag.remove();
            document.getElementById(`destinatario-${id}`)?.remove();
        };

        listaDest.appendChild(tag);
        novoDestinatario.value = '';
        btnAddDest.disabled = true;
    });

    // Adicionar destinatário livre
    btnAddLivre.addEventListener('click', () => {
        const email = novoLivre.value.trim();
        if (!email) return;

        const id = `livre-${Date.now()}`;
        const tag = document.createElement('span');
        tag.className = 'badge bg-secondary d-flex align-items-center gap-1 p-2';
        tag.innerHTML = `${email} <i class="bi bi-x" style="cursor:pointer"></i>`;

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'destinatarios_livres[]';
        input.value = email;
        input.id = id;
        hiddenLivreInputs.appendChild(input);

        tag.querySelector('i').onclick = () => {
            tag.remove();
            document.getElementById(id)?.remove();
        };

        listaLivre.appendChild(tag);
        novoLivre.value = '';
        btnAddLivre.disabled = true;
    });

    // Impede envio sem destinatários
    document.querySelector('form').addEventListener('submit', (e) => {
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
@endsection
