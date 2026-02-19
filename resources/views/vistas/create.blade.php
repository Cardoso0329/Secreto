<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Vista</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4" style="max-width: 1050px;">

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h3 class="mb-0">➕ Criar Vista</h3>
            <small class="text-muted">Define filtros e regras de acesso</small>
        </div>
        <button type="button" class="btn btn-secondary" onclick="history.back()">← Voltar</button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Há erros no formulário:</strong>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">

            <form action="{{ route('vistas.store') }}" method="POST">
                @csrf

                {{-- Nome --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nome da Vista</label>
                    <input type="text" name="nome" class="form-control"
                           placeholder="Ex: Recados Abertos"
                           value="{{ old('nome', '') }}" required>
                </div>

                {{-- Lógica --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Lógica</label>
                    <select name="logica" class="form-select" required>
                        <option value="AND" {{ old('logica','AND')=='AND'?'selected':'' }}>AND</option>
                        <option value="OR"  {{ old('logica')=='OR'?'selected':'' }}>OR</option>
                    </select>
                    <div class="form-text">
                        Dica: se precisares de "Departamento = APV" E "Tipo IN (A,B)", usa AND e mete o Tipo com IN.
                    </div>
                </div>

                {{-- Acesso --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Acesso</label>
                    <select name="acesso" class="form-select" id="acesso" required onchange="toggleAccessBlocks()">
                        <option value="all" {{ old('acesso','all')=='all'?'selected':'' }}>Todos</option>
                        <option value="department" {{ old('acesso')=='department'?'selected':'' }}>Departamento</option>
                        <option value="specific" {{ old('acesso')=='specific'?'selected':'' }}>Utilizadores específicos</option>
                    </select>
                </div>

                {{-- Departamentos (se department) --}}
                <div class="mb-3" id="block_departamentos" style="display:none;">
                    <label class="form-label fw-semibold">Departamentos com acesso</label>
                    <select name="departamentos[]" class="form-select" multiple>
                        @foreach($departamentos as $d)
                            <option value="{{ $d->id }}" {{ collect(old('departamentos', []))->contains($d->id) ? 'selected' : '' }}>
                                {{ $d->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Seleciona 1+ departamentos.</div>
                </div>

                {{-- Users (se specific) --}}
                <div class="mb-3" id="block_users" style="display:none;">
                    <label class="form-label fw-semibold">Utilizadores com acesso</label>
                    <select name="users[]" class="form-select" multiple>
                        @foreach($users as $u)
                            <option value="{{ $u->id }}" {{ collect(old('users', []))->contains($u->id) ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->email }})
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">Seleciona 1+ utilizadores.</div>
                </div>

                {{-- Condições --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Condições</label>
                    <div id="conditions"></div>
                    <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addCondition()">
                        <i class="bi bi-plus-lg me-1"></i> Adicionar condição
                    </button>
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save2 me-1"></i> Guardar Vista
                </button>
            </form>

        </div>
    </div>
</div>

<script>
const fieldsConfig = {
    id: { label: 'ID', type: 'text' },
    name: { label: 'Nome', type: 'text' },
    contact_client: { label: 'Contacto', type: 'text' },
    plate: { label: 'Matrícula', type: 'text' },
    operator_email: { label: 'Email Operador', type: 'text' },
    mensagem: { label: 'Mensagem', type: 'text' },

    estado_id: {
        label: 'Estado',
        type: 'select',
        options: @json($estados->map(fn($e)=>['id'=>$e->id,'name'=>$e->name])->values())
    },
    tipo_formulario_id: {
        label: 'Tipo de Formulário',
        type: 'select',
        options: @json($tiposFormulario->map(fn($t)=>['id'=>$t->id,'name'=>$t->name])->values())
    },
    sla_id: {
        label: 'SLA',
        type: 'select',
        options: @json($slas->map(fn($s)=>['id'=>$s->id,'name'=>$s->name])->values())
    },
    campanha_id: {
        label: 'Campanha',
        type: 'select',
        options: @json($campanhas->map(fn($c)=>['id'=>$c->id,'name'=>$c->name])->values())
    },

    setor_id: {
        label: 'Setor',
        type: 'select',
        options: @json($setores->map(fn($s)=>['id'=>$s->id,'name'=>$s->name])->values())
    },
    origem_id: {
        label: 'Origem',
        type: 'select',
        options: @json($origens->map(fn($o)=>['id'=>$o->id,'name'=>$o->name])->values())
    },
    tipo_id: {
        label: 'Tipo',
        type: 'select',
        options: @json($tipos->map(fn($t)=>['id'=>$t->id,'name'=>$t->name])->values())
    },
    aviso_id: {
        label: 'Aviso',
        type: 'select',
        options: @json($avisos->map(fn($a)=>['id'=>$a->id,'name'=>$a->name])->values())
    },

    departamento_id: {
        label: 'Departamento',
        type: 'select',
        options: @json($departamentos->map(fn($d)=>['id'=>$d->id,'name'=>$d->name])->values())
    },

    // ✅ NOVO: Chefias nas condições
    chefia_id: {
        label: 'Chefia',
        type: 'select',
        options: @json($chefias->map(fn($c)=>['id'=>$c->id,'name'=>$c->name])->values())
    },

    destinatario_user_id: {
        label: 'Destinatário (Utilizador)',
        type: 'select',
        options: @json($users->map(fn($u)=>['id'=>$u->id,'name'=>$u->name])->values())
    },

    // ✅ NOVO: Grupo nas condições
    grupo_id: {
        label: 'Grupo (Destinatários)',
        type: 'select',
        options: @json($grupos->map(fn($g)=>['id'=>$g->id,'name'=>$g->name])->values())
    },

    abertura: { label: 'Data de Abertura', type: 'date' }
};

let index = 0;
const oldConditions = {!! json_encode(old('conditions', [])) !!};

function toggleAccessBlocks() {
    const acesso = document.getElementById('acesso').value;
    document.getElementById('block_departamentos').style.display = acesso === 'department' ? 'block' : 'none';
    document.getElementById('block_users').style.display = acesso === 'specific' ? 'block' : 'none';
}

function operatorOptionsHTML(isSelect, selectedOp) {
    const sel = (v) => (String(selectedOp).toLowerCase() === String(v).toLowerCase()) ? 'selected' : '';
    let html = `
        <option value="=" ${sel('=')}>=</option>
        <option value="!=" ${sel('!=')}>≠</option>
        <option value="like" ${sel('like')}>Contém</option>
    `;

    if (isSelect) {
        html += `
            <option value="in" ${sel('in')}>IN (um destes)</option>
            <option value="not in" ${sel('not in')}>NOT IN</option>
        `;
    }

    return html;
}

function addCondition(data = null) {
    const rowIndex = index;
    index++;

    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 align-items-center';

    const field = document.createElement('select');
    field.name = `conditions[${rowIndex}][field]`;
    field.className = 'form-select col';
    field.innerHTML = Object.entries(fieldsConfig)
        .map(([key, cfg]) => `<option value="${key}" ${data?.field===key?'selected':''}>${cfg.label}</option>`)
        .join('');

    const operator = document.createElement('select');
    operator.name = `conditions[${rowIndex}][operator]`;
    operator.className = 'form-select col';

    const valueDiv = document.createElement('div');
    valueDiv.className = 'col';

    function renderOperatorAndValue() {
        const cfg = fieldsConfig[field.value];
        const isSelect = cfg.type === 'select';

        let currentOp = (operator.value || data?.operator || '=');
        operator.innerHTML = operatorOptionsHTML(isSelect, currentOp);

        if (!isSelect && (String(currentOp).toLowerCase() === 'in' || String(currentOp).toLowerCase() === 'not in')) {
            operator.value = '=';
        } else {
            operator.value = String(currentOp).toLowerCase() === 'not in' ? 'not in' : String(currentOp).toLowerCase();
            if (currentOp === '=' || currentOp === '!=' || String(currentOp).toLowerCase() === 'like') operator.value = currentOp;
        }

        renderValue();
    }

    function renderValue() {
        valueDiv.innerHTML = '';
        const cfg = fieldsConfig[field.value];
        const op  = String(operator.value || '').toLowerCase();

        const val = data?.value ?? '';

        if (cfg.type === 'select') {
            if (op === 'in' || op === 'not in') {
                const selectedArr = Array.isArray(val) ? val.map(String) : (val ? [String(val)] : []);

                const sel = document.createElement('select');
                sel.name = `conditions[${rowIndex}][value][]`;
                sel.className = 'form-select';
                sel.multiple = true;

                sel.innerHTML = cfg.options
                    .map(o => `<option value="${o.id}" ${selectedArr.includes(String(o.id)) ? 'selected' : ''}>${o.name}</option>`)
                    .join('');

                valueDiv.appendChild(sel);

                const hint = document.createElement('div');
                hint.className = 'form-text';
                hint.textContent = 'Seleciona 1+ valores.';
                valueDiv.appendChild(hint);
            } else {
                const sel = document.createElement('select');
                sel.name = `conditions[${rowIndex}][value]`;
                sel.className = 'form-select';

                sel.innerHTML = `<option value="">—</option>` + cfg.options
                    .map(o => `<option value="${o.id}" ${String(o.id) === String(val) ? 'selected' : ''}>${o.name}</option>`)
                    .join('');

                valueDiv.appendChild(sel);
            }
        } else {
            const input = document.createElement('input');
            input.type = cfg.type;
            input.name = `conditions[${rowIndex}][value]`;
            input.className = 'form-control';
            input.value = Array.isArray(val) ? (val[0] ?? '') : val;
            valueDiv.appendChild(input);
        }
    }

    field.onchange = renderOperatorAndValue;
    operator.onchange = renderValue;

    renderOperatorAndValue();

    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'btn btn-outline-danger btn-sm';
    removeBtn.innerHTML = '<i class="bi bi-trash"></i>';
    removeBtn.onclick = () => row.remove();

    const btnCol = document.createElement('div');
    btnCol.className = 'col-auto d-flex align-items-center';
    btnCol.appendChild(removeBtn);

    row.append(field, operator, valueDiv, btnCol);
    document.getElementById('conditions').appendChild(row);
}

document.addEventListener('DOMContentLoaded', () => {
    toggleAccessBlocks();
    if (oldConditions.length) oldConditions.forEach(c => addCondition(c));
    else addCondition();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
