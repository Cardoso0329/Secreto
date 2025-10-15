<nav class="navbar navbar-expand-lg" style="background-color: #212529;">
  <div class="container py-2">

    <button class="navbar-toggler text-white" type="button" data-bs-toggle="collapse"
      data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent"
      aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <!-- Outros links, se precisares -->
      </ul>

      <ul class="navbar-nav ms-auto">
        @auth
          <li class="nav-item">
            <a class="nav-link px-4 py-2 rounded-pill text-white fw-semibold" ">
              {{ Auth::user()->name }}
            </a>
          </li>
        @endauth
      </ul>
    </div>
  </div>
</nav>
