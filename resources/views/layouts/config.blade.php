<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Controlo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            width: 220px;
            min-height: 100vh;
            background-color: #212529;
        }
        .sidebar a {
            color: #ddd;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
        }
        .sidebar a:hover {
            background-color: #343a40;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        {{-- Sidebar --}}
        <div class="sidebar p-3">
            <h5 class="text-white mb-4">Painel</h5>
            <a href="{{ route('recados.index') }}"><i class="bi bi-chat-left-text"></i> Recados</a>
            <a href="{{ route('painel.index') }}"><i class="bi bi-gear"></i> Configurações</a>
            <a href="{{ route('logout') }}"><i class="bi bi-box-arrow-right"></i> Sair</a>
        </div>

        {{-- Conteúdo --}}
        <div class="flex-grow-1 p-4">
            @yield('content')
        </div>
    </div>
</body>
</html>
