<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Bem-vindo à Mercedes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #000, #444);
            color: #fff;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .navbar-brand img {
            height: 40px;
        }
        .welcome-section {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            text-align: center;
            padding: 2rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark px-4">
        <a class="navbar-brand" href="#">
            <img src="https://upload.wikimedia.org/wikipedia/commons/9/90/Mercedes-Logo.svg" alt="Mercedes Logo">
            Mercedes-Benz
        </a>
    </nav>

    <div class="welcome-section">
        <div>
            <h1 class="display-4">Bem-vindo à Sociedade Comercial C. Santos</h1>
            <p class="lead mt-3">A excelência em movimento.</p>
            @guest
                <a href="{{ route('login') }}" class="btn btn-light btn-lg mt-4">Entrar na plataforma</a>
            @endguest
        </div>
    </div>

    <footer class="bg-dark text-center py-3">
        <small>&copy; {{ date('Y') }} Mercedes-Benz Portugal. Todos os direitos reservados.</small>
    </footer>
</body>
</html>
