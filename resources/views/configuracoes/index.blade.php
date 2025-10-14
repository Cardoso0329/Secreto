@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h3>⚙️ Configurações</h3>

    <!-- Nav Tabs -->
    <ul class="nav nav-tabs" id="configTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="recados-tab" data-bs-toggle="tab" data-bs-target="#recados" type="button" role="tab">📋 Recados</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="users-tab" data-bs-toggle="tab" data-bs-target="#users" type="button" role="tab">👥 Utilizadores</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="setores-tab" data-bs-toggle="tab" data-bs-target="#setores" type="button" role="tab">🏢 Setores</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="departamentos-tab" data-bs-toggle="tab" data-bs-target="#departamentos" type="button" role="tab">🗂️ Departamentos</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="cargos-tab" data-bs-toggle="tab" data-bs-target="#cargos" type="button" role="tab">🎭 Cargos</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="slas-tab" data-bs-toggle="tab" data-bs-target="#slas" type="button" role="tab">⏱️ SLAs</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="origens-tab" data-bs-toggle="tab" data-bs-target="#origens" type="button" role="tab">🌐 Origens</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tipos-tab" data-bs-toggle="tab" data-bs-target="#tipos" type="button" role="tab">📌 Tipos</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="avisos-tab" data-bs-toggle="tab" data-bs-target="#avisos" type="button" role="tab">⚠️ Avisos</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="grupos-tab" data-bs-toggle="tab" data-bs-target="#grupos" type="button" role="tab">👥 Grupos</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="destinatarios-tab" data-bs-toggle="tab" data-bs-target="#destinatarios" type="button" role="tab">📬 Destinatários</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="estados-tab" data-bs-toggle="tab" data-bs-target="#estados" type="button" role="tab">📊 Estados</button>
        </li>
    </ul>

    <!-- Conteúdo das abas -->
    <div class="tab-content mt-3">

       <!-- Recados -->
<div class="tab-pane fade show active" id="recados" role="tabpanel">
    <h5>📋 Gerir Recados</h5>

    {{-- Cabeçalho e ações de exportar/importar --}}
    <div class="d-flex flex-wrap gap-2 mb-4">

        {{-- Exportar Todos --}}
        <div class="card flex-grow-1 shadow-sm border-0 hover-shadow transition" style="min-width: 200px;">
            <div class="card-body d-flex flex-column align-items-center text-center py-3">
                <i class="bi bi-file-earmark-arrow-down fs-2 text-success mb-2"></i>
                <h6 class="fw-bold">Exportar Todos</h6>
                <a href="{{ route('recados.export') }}" class="btn btn-outline-success btn-sm mt-2 w-100">
                    Exportar
                </a>
            </div>
        </div>

        {{-- Exportar Filtrados --}}
        <div class="card flex-grow-1 shadow-sm border-0 hover-shadow transition" style="min-width: 200px;">
            <div class="card-body d-flex flex-column align-items-center text-center py-3">
                <i class="bi bi-funnel fs-2 text-success mb-2"></i>
                <h6 class="fw-bold">Exportar Filtrados</h6>
               <a href="{{ route('configuracoes.recados.export.filtered', request()->only(['id','contact_client','plate','estado_id','tipo_formulario_id'])) }}" 
   class="btn btn-success btn-sm mt-2 w-100">
   Exportar
</a>



            </div>
        </div>

        {{-- Importar Recados --}}
        <div class="card flex-grow-1 shadow-sm border-0 hover-shadow transition" style="min-width: 200px;">
            <div class="card-body d-flex flex-column align-items-center text-center py-3">
                <i class="bi bi-file-earmark-arrow-up fs-2 text-primary mb-2"></i>
                <h6 class="fw-bold">Importar Recados</h6>
                <form action="{{ route('recados.importar') }}" method="POST" enctype="multipart/form-data" class="w-100 mt-2 d-flex flex-column gap-2">
                    @csrf
                    <input type="file" name="file" class="form-control form-control-sm" required>
                    <button type="submit" class="btn btn-primary btn-sm w-100">Importar</button>
                </form>
            </div>
        </div>

    </div>

    {{-- Filtros em Accordion estático sem seta --}}
<div class="mb-4">
    <div class="p-2 mb-2 bg-light border rounded">
        <h5 class="mb-0">🔍 Filtros Avançados</h5>
    </div>
    <div class="p-3 border rounded">
        <form action="{{ route('configuracoes.index') }}" method="GET" class="row g-3">
            <div class="col-md-2">
                <input type="text" name="id" class="form-control" placeholder="ID..." value="{{ request('id') }}">
            </div>
            <div class="col-md-2">
                <input type="text" name="contact_client" class="form-control" placeholder="Contacto..." value="{{ request('contact_client') }}">
            </div>
            <div class="col-md-2">
                <input type="text" name="plate" class="form-control" placeholder="Matrícula..." value="{{ request('plate') }}">
            </div>
            <div class="col-md-3">
                <select name="estado_id" class="form-select">
                    <option value="">Todos os Estados</option>
                    @foreach($estados as $estado)
                        <option value="{{ $estado->id }}" {{ request('estado_id') == $estado->id ? 'selected' : '' }}>
                            {{ $estado->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="tipo_formulario_id" class="form-select">
                    <option value="">Todos os Tipos</option>
                    @foreach($tiposFormulario as $tipo_formulario)
                        <option value="{{ $tipo_formulario->id }}" {{ request('tipo_formulario_id') == $tipo_formulario->id ? 'selected' : '' }}>
                            {{ $tipo_formulario->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </form>
    </div>
</div>


    {{-- Mensagem de sucesso --}}
    @if(session('success'))
        <div class="alert alert-success shadow-sm">
            <i class="bi bi-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Tabela de recados (sem ações) --}}
    <div class="card shadow-sm border-0">
        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Contacto Cliente</th>
                        <th>Matrícula</th>
                        <th>Email Operador</th>
                        <th>Estado</th>
                        <th>Tipo</th>
                        <th class="text-nowrap">Criado em</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recados as $recado)
                        <tr>
                            <td>#{{ $recado->id }}</td>
                            <td>{{ $recado->name }}</td>
                            <td>{{ $recado->contact_client }}</td>
                            <td>{{ $recado->plate ?? '—' }}</td>
                            <td>{{ $recado->operator_email ?? '—' }}</td>
                            <td>
                                <span class="badge rounded-pill {{ strtolower($recado->estado->name ?? '') == 'pendente' ? 'bg-warning text-dark' : 'bg-purple text-white' }}">
                                    {{ $recado->estado->name ?? '—' }}
                                </span>
                            </td>
                            <td>
                                <span class="badge rounded-pill {{ strtolower($recado->tipoFormulario->name ?? '') == 'central' ? 'bg-primary text-white' : 'bg-success text-white' }}">
                                    {{ $recado->tipoFormulario->name ?? '—' }}
                                </span>
                            </td>
                            <td>{{ $recado->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Nenhum recado encontrado.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- Paginação --}}
            <div class="d-flex justify-content-center mt-4">
                {{ $recados->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>


        <!-- Utilizadores -->
        <div class="tab-pane fade" id="users" role="tabpanel">
            <iframe src="{{ route('users.index') }}" width="100%" height="600" frameborder="0"></iframe>
        </div>

        <!-- Setores -->
        <div class="tab-pane fade" id="setores" role="tabpanel">
            <iframe src="{{ route('setores.index') }}" width="100%" height="600" frameborder="0"></iframe>
        </div>

        <!-- Departamentos -->
        <div class="tab-pane fade" id="departamentos" role="tabpanel">
            <iframe src="{{ route('departamentos.index') }}" width="100%" height="600" frameborder="0"></iframe>
        </div>

        <!-- Cargos -->
        <div class="tab-pane fade" id="cargos" role="tabpanel">
            <iframe src="{{ route('cargos.index') }}" width="100%" height="600" frameborder="0"></iframe>
        </div>

        <!-- SLAs -->
        <div class="tab-pane fade" id="slas" role="tabpanel">
            <iframe src="{{ route('slas.index') }}" width="100%" height="600" frameborder="0"></iframe>
        </div>

        <!-- Origens -->
        <div class="tab-pane fade" id="origens" role="tabpanel">
            <iframe src="{{ route('origens.index') }}" width="100%" height="600" frameborder="0"></iframe>
        </div>

        <!-- Tipos -->
        <div class="tab-pane fade" id="tipos" role="tabpanel">
            <iframe src="{{ route('tipos.index') }}" width="100%" height="600" frameborder="0"></iframe>
        </div>

        <!-- Avisos -->
        <div class="tab-pane fade" id="avisos" role="tabpanel">
            <iframe src="{{ route('avisos.index') }}" width="100%" height="600" frameborder="0"></iframe>
        </div>

        <!-- Grupos -->
        <div class="tab-pane fade" id="grupos" role="tabpanel">
            <iframe src="{{ route('grupos.index') }}" width="100%" height="600" frameborder="0"></iframe>
        </div>

        <!-- Destinatários -->
        <div class="tab-pane fade" id="destinatarios" role="tabpanel">
            <iframe src="{{ route('destinatarios.index') }}" width="100%" height="600" frameborder="0"></iframe>
        </div>

        <!-- Estados -->
        <div class="tab-pane fade" id="estados" role="tabpanel">
            <iframe src="{{ route('estados.index') }}" width="100%" height="600" frameborder="0"></iframe>
        </div>

    </div>
</div>

<style>
.bg-purple {
    background-color: #6f42c1 !important;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection
