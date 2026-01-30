<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Vista</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">✏️ Editar Vista</h3>
        <button type="button" class="btn btn-secondary" onclick="history.back()">← Voltar</button>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vistas.update', $vista['id']) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Nome --}}
        <div class="mb-3">
            <label class="form-label">Nome da Vista</label>
            <input type="text" name="nome" class="form-control"
                   value="{{ old('nome', $vista['nome'] ?? '') }}" required>
        </div>

        {{-- Lógica --}}
        <div class="mb-3">
            <label class="form-label">Lógica</label>
            <select name="logica" class="form-select" required>
                @php $logicaOld = old('logica', $vista['logica'] ?? 'AND'); @endphp
                <option value="AND" {{ $logicaOld === 'AND' ? 'selected' : '' }}>AND</option>
                <option value="OR"  {{ $logicaOld === 'OR'  ? 'selected' : '' }}>OR</option>
            </select>
            <div class="form-text">
                Dica: se precisares de "Departamento = APV" E "Tipo IN (A,B)", usa AND e mete o Tipo com IN.
            </div>
        </div>

        {{-- Acesso --}}
        <div class="mb-3">
            <label class="form-label">Acesso</label>
            <select name="acesso" class="form-select" id="acesso" required onchange="toggleAccessBlocks()">
                @php $acessoOld = old('acesso', $vista['acesso'] ?? 'all'); @endphp
                <option value="all" {{ $acessoOld==='all'?'selected':'' }}>Todos</option>
                <option value="department" {{ $acessoOld==='department'?'selected':'' }}>Departamento</option>
                <option value="specific" {{ $acessoOld==='specific'?'selected':'' }}>Utilizadores específicos</option>
            </select>
        </div>

        {{-- Departamentos --}}
        <div class="mb-3" id="block_departamentos" style="display:none;">
            <label class="form-label">Departamentos com acesso</label>
            @php
                $selectedDeps = collect(old('departamentos', $vista['departamentos'] ?? []))
                    ->map(fn($x)=>(int)$x)->all();
            @endphp
            <select name="departamentos[]" class="form-select" multiple>
                @foreach($departamentos as $d)
                    <option value="{{ $d->id }}" {{ in_array((int)$d->id, $selectedDeps, true) ? 'selected' : '' }}>
                        {{ $d->name }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Users --}}
        <div class="mb-3" id="block_users" style="display:none;">
            <label class="form-label">Utilizadores com acesso</label>
            @php
                $selectedUsers = collect(old('users', $vista['users'] ?? []))
                    ->map(fn($x)=>(int)$x)->all();
            @endphp
            <select name="users[]" class="form-select" multiple>
                @foreach($users as $u)
                    <option value="{{ $u->id }}" {{ in_array((int)$u->id, $selectedUsers, true) ? 'selected' : '' }}>
                        {{ $u->name }} ({{ $u->email }})
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Condições --}}
        <div class="mb-3">
            <label class="form-label">Condições</label>
            <div id="conditions"></div>

            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="addCondition()">
                ➕ Adicionar condição
            </button>
        </div>

        <button type="submit" class="btn btn-primary">💾 Guardar Vista</button>
    </form>
</div>

@php
    /**
     * ✅ normaliza filtros guardados:
     * - array direto
     * - formato antigo { conditions: [...] }
     * - string JSON
     */
    $stored = $vista['filtros'] ?? [];

    if (is_array($stored) && array_key_exists('conditions', $stored)) {
        $stored = $stored['conditions'] ?? [];
    }

    if (is_string($stored)) {
        $decoded = json_decode($stored, true);
        if (is_array($decoded)) $stored = $decoded;
    }

    if (!is_array($stored)) $stored = [];

    $existingConditionsPhp = old('conditions', $stored);
@endphp

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
        options: @json(($chefias ?? collect())->map(fn($c)=>['id'=>$c->id,'name'=>$c->name])->values())
    },

    destinatario_user_id: {
        label: 'Destinatário (Utilizador)',
        type: 'select',
        options: @json($users->map(fn($u)=>['id'=>$u->id,'name'=>$u->name])->values())
    },

    // ✅ Grupo nas condições
    grupo_id: {
        label: 'Grupo',
        type: 'select',
        options: @json(($grupos ?? collect())->map(fn($g)=>['id'=>$g->id,'name'=>$g->name])->values())
    },

    abertura: { label: 'Data de Abertura', type: 'date' }
};

let conditionIndex = 0;
const existingConditions = @json($existingConditionsPhp);

function toggleAccessBlocks() {
    const acesso = document.getElementById('acesso').value;
    document.getElementById('block_departamentos').style.display = acesso === 'department' ? 'block' : 'none';
    document.getElementById('block_users').style.display = acesso === 'specific' ? 'block' : 'none';
}

function operatorOptionsHTML(isSelect, selectedOp) {
    const norm = (v) => String(v ?? '').trim().toLowerCase();
    const sel = (v) => norm(selectedOp) === norm(v) ? 'selected' : '';

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

function addCondition(data = {}) {
    const rowIndex = conditionIndex;
    conditionIndex++;

    const row = document.createElement('div');
    row.className = 'row g-2 mb-2 align-items-center';

    const field = document.createElement('select');
    field.className = 'form-select col';
    field.name = `conditions[${rowIndex}][field]`;
    field.innerHTML = Object.entries(fieldsConfig)
        .map(([key, cfg]) => `<option value="${key}" ${String(data.field) === String(key) ? 'selected' : ''}>${cfg.label}</option>`)
        .join('');

    const operator = document.createElement('select');
    operator.className = 'form-select col';
    operator.name = `conditions[${rowIndex}][operator]`;

    const valueDiv = document.createElement('div');
    valueDiv.className = 'col';

    function renderOperatorAndValue() {
        const cfg = fieldsConfig[field.value];
        const isSelect = cfg.type === 'select';

        let currentOp = (operator.value || data.operator || '=');
        operator.innerHTML = operatorOptionsHTML(isSelect, currentOp);

        const normOp = String(currentOp ?? '').trim().toLowerCase();

        // se veio IN mas o campo não é select -> reset
        if (!isSelect && (normOp === 'in' || normOp === 'not in')) {
            operator.value = '=';
        } else {
            if (normOp === 'not in') operator.value = 'not in';
            else if (normOp === 'in') operator.value = 'in';
            else if (normOp === 'like') operator.value = 'like';
            else if (normOp === '!=') operator.value = '!=';
            else operator.value = '=';
        }

        renderValue();
    }

    function renderValue() {
        valueDiv.innerHTML = '';
        const cfg = fieldsConfig[field.value];
        const op  = String(operator.value || '').trim().toLowerCase();
        const val = data.value ?? '';

        if (cfg.type === 'select') {
            if (op === 'in' || op === 'not in') {
                const selectedArr = Array.isArray(val) ? val.map(String) : (val ? [String(val)] : []);

                const sel = document.createElement('select');
                sel.className = 'form-select';
                sel.name = `conditions[${rowIndex}][value][]`;
                sel.multiple = true;

                sel.innerHTML = (cfg.options || [])
                    .map(o => `<option value="${o.id}" ${selectedArr.includes(String(o.id)) ? 'selected' : ''}>${o.name}</option>`)
                    .join('');

                valueDiv.appendChild(sel);

                const hint = document.createElement('div');
                hint.className = 'form-text';
                hint.textContent = 'Seleciona 1+ valores.';
                valueDiv.appendChild(hint);
            } else {
                const sel = document.createElement('select');
                sel.className = 'form-select';
                sel.name = `conditions[${rowIndex}][value]`;

                sel.innerHTML = `<option value="">—</option>` + (cfg.options || [])
                    .map(o => `<option value="${o.id}" ${String(o.id) === String(val) ? 'selected' : ''}>${o.name}</option>`)
                    .join('');

                valueDiv.appendChild(sel);
            }
        } else {
            const input = document.createElement('input');
            input.type = cfg.type;
            input.className = 'form-control';
            input.name = `conditions[${rowIndex}][value]`;
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
    removeBtn.textContent = '🗑️';
    removeBtn.onclick = () => row.remove();

    const btnCol = document.createElement('div');
    btnCol.className = 'col-auto d-flex align-items-center';
    btnCol.appendChild(removeBtn);

    row.append(field, operator, valueDiv, btnCol);
    document.getElementById('conditions').appendChild(row);
}

document.addEventListener('DOMContentLoaded', () => {
    toggleAccessBlocks();

    if (Array.isArray(existingConditions) && existingConditions.length) {
        existingConditions.forEach(c => addCondition(c));
    } else {
        addCondition();
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
