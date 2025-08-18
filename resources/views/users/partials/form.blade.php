<div class="mb-3">
    <label for="name" class="form-label">Nome</label>
    <input type="text" class="form-control @error('name') is-invalid @enderror" name="name"
           value="{{ old('name', $user->name ?? '') }}" required>
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="email" class="form-label">Email</label>
    <input type="email" class="form-control @error('email') is-invalid @enderror" name="email"
           value="{{ old('email', $user->email ?? '') }}" required>
    @error('email')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="mb-3">
    <label for="cargo_id" class="form-label">Cargo</label>
    <select name="cargo_id" id="cargo_id" class="form-select @error('cargo_id') is-invalid @enderror" required>
        <option value="">-- Selecione um cargo --</option>
        @foreach ($cargos as $cargo)
            <option value="{{ $cargo->id }}" {{ old('cargo_id', $user->cargo_id ?? '') == $cargo->id ? 'selected' : '' }}>
                {{ $cargo->name }}
            </option>
        @endforeach
    </select>
    @error('cargo_id')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>






{{-- Campo senha --}}

<div class="mb-3">
    <label for="password" class="form-label">Password</label>
    <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" 
           {{ isset($user) ? '' : 'required' }}>
    @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

{{-- Campo confirmação de senha --}}

<div class="mb-3">
    <label for="password_confirmation" class="form-label">Confirmar Password</label>
    <input type="password" class="form-control" name="password_confirmation" 
           {{ isset($user) ? '' : 'required' }}>
</div>
