<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Aviso de Recado</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <h2>📢 Novo Aviso sobre o Recado #{{ $recado->id }}</h2>

    <p><strong>Nome:</strong> {{ $recado->name }}</p>
    <p><strong>Mensagem:</strong> {{ $recado->mensagem }}</p>
    <p><strong>Aviso:</strong> {{ $recado->aviso->name ?? '—' }}</p>

    <p>Podes consultar o recado aqui:</p>
    <p>
        <a href="{{ url('/recados/' . $recado->id) }}" style="color: #007bff; text-decoration: none;">
            Ver Recado
        </a>
    </p>

    <hr>
    <small>Mensagem automática — por favor, não responder a este email.</small>
</body>
</html>
