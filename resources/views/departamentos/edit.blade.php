<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar Departamento</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-5">
    <h1 class="mb-4">Editar Departamento</h1>

    {{-- Formulario para atualizar nome --}}
    <form action="{{ route('departamentos.update', $departamento->id) }}" method="POST" class="mb-4">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="name" class="form-label">Nome do Departamento:</label>
            <input type="text" id="name" name="name" class="form-control" value="{{ $departamento->name }}" required>
        </div>

        <h4>Utilizadores associados</h4>

        {{-- Pesquisa --}}
        <input type="text" id="searchUsers" class="form-control mb-3" placeholder="Pesquisar utilizador...">

        <div class="mb-3" id="usersList">
            @foreach($users as $user)
                <div class="form-check user-item">
                    <input type="checkbox"
                           name="users[]"
                           value="{{ $user->id }}"
                           class="form-check-input"
                           id="user-{{ $user->id }}"
                           {{ $departamento->users->contains($user->id) ? 'checked' : '' }}>
                    <label class="form-check-label" for="user-{{ $user->id }}">
                        {{ $user->name }} ({{ $user->email }})
                    </label>
                </div>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary">Atualizar Departamento</button>
        <a href="{{ route('departamentos.index') }}" class="btn btn-secondary ms-2">Voltar</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

{{-- Script para pesquisa em tempo real --}}
<script>
document.getElementById('searchUsers').addEventListener('input', function() {
    let filter = this.value.toLowerCase();
    let users = document.querySelectorAll('#usersList .user-item');

    users.forEach(function(user) {
        let text = user.textContent.toLowerCase();
        if(text.includes(filter)) {
            user.style.display = 'block';
        } else {
            user.style.display = 'none';
        }
    });
});
</script>

</body>
</html>
