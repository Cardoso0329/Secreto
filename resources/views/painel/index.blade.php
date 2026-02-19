<!DOCTYPE html>
<html lang="pt">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Painel de Controlo</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

  <style>
    :root{
      --mercedes-black:#0f0f10;
      --mercedes-dark:#1a1a1d;
      --mercedes-gray:#2b2b30;
      --mercedes-silver:#c9c9c9;
      --mercedes-accent:#00a3e0;
    }

    body, html {
      height: 100vh;
      margin: 0;
      overflow: hidden;
      font-family: 'Segoe UI', sans-serif;
      background: var(--mercedes-black);
    }

    .sidebar {
      min-height: 100vh;
      background: linear-gradient(180deg, var(--mercedes-dark), var(--mercedes-black));
      color: var(--mercedes-silver);
      box-shadow: 4px 0 20px rgba(0,0,0,0.4);
      display: flex;
      flex-direction: column;
      padding: 20px 15px;
    }

    .sidebar h4 {
      font-weight: 600;
      letter-spacing: .5px;
      margin-bottom: 2rem;
      color: white;
      text-align: center;
    }

    .sidebar a,
    .sidebar button {
      color: var(--mercedes-silver);
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 15px;
      border: none;
      background: none;
      width: 100%;
      text-align: left;
      cursor: pointer;
      border-radius: 8px;
      transition: all .2s ease;
      font-size: 14px;
    }

    .sidebar a:hover,
    .sidebar button:hover {
      background: var(--mercedes-gray);
      color: white;
      transform: translateX(4px);
    }

    .sidebar a.active {
      background: var(--mercedes-accent);
      color: white;
    }

    .sidebar i {
      font-size: 16px;
    }

    main.content {
      height: 100vh;
      background: #f5f6f7;
      display: flex;
      flex-direction: column;
    }

    .topbar {
      height: 60px;
      background: white;
      border-bottom: 1px solid #e5e5e5;
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 25px;
      font-size: 14px;
    }

    .topbar .user-info {
      font-weight: 500;
      color: #333;
    }

    iframe#iframeMain {
      width: 100%;
      flex: 1;
      border: none;
      background: white;
    }

    .btn-logout {
      background: none;
      border: none;
      color: #9aa0a6;
      padding: 12px 15px;
      border-radius: 8px;
      transition: all .2s ease;
    }

    .btn-logout:hover {
      background: var(--mercedes-gray);
      color: white;
    }

    .bottom-links {
      margin-top: auto;
    }

    @media (max-width: 768px){
      .sidebar{
        position: fixed;
        z-index: 999;
        width: 220px;
      }
    }
  </style>
</head>

<body>
  <div class="container-fluid">
    <div class="row g-0">

      <!-- Sidebar -->
      <nav class="col-md-3 col-lg-2 sidebar">

        <h4>⚙ Sistema de Recados</h4>

        <a href="#" class="nav-link-custom"
           onclick="loadPage('/recados', this)">
          <i class="bi bi-chat-left-dots"></i> Recados
        </a>

        <div class="bottom-links">

          @if(Auth::check() && Auth::user()->cargo_id === 1)

            <a href="#" onclick="loadPage('/configuracoes', this)">
              <i class="bi bi-gear"></i> Configurações
            </a>

            <a href="#" onclick="loadPage('/email-logs', this)">
              <i class="bi bi-envelope-paper"></i> Logs de Emails
            </a>

            <a href="#" onclick="loadPage('/admin/audit-logs', this)">
              <i class="bi bi-shield-check"></i> Audit Logs
            </a>

          @endif

          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn-logout w-100 text-start">
              <i class="bi bi-box-arrow-left"></i> Sair
            </button>
          </form>

        </div>
      </nav>

      <!-- Conteúdo -->
      <main class="col-md-9 col-lg-10 content p-0">

        <!-- Topbar -->

        <iframe id="iframeMain" src="/recados"></iframe>

      </main>

    </div>
  </div>

  <script>
    function loadPage(url, element) {
      document.getElementById('iframeMain').src = url;

      document.querySelectorAll('.sidebar a').forEach(link => {
        link.classList.remove('active');
      });

      if(element){
        element.classList.add('active');
      }
    }
  </script>

</body>
</html>
