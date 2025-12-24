<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Vista</title>

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">

    <h3 class="mb-3">✏️ Editar Vista</h3>

    {{-- Botão voltar --}}
    <div class="mb-3">
        <button type="button" class="btn btn-secondary" onclick="history.back()">← Voltar</button>
    </div>

    {{-- Erros --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vistas.update', $vista->id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- Nome --}}
        <div class="mb-3">
            <label class="form-label">Nome da Vista</label>
            <input
                type="text"
                name="nome"
                class="form-control"
                value="{{ old('nome', $vista->nome) }}"
                placeholder="Deixe vazio para manter o nome atual">
        </div>

        {{-- Condições --}}
        <div class="mb-3">
            <label class="form-label">Condições</label>

            <div id="conditions"></div>

            <button
                type="button"
                class="btn btn-sm btn-outline-secondary mt-2"
                onclick="addCondition()">
                ➕ Adicionar condição
            </button>
        </div>

        {{-- Acessos --}}
        <div class="mb-4">
            <label class="form-label">Acessos</label>
            <select name="access_type" class="form-select" required>
                <option value="all" {{ $vista->access_type==='all' ? 'selected' : '' }}>Todos</option>
                <option value="department" {{ $vista->access_type==='department' ? 'selected' : '' }}>Departamento</option>
                <option value="specific" {{ $vista->access_type==='specific' ? 'selected' : '' }}>Utilizadores específicos</option>
                <option value="owner" {{ $vista->access_type==='owner' ? 'selected' : '' }}>Apenas o criador</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            💾 Guardar Vista
        </button>

    </form>
</div>

<script>
const fieldsConfig = {!! json_encode([
    'name'=>['label'=>'Nome','type'=>'text','operators'=>['=','!=','like']],
    'contact_client'=>['label'=>'Contacto','type'=>'text','operators'=>['=','!=','like']],
    'plate'=>['label'=>'Matrícula','type'=>'text','operators'=>['=','!=','like']],
    'operator_email'=>['label'=>'Email Operador','type'=>'text','operators'=>['=','!=','like']],
    'mensagem'=>['label'=>'Mensagem','type'=>'text','operators'=>['like']],
    'observacoes'=>['label'=>'Observações','type'=>'text','operators'=>['like']],
    'wip'=>['label'=>'WIP','type'=>'text','operators'=>['=','!=','like']],
    'estado_id'=>['label'=>'Estado','type'=>'select','operators'=>['=','!='],'options'=>$estados],
    'tipo_formulario_id'=>['label'=>'Tipo de Formulário','type'=>'select','operators'=>['=','!='],'options'=>$tiposFormulario],
    'sla_id'=>['label'=>'SLA','type'=>'select','operators'=>['=','!='],'options'=>$slas],
    'tipo_id'=>['label'=>'Tipo','type'=>'select','operators'=>['=','!='],'options'=>$tipos],
    'origem_id'=>['label'=>'Origem','type'=>'select','operators'=>['=','!='],'options'=>$origens],
    'setor_id'=>['label'=>'Setor','type'=>'select','operators'=>['=','!='],'options'=>$setores],
    'departamento_id'=>['label'=>'Departamento','type'=>'select','operators'=>['=','!='],'options'=>$departamentos],
    'aviso_id'=>['label'=>'Aviso','type'=>'select','operators'=>['=','!='],'options'=>$avisos],
    'abertura'=>['label'=>'Data de Abertura','type'=>'date','operators'=>['=','>=','<=']],
    'termino'=>['label'=>'Data de Término','type'=>'date','operators'=>['=','>=','<=']],
]) !!};

let conditionIndex = 0;

function addCondition(data = {}) {

    const wrapper = document.createElement('div');
    wrapper.className = 'row g-2 align-items-center mb-2';

    // Campo
    const fieldSelect = document.createElement('select');
    fieldSelect.className = 'form-select';
    fieldSelect.name = `conditions[${conditionIndex}][field]`;
    fieldSelect.innerHTML = Object.entries(fieldsConfig)
        .map(([key, cfg]) =>
            `<option value="${key}" ${data.field === key ? 'selected' : ''}>${cfg.label}</option>`
        ).join('');

    // Operador
    const operatorSelect = document.createElement('select');
    operatorSelect.className = 'form-select';
    operatorSelect.name = `conditions[${conditionIndex}][operator]`;
    operatorSelect.innerHTML = ['=','!=','like']
        .map(op =>
            `<option value="${op}" ${data.operator === op ? 'selected' : ''}>${op}</option>`
        ).join('');

    // Valor
    const valueWrapper = document.createElement('div');

    function renderValue(field) {
        valueWrapper.innerHTML = '';
        if (!fieldsConfig[field]) return;

        const cfg = fieldsConfig[field];

        if (cfg.type === 'select') {
            const sel = document.createElement('select');
            sel.className = 'form-select';
            sel.name = `conditions[${conditionIndex}][value]`;
            sel.innerHTML = cfg.options
                .map(o =>
                    `<option value="${o.id}" ${String(data.value) === String(o.id) ? 'selected' : ''}>${o.name}</option>`
                ).join('');
            valueWrapper.appendChild(sel);
        } else {
            const input = document.createElement('input');
            input.type = cfg.type === 'date' ? 'date' : 'text';
            input.className = 'form-control';
            input.name = `conditions[${conditionIndex}][value]`;
            input.value = data.value ?? '';
            valueWrapper.appendChild(input);
        }
    }

    fieldSelect.onchange = () => renderValue(fieldSelect.value);
    if (data.field) renderValue(data.field);

    // Remover
    const removeBtn = document.createElement('button');
    removeBtn.type = 'button';
    removeBtn.className = 'btn btn-outline-danger btn-sm';
    removeBtn.innerText = '🗑️';
    removeBtn.onclick = () => wrapper.remove();

    wrapper.appendChild(wrap(fieldSelect));
    wrapper.appendChild(wrap(operatorSelect));
    wrapper.appendChild(wrap(valueWrapper));
    wrapper.appendChild(wrap(removeBtn));

    document.getElementById('conditions').appendChild(wrapper);
    conditionIndex++;
}

function wrap(el) {
    const d = document.createElement('div');
    d.className = 'col';
    d.appendChild(el);
    return d;
}

// Carregar condições existentes
@foreach($vista->filtros['conditions'] ?? [] as $cond)
    addCondition(@json($cond));
@endforeach
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
