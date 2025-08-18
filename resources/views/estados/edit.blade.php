<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Estados</title>
</head>
<body>
    <h1>Editar Estado</h1>

    <form action="{{ route('estados.update', $estado->id) }}" method="POST">
        @csrf
        @method('PUT')
        <label for="name">Nome:</label>
        <input type="text" id="name" name="name" value="{{ $estado->name }}" required>
        <button type="submit">Atualizar</button>
    </form>

    <a href="{{ route('estados.index') }}">Voltar</a>
</body>
</html>