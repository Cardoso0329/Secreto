<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recados de Campanhas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Recados de Campanhas do Meu Departamento</h1>

    @if($recados->count())
        <table class="table table-bordered table-striped mt-4">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Campanhas</th>
                    <th>Mensagem</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recados as $recado)
                    <tr>
                        <td>{{ $recado->id }}</td>
                        <td>{{ $recado->name }}</td>
                       <td>
    @if($recado->campanha)
        <span class="badge bg-primary">{{ $recado->campanha->name }}</span>
    @endif
</td>

                        <td>{{ $recado->mensagem }}</td>
                        <td>
                            <a href="{{ route('recados.show', $recado->id) }}" class="btn btn-sm btn-info">Ver</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Nenhum recado de campanha encontrado para o seu departamento.</p>
    @endif
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
