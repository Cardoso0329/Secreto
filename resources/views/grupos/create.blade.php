@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Criar Novo Grupo</h2>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('grupos.store') }}" method="POST" class="card p-4 shadow-sm">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nome do Grupo</label>
            <input type="text" class="form-control" id="nome" name="name" required>
        </div>

        <div class="d-flex justify-content-between">
            <a href="{{ route('grupos.index') }}" class="btn btn-secondary">Voltar</a>
            <button type="submit" class="btn btn-primary">Criar Grupo</button>
        </div>
    </form>
</div>
@endsection
