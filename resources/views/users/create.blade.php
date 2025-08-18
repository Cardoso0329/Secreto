@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh; background-color: #f8f9fa;">
    <div class="card shadow-sm w-100" style="max-width: 720px; border-radius: 1rem;">
        <div class="card-body p-4">
            <h3 class="text-center mb-4" style="font-weight: 600; color: #343a40;">Criar Novo Utilizador</h3>

            <form method="POST" action="{{ route('users.store') }}">
                @csrf

                {{-- Nome --}}
                <div class="mb-3">
                    <label for="name" class="form-label">Nome</label>
                    <input type="text" name="name" id="name" class="form-control rounded-pill" required>
                </div>

                {{-- Email --}}
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control rounded-pill" required>
                </div>


                {{-- Cargo --}}
                <div class="mb-3">
                    <label for="cargo_id" class="form-label">Cargo</label>
                    <select name="cargo_id" id="cargo_id" class="form-select rounded-pill" required>
                        <option value="">Selecione um Cargo</option>
                        @foreach ($cargos as $cargo)
                            <option value="{{ $cargo->id }}">{{ $cargo->name }}</option>
                        @endforeach
                    </select>
                </div>


                {{-- Password --}}
                <div class="mb-3">
                    <label for="password" class="form-label">Palavra-passe</label>
                    <input type="password" name="password" id="password" class="form-control rounded-pill" required>
                </div>

                {{-- Confirmar Password --}}
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Confirmar Palavra-passe</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control rounded-pill" required>
                </div>

                {{-- Botões --}}
                <div class="d-flex justify-content-between mt-4">
                    <button type="submit" class="btn btn-dark rounded-pill px-4">Criar</button>
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary rounded-pill px-4">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
