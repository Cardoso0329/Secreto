@extends('layouts.app')

@section('content')
<div class="container py-5" style="max-width: 900px;">
    <div class="card shadow-lg border-0 rounded-4">
        <div class="card-body p-5">
            <h2 class="text-center fw-bold mb-5">📨 Criar Novo Recado</h2>

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

                {{-- Seleções Dinâmicas (SLA, Tipo, etc.) --}}
                @foreach (['sla' => $slas, 'tipo' => $tipos, 'origem' => $origens, 'setor' => $setores, 'departamento' => $departamentos] as $field => $items)
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

                {{-- Destinatários Dinâmicos --}}
                <div class="mb-4">
                    <label class="form-label fw-semibold">Destinatários *</label>
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

                {{-- Destinatário Livre --}}
                <div class="form-floating mb-4">
                    <input type="text" name="destinatario_livre" id="destinatario_livre" class="form-control rounded-3" placeholder="Destinatário livre">
                    <label for="destinatario_livre">Destinatário Livre</label>
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
                <div class="mb-4">
                    <label for="estado_id" class="form-label fw-semibold">Estado *</label>
                    <select name="estado_id" id="estado_id" class="form-select rounded-3" required>
                        <option value="">-- Selecione --</option>
                        @foreach ($estados as $estado)
                            <option value="{{ $estado->id }}" {{ $estado->name === 'Aguardar' ? 'selected' : '' }}>
                                {{ $estado->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

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

        document.querySelector('form').addEventListener('submit', function (e) {
            const temUsers = document.querySelectorAll('input[name="destinatarios_users[]"]').length > 0;
            const temGrupos = document.querySelector('#destinatarios_grupos')?.selectedOptions.length > 0;
            const livre = document.getElementById('destinatario_livre').value.trim();

            if (!temUsers && !temGrupos && !livre) {
                e.preventDefault();
                alert('Por favor, selecione ao menos um destinatário (usuário, grupo ou livre).');
            }
        });
    });
</script>
@endpush
