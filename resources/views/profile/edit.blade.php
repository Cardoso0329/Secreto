<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Perfil do Utilizador</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Alpine.js -->
    <script src="//unpkg.com/alpinejs" defer></script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
        }

        h2.section-title {
            margin-bottom: 1rem;
            font-weight: 600;
            color: #343a40;
        }

        [x-cloak] {
            display: none !important;
        }
    </style>
</head>
<body>
    <div class="container-lg py-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <h1 class="mb-4 text-center">Perfil do Utilizador</h1>

                <!-- Informações do Perfil -->
                <div class="card mb-4 p-4 border-secondary">
                    <h2 class="section-title">Informações do Perfil</h2>
                    @include('profile.partials.update-profile-information-form')
                </div>

                <!-- Alterar Palavra-passe -->
                <div class="card mb-4 p-4 border-secondary">
                    <h2 class="section-title">Alterar Palavra-passe</h2>
                    @include('profile.partials.update-password-form')
                </div>

            </div>
        </div>
    </div>
</body>
</html>
