<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Avisos</title>
</head>
<body>
    <h1>Editar Aviso</h1>

    <form action="{{ route('avisos.update', $aviso->id) }}" method="POST">
        @csrf
        @method('PUT')
        <label for="name">Nome:</label>
        <input type="text" id="name" name="name" value="{{ $aviso->name }}" required>
        <button type="submit">Atualizar</button>
    </form>

    <a href="{{ route('avisos.index') }}">Voltar</a>
</body>
</html>