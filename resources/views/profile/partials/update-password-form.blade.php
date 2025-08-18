<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')

    <div class="mb-3">
        <label for="current_password" class="form-label">Palavra-passe atual</label>
        <input id="current_password" name="current_password" type="password" class="form-control" required autocomplete="current-password">
        @error('current_password')
            <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password" class="form-label">Nova palavra-passe</label>
        <input id="password" name="password" type="password" class="form-control" required autocomplete="new-password">
        @error('password')
            <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="password_confirmation" class="form-label">Confirmar nova palavra-passe</label>
        <input id="password_confirmation" name="password_confirmation" type="password" class="form-control" required autocomplete="new-password">
    </div>

    <button type="submit" class="btn btn-primary">Alterar Palavra-passe</button>

    @if (session('status') === 'password-updated')
        <span class="text-success ms-3">Atualizada com sucesso.</span>
    @endif
</form>
