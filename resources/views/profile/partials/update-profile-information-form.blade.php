<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    <div class="mb-3">
        <label for="name" class="form-label">Nome</label>
        <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $user->name) }}" required autocomplete="name" autofocus>
        @error('name')
            <div class="text-danger mt-1">{{ $message }}</div>
        @enderror
    </div>

    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" id="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required autocomplete="username">
        @error('email')
            <div class="text-danger mt-1">{{ $message }}</div>
        @enderror

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="alert alert-warning mt-2">
                O seu email não está verificado. 
                <button form="send-verification" class="btn btn-link p-0 align-baseline">Clique aqui para reenviar o email de verificação.</button>
            </div>

            @if (session('status') === 'verification-link-sent')
                <div class="alert alert-success mt-2">
                    Um novo link de verificação foi enviado para o seu email.
                </div>
            @endif
        @endif
    </div>

    <button type="submit" class="btn btn-primary">Guardar</button>

    @if (session('status') === 'profile-updated')
        <span class="text-success ms-3">Guardado com sucesso.</span>
    @endif
</form>
