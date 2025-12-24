<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Vista</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
    <h3>➕ Criar Vista</h3>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vistas.store') }}" method="POST">
        @csrf

        {{-- Nome da Vista --}}
        <div class="mb-3">
            <label for="name" class="form-label">Nome da Vista</label>
            <input type="text" name="nome" id="name" class="form-control" required value="{{ old('nome') }}">
        </div>

        {{-- Lógica --}}
        <div class="mb-3">
            <label for="logica" class="form-label">Lógica das condições</label>
            <select name="logica" id="logica" class="form-select" required>
                <option value="AND" {{ old('logica')=='AND'?'selected':'' }}>AND</option>
                <option value="OR" {{ old('logica')=='OR'?'selected':'' }}>OR</option>
            </select>
        </div>

        {{-- Acesso --}}
        <div class="mb-3">
            <label for="acesso" class="form-label">Acesso</label>
            <select name="access_type" id="acesso" class="form-select" required>
                <option value="all" {{ old('access_type')=='all'?'selected':'' }}>Todos</option>
                <option value="department" {{ old('access_type')=='department'?'selected':'' }}>Departamento</option>
                <option value="owner" {{ old('access_type')=='owner'?'selected':'' }}>Proprietário</option>
                <option value="specific" {{ old('access_type')=='specific'?'selected':'' }}>Utilizadores específicos</option>
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

        <button type="submit" class="btn btn-primary">Guardar Vista</button>
    </form>
</div>

<script>
const fieldsConfig = {
    name: { label: 'Nome', type: 'text', operators: ['=','!=','like'] },
    contact_client: { label: 'Contacto', type: 'text', operators: ['=','!=','like'] },
    plate: { label: 'Matrícula', type: 'text', operators: ['=','!=','like'] },
    operator_email: { label: 'Email Operador', type: 'text', operators: ['=','!=','like'] },
    mensagem: { label: 'Mensagem', type: 'text', operators: ['like'] },
    observacoes: { label: 'Observações', type: 'text', operators: ['like'] },
    wip: { label: 'WIP', type: 'text', operators: ['=','!=','like'] },

    estado_id: { label: 'Estado', type: 'select', operators: ['=','!='], options: @json($estados->map(fn($e)=>['id'=>$e->id,'name'=>$e->name])) },
    tipo_formulario_id: { label: 'Tipo de Formulário', type: 'select', operators: ['=','!='], options: @json($tiposFormulario->map(fn($t)=>['id'=>$t->id,'name'=>$t->name])) },
    sla_id: { label: 'SLA', type: 'select', operators: ['=','!='], options: @json($slas->map(fn($s)=>['id'=>$s->id,'name'=>$s->name])) },
    tipo_id: { label: 'Tipo', type: 'select', operators: ['=','!='], options: @json($tipos->map(fn($t)=>['id'=>$t->id,'name'=>$t->name])) },
    origem_id: { label: 'Origem', type: 'select', operators: ['=','!='], options: @json($origens->map(fn($o)=>['id'=>$o->id,'name'=>$o->name])) },
    setor_id: { label: 'Setor', type: 'select', operators: ['=','!='], options: @json($setores->map(fn($s)=>['id'=>$s->id,'name'=>$s->name])) },
    departamento_id: { label: 'Departamento', type: 'select', operators: ['=','!='], options: @json($departamentos->map(fn($d)=>['id'=>$d->id,'name'=>$d->name])) },
    aviso_id: { label: 'Aviso', type: 'select', operators: ['=','!='], options: @json($avisos->map(fn($a)=>['id'=>$a->id,'name'=>$a->name])) },

    abertura: { label: 'Data de Abertura', type: 'date', operators: ['=','>=','<='] },
    termino: { label: 'Data de Término', type: 'date', operators: ['=','>=','<='] }
};

let conditionIndex = 0;

function addCondition(data = {}) {
    const wrapper = document.createElement('div');
    wrapper.className = 'row g-2 align-items-center mb-2';

    const fieldSelect = document.createElement('select');
    fieldSelect.className = 'form-select col';
    fieldSelect.name = `conditions[${conditionIndex}][field]`;
    fieldSelect.innerHTML = Object.entries(fieldsConfig)
        .map(([key,cfg]) => `<option value="${key}" ${data.field===key?'selected':''}>${cfg.label}</option>`).join('');

    const operatorSelect = document.createElement('select');
    operatorSelect.className = 'form-select col';
    operatorSelect.name = `conditions[${conditionIndex}][operator]`;
    operatorSelect.innerHTML = `<option value="=">=</option><option value="!=">≠</option><option value="like">Contém</option>`;
    if(data.operator) operatorSelect.value = data.operator;

    const valueWrapper = document.createElement('div');
    valueWrapper.className = 'col';

    function renderValue(field){
        valueWrapper.innerHTML = '';
        if(!field || !fieldsConfig[field]) return;
        const cfg = fieldsConfig[field];
        if(cfg.type==='select'){
            const sel = document.createElement('select');
            sel.className='form-select';
            sel.name = `conditions[${conditionIndex}][value]`;
            sel.innerHTML = cfg.options.map(o=>`<option value="${o.id}" ${data.value==o.id?'selected':''}>${o.name}</option>`).join('');
            valueWrapper.appendChild(sel);
        } else {
            const input = document.createElement('input');
            input.type = cfg.type==='date'?'date':'text';
            input.name = `conditions[${conditionIndex}][value]`;
            input.className = 'form-control';
            input.value = data.value??'';
            valueWrapper.appendChild(input);
        }
    }

    fieldSelect.onchange = ()=>renderValue(fieldSelect.value);
    if(data.field) renderValue(data.field);

    wrapper.appendChild(divWrap(fieldSelect));
    wrapper.appendChild(divWrap(operatorSelect));
    wrapper.appendChild(valueWrapper);

    document.getElementById('conditions').appendChild(wrapper);
    conditionIndex++;
}

function divWrap(el){
    const d = document.createElement('div');
    d.className='col';
    d.appendChild(el);
    return d;
}

// Cria a primeira condição automaticamente
addCondition();
</script>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
