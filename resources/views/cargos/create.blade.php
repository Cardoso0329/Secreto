@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh; background-color: #f8f9fa;">
    <div class="card shadow-sm w-100" style="max-width: 600px; border-radius: 1rem;">
        <div class="card-body p-4">
            <h3 class="text-center mb-4" style="font-weight: 600; color: #343a40;">Criar Novo Cargo</h3>

            <form action="{{ route('cargos.store') }}" method="POST">
                @csrf

                <div class="mb-3">
                    <label for="name" class="form-label">Nome do Cargo</label>
                    <input type="text" name="name" id="name"
                           class="form-control rounded-pill @error('name') is-invalid @enderror"
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-between mt-4">
                    <button class="btn btn-dark rounded-pill px-4" type="submit">Guardar</button>
                    <a href="{{ route('cargos.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
