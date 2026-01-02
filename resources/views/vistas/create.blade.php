<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Vista</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="container mt-4">
    <h3>➕ Criar Vista</h3>

    <form action="{{ route('recados.index') }}" method="GET">

        {{-- Nome da Vista --}}
        <div class="mb-3">
            <label class="form-label">Nome da Vista</label>
            <input type="text" name="vista_nome" class="form-control" 
                   placeholder="Ex: Recados Abertos" value="{{ old('vista_nome', '') }}">
        </div>

        {{-- Lógica --}}
        <div class="mb-3">
            <label class="form-label">Lógica</label>
            <select name="logica" class="form-select">
                <option value="AND" {{ old('logica')=='AND'?'selected':'' }}>AND</option>
                <option value="OR" {{ old('logica')=='OR'?'selected':'' }}>OR</option>
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

        <button type="submit" class="btn btn-primary">Aplicar Vista</button>
    </form>
</div>

<script>
const fieldsConfig = {
    name: { label: 'Nome', type: 'text' },
    contact_client: { label: 'Contacto', type: 'text' },
    plate: { label: 'Matrícula', type: 'text' },
    operator_email: { label: 'Email Operador', type: 'text' },
    mensagem: { label: 'Mensagem', type: 'text' },
    estado_id: {
        label: 'Estado',
        type: 'select',
        options: @json($estados->map(fn($e)=>['id'=>$e->id,'name'=>$e->name]))
    },
    sla_id: {
        label: 'SLA',
        type: 'select',
        options: @json($slas->map(fn($s)=>['id'=>$s->id,'name'=>$s->name]))
    },
    abertura: { label: 'Data de Abertura', type: 'date' }
};

let index = 0;

// Recupera valores antigos do Blade, se existirem
const oldConditions = {!! json_encode(old('conditions', [])) !!};

function addCondition(data = null) {
    const row = document.createElement('div');
    row.className = 'row g-2 mb-2';

    // FIELD
    const field = document.createElement('select');
    field.name = `conditions[${index}][field]`;
    field.className = 'form-select col';

    field.innerHTML = Object.entries(fieldsConfig)
        .map(([key, cfg]) => `<option value="${key}" ${data?.field===key?'selected':''}>${cfg.label}</option>`)
        .join('');

    // OPERATOR
    const operator = document.createElement('select');
    operator.name = `conditions[${index}][operator]`;
    operator.className = 'form-select col';
    operator.innerHTML = `
        <option value="=" ${data?.operator==='='?'selected':''}>=</option>
        <option value="!=" ${data?.operator==='!='?'selected':''}>≠</option>
        <option value="like" ${data?.operator==='like'?'selected':''}>Contém</option>
    `;

    // VALUE
    const valueDiv = document.createElement('div');
    valueDiv.className = 'col';

    function renderValue() {
        valueDiv.innerHTML = '';
        const cfg = fieldsConfig[field.value];
        let val = data?.value ?? '';

        if (cfg.type === 'select') {
            const sel = document.createElement('select');
            sel.name = `conditions[${index}][value]`;
            sel.className = 'form-select';
            sel.innerHTML = cfg.options
                .map(o => `<option value="${o.id}" ${o.id == val?'selected':''}>${o.name}</option>`)
                .join('');
            valueDiv.appendChild(sel);
        } else {
            const input = document.createElement('input');
            input.type = cfg.type;
            input.name = `conditions[${index}][value]`;
            input.className = 'form-control';
            input.value = val;
            valueDiv.appendChild(input);
        }
    }

    field.onchange = renderValue;
    renderValue();

    row.append(field, operator, valueDiv);
    document.getElementById('conditions').appendChild(row);

    index++;
}

// Inicializa com old conditions ou cria uma primeira vazia
document.addEventListener('DOMContentLoaded', () => {
    if (oldConditions.length) {
        oldConditions.forEach(c => addCondition(c));
    } else {
        addCondition();
    }
});
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
