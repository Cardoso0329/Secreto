<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Painel de Controlo</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body, html {
      height: 100vh;
      margin: 0;
      overflow: hidden;
      font-family: 'Segoe UI', sans-serif;
    }

    .sidebar {
      min-height: 100vh;
      background-color: #212529;
      color: white;
      box-shadow: 2px 0 5px rgba(0,0,0,0.1);
      overflow-y: auto;
    }

    .sidebar a,
    .sidebar button {
      color: white;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 10px 15px;
      border: none;
      background: none;
      width: 100%;
      text-align: left;
      cursor: pointer;
      transition: background-color 0.2s ease-in-out;
    }

    .sidebar a:hover,
    .sidebar button:hover {
      background-color: #343a40;
    }

    .dropdown-container {
      display: none;
      flex-direction: column;
      animation: fadeIn 0.3s ease-in-out;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-5px); }
      to { opacity: 1; transform: translateY(0); }
    }

    main.content {
      height: 100vh;
    }

    iframe#iframeMain {
      width: 100%;
      height: 100vh;
      border: none;
    }

    .btn-logout {
      margin-top: auto;
      background-color: transparent;
      border: none;
      color: #adb5bd;
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 10px 15px;
    }

    .btn-logout:hover {
      background-color: #343a40;
      color: #fff;
    }

    .sidebar h4 {
      font-weight: bold;
      margin-bottom: 1.5rem;
    }
  </style>
</head>
<body>
  <div class="container-fluid">
    <div class="row g-0">
      <!-- Sidebar -->
      <nav class="col-md-3 col-lg-2 sidebar d-flex flex-column p-3">
        <h4 class="text-center">📋 Painel</h4>

        <div class="flex-grow-1">
          <a href="/recados" onclick="event.preventDefault(); document.getElementById('iframeMain').src='/recados';">
            <i class="bi bi-chat-left-dots"></i> Recados
          </a>
        </div>

        @if(Auth::check() && Auth::user()->cargo_id === 1)
  <a href="/configuracoes" onclick="event.preventDefault(); document.getElementById('iframeMain').src='/configuracoes';">
    <i class="bi bi-gear"></i> Configurações
  </a>
@endif


        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="btn-logout">
            <i class="bi bi-box-arrow-left"></i> Sair
          </button>
        </form>
      </nav>

      <!-- Conteúdo principal -->
      <main class="col-md-9 col-lg-10 content p-0">
        <iframe id="iframeMain" src="/recados"></iframe>
      </main>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.querySelector('.dropdown-btn')?.addEventListener('click', function () {
      const container = document.querySelector('.dropdown-container');
      container.style.display = container.style.display === 'flex' ? 'none' : 'flex';
    });
  </script>
</body>
</html>
