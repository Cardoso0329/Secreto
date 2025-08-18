@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Detalhes do Grupo</h2>

    <div class="card shadow-sm p-4">
        <p><strong>ID:</strong> {{ $grupo->id }}</p>
        <p><strong>Nome:</strong> {{ $grupo->name }}</p>
        <p><strong>Criado em:</strong> {{ $grupo->created_at->format('d/m/Y H:i') }}</p>
    </div>

    <div class="mt-3">
        <a href="{{ route('grupos.index') }}" class="btn btn-secondary">Voltar</a>
        <a href="{{ route('grupos.edit', $grupo) }}" class="btn btn-warning">Editar</a>
    </div>
</div>
@endsection
