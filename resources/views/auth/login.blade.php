<x-guest-layout>
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root{
            --bg:#1C1C1C;
            --card:#2A2A2A;
            --input:#3A3A3A;
            --border:#A5A5A5;
            --text:#F8F8F8;
            --muted:#C0C0C0;
            --accent:#009EDB;
        }

        body{
            background:var(--bg);
            color:var(--text);
            font-family:'Segoe UI', sans-serif;
        }

        .form-container{
            background:var(--card);
            padding:2rem;
            border-radius:12px;
            box-shadow:0 0 12px rgba(255,255,255,.05);
            max-width:500px;
            margin:3rem auto;
        }

        .field{
            margin-bottom:1rem;
        }

        .label{
            display:block;
            margin-bottom:.35rem;
            font-weight:600;
            color:var(--text);
        }

        .input{
            width:100%;
            background:var(--input);
            border:1px solid var(--border);
            color:var(--text);
            padding:.55rem .75rem;
            border-radius:8px;
            transition:all .2s ease;
        }

        .input:focus{
            border-color:var(--accent);
            box-shadow:0 0 0 3px rgba(0,158,219,.20);
            outline:none;
        }

        .password-wrap{
            position:relative;
        }

        .password-wrap .input{
            padding-right:44px; /* espaço para o olho */
        }

        .eye-btn{
            position:absolute;
            right:12px;
            top:50%;
            transform:translateY(-50%);
            background:transparent;
            border:0;
            padding:0;
            cursor:pointer;
            color:var(--muted);
            font-size:18px;
            line-height:1;
        }

        .eye-btn:hover{
            color:var(--accent);
        }

        .remember{
            display:flex;
            align-items:center;
            gap:.55rem;
            margin-top:.25rem;
        }

        .remember input[type="checkbox"]{
            accent-color:var(--accent);
            width:16px;
            height:16px;
        }

        .form-footer{
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:1rem;
            margin-top:1.75rem;
        }

        .link{
            color:var(--muted);
            text-decoration:underline;
            text-underline-offset:3px;
            transition:color .2s ease;
            font-size:.92rem;
        }

        .link:hover{
            color:#fff;
        }

        .btn-mercedes{
            background:var(--border);
            color:#000;
            padding:.55rem 1.25rem;
            border-radius:8px;
            font-weight:600;
            border:none;
            transition:background-color .2s ease;
            white-space:nowrap;
        }

        .btn-mercedes:hover{
            background:#DADADA;
        }

        .alert{
            padding:12px;
            border-radius:8px;
            margin-bottom:1.1rem;
            text-align:center;
            font-size:.95rem;
        }

        .alert-auth{
            background:#3a1d1d;
            border:1px solid #ff6b6b;
            color:#ffb3b3;
        }

        .alert-login{
            background:#2b2413;
            border:1px solid #f7c948;
            color:#ffe8a3;
        }
    </style>

    <div class="form-container">

        {{-- 🔒 Mensagem de acesso restrito --}}
        @if (session('error'))
            <div class="alert alert-auth">
                🔒 <strong>Acesso restrito</strong><br>
                {{ session('error') }}
            </div>
        @endif

        {{-- ⚠️ Erro de login --}}
        @if ($errors->any())
            <div class="alert alert-login">
                ⚠️ <strong>Não foi possível iniciar sessão</strong><br>
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <div class="field">
                <label for="email" class="label">Email</label>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    class="input"
                >
            </div>

            {{-- Password --}}
            <div class="field">
                <label for="password" class="label">Password</label>

                <div class="password-wrap">
                    <input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        class="input"
                    >

                    <button type="button" class="eye-btn" onclick="togglePassword()" aria-label="Mostrar/esconder password">
                        <i id="eyeIcon" class="bi bi-eye"></i>
                    </button>
                </div>
            </div>

            {{-- Remember --}}
            <div class="remember">
                <input type="checkbox" name="remember" id="remember_me">
                <label for="remember_me" class="label" style="margin:0; font-weight:500;">Lembrar-me</label>
            </div>

            {{-- Footer --}}
            <div class="form-footer">
                @if (Route::has('password.request'))
                    <a class="link" href="{{ route('password.request') }}">
                        Esqueceste-te da password?
                    </a>
                @endif

                <button type="submit" class="btn-mercedes">
                    Login
                </button>
            </div>
        </form>
    </div>

    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');

            const showing = password.type === 'text';
            password.type = showing ? 'password' : 'text';

            icon.classList.toggle('bi-eye', showing);
            icon.classList.toggle('bi-eye-slash', !showing);
        }
    </script>
</x-guest-layout>
