<x-guest-layout>
    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        /* ============================= */
        /* ✅ FULLSCREEN + FIX DO BREEZE */
        /* ============================= */
        html, body { height:100%; margin:0; }

        .min-h-screen{ padding:0 !important; }
        .min-h-screen > div{
            max-width:100% !important;
            width:100% !important;
            padding:0 !important;
            margin:0 !important;
            background:transparent !important;
            box-shadow:none !important;
            border:none !important;
        }
        .bg-white,.shadow-md,.sm\:rounded-lg{
            background:transparent !important;
            box-shadow:none !important;
            border-radius:0 !important;
        }

        /* ============================= */
        /* MERCEDES-STYLE PALETTE        */
        /* ============================= */
        :root{
            --black:#0a0a0a;
            --deep:#111111;
            --silver:#C8C8C8;
            --metal:#8F8F8F;
            --white:#F5F5F5;
            --accent:#00A19B;
            --glass:rgba(255,255,255,0.05);
            --stroke:rgba(255,255,255,0.12);
            --danger:#ff6b6b;
        }

        body{
            font-family:'Segoe UI', sans-serif;
            color:var(--white);
            background:
                radial-gradient(1100px 560px at 20% 20%, rgba(0,161,155,.15), transparent 60%),
                linear-gradient(180deg, var(--black), var(--deep));
        }

        /* ============================= */
        /* LAYOUT                        */
        /* ============================= */
        .page{
            width:100vw;
            height:100vh;
            display:grid;
            grid-template-columns: 1.1fr .9fr;
        }

        @media(max-width:980px){
            .page{ grid-template-columns:1fr; }
            .left{ display:none; }
            .right{ padding:24px; }
        }

        /* ============================= */
        /* LEFT PANEL                     */
        /* ============================= */
        .left{
            display:flex;
            align-items:center;
            justify-content:center;
            padding:70px;
            background:
                radial-gradient(800px 420px at 30% 30%, rgba(0,161,155,.12), transparent 60%),
                linear-gradient(135deg, rgba(255,255,255,.04), rgba(255,255,255,.01));
            border-right:1px solid var(--stroke);
        }

        .brand{ max-width:560px; }

        .logo{
            width:50px;
            height:50px;
            border-radius:50%;
            border:2px solid var(--silver);
            display:flex;
            align-items:center;
            justify-content:center;
            margin-bottom:20px;
            font-size:20px;
            color:var(--silver);
        }

        .company{
            font-size:1.5rem;
            font-weight:700;
            letter-spacing:.5px;
            margin-bottom:8px;
        }

        .tagline{
            color:var(--metal);
            font-size:.95rem;
            margin-bottom:30px;
        }

        .headline{
            font-size:2.1rem;
            font-weight:600;
            margin-bottom:15px;
        }

        .desc{
            color:var(--silver);
            line-height:1.6;
            font-size:1rem;
        }

        .tips{
            margin-top:22px;
            display:grid;
            gap:12px;
        }

        .tip{
            display:flex;
            gap:12px;
            align-items:flex-start;
            padding:12px 14px;
            border-radius:16px;
            background: rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.10);
        }

        .tip i{
            color:var(--accent);
            font-size:18px;
            margin-top:2px;
        }

        .tip b{ display:block; margin-bottom:2px; }
        .tip span{ color:var(--metal); font-size:.92rem; line-height:1.35; }

        /* ============================= */
        /* RIGHT PANEL (FORM)            */
        /* ============================= */
        .right{
            display:flex;
            align-items:center;
            justify-content:center;
            padding:40px;
        }

        .card{
            width:100%;
            max-width:480px;
            background:var(--glass);
            border:1px solid var(--stroke);
            backdrop-filter:blur(12px);
            border-radius:24px;
            padding:40px;
            box-shadow:0 30px 80px rgba(0,0,0,.6);
        }

        .title{
            font-size:1.35rem;
            font-weight:800;
            margin:0 0 8px;
            display:flex;
            align-items:center;
            gap:10px;
        }

        .sub{
            color:var(--metal);
            font-size:.92rem;
            margin:0 0 18px;
            line-height:1.5;
        }

        .alert{
            background:rgba(255,0,0,.08);
            border:1px solid rgba(255,0,0,.30);
            padding:12px 14px;
            border-radius:14px;
            margin-bottom:14px;
            font-size:.92rem;
            color:#ffd0d0;
            display:flex;
            gap:10px;
            align-items:flex-start;
        }

        .field{ margin-top:14px; }

        .label{
            display:block;
            margin-bottom:8px;
            color:var(--silver);
            font-size:.9rem;
            font-weight:600;
        }

        .input{
            width:100%;
            padding:14px;
            border-radius:14px;
            border:1px solid var(--stroke);
            background:rgba(0,0,0,.40);
            color:var(--white);
            outline:none;
            transition:.2s;
        }

        .input:focus{
            border-color:var(--accent);
            box-shadow:0 0 0 3px rgba(0,161,155,.22);
        }

        .password-wrap{ position:relative; }
        .password-wrap .input{ padding-right:50px; }

        .eye-btn{
            position:absolute;
            right:12px;
            top:50%;
            transform:translateY(-50%);
            background:none;
            border:none;
            color:var(--metal);
            cursor:pointer;
            padding:8px;
            border-radius:10px;
            transition:.2s;
        }
        .eye-btn:hover{
            color:var(--accent);
            background:rgba(255,255,255,.06);
        }

        .btn{
            width:100%;
            margin-top:20px;
            padding:14px;
            border-radius:14px;
            border:none;
            font-weight:800;
            letter-spacing:.4px;
            background:linear-gradient(180deg,#E6E6E6,#CFCFCF);
            color:#000;
            cursor:pointer;
            transition:.2s;
            display:flex;
            align-items:center;
            justify-content:center;
            gap:10px;
        }

        .btn:hover{
            transform:translateY(-2px);
            box-shadow:0 10px 30px rgba(0,0,0,.6);
        }

        .back{
            display:inline-flex;
            align-items:center;
            gap:8px;
            margin-top:16px;
            color:var(--silver);
            text-decoration:underline;
            text-underline-offset:3px;
            font-size:.9rem;
        }
        .back:hover{ color:#fff; }

        .footer-note{
            margin-top:18px;
            text-align:center;
            font-size:.8rem;
            color:var(--metal);
        }
    </style>

    <div class="page">

        {{-- LEFT --}}
        <section class="left">
            <div class="brand">
                <div class="logo">
                    <i class="bi bi-shield-check"></i>
                </div>

                <div class="company">Sociedade Comercial C. Santos</div>
                <div class="tagline">Plataforma Interna de Recados</div>

                <div class="headline">Defina uma nova password</div>
                <div class="desc">
                    Crie uma password forte para manter a sua conta segura.
                    Após redefinir, poderá entrar novamente na plataforma.
                </div>

                <div class="tips">
                    <div class="tip">
                        <i class="bi bi-key"></i>
                        <div>
                            <b>Password forte</b>
                            <span>Use letras, números e um símbolo (ex.: ! @ #).</span>
                        </div>
                    </div>
                    <div class="tip">
                        <i class="bi bi-shield-lock"></i>
                        <div>
                            <b>Segurança</b>
                            <span>Evite reutilizar passwords de outros serviços.</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- RIGHT --}}
        <section class="right">
            <div class="card">

                <h2 class="title"><i class="bi bi-arrow-repeat"></i> Redefinir password</h2>
                <p class="sub">Confirme o email e defina a nova password.</p>

                {{-- Mensagens de erro --}}
                @if ($errors->any())
                    <div class="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <div>{{ $errors->first() }}</div>
                    </div>
                @endif

                {{-- ✅ ROTA ORIGINAL MANTIDA --}}
                <form method="POST" action="{{ route('password.store') }}">
                    @csrf

                    {{-- Password Reset Token --}}
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    {{-- Email --}}
                    <div class="field">
                        <label class="label" for="email">{{ __('Email') }}</label>
                        <input
                            id="email"
                            class="input"
                            type="email"
                            name="email"
                            value="{{ old('email', $request->email) }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="name@example.com"
                        >
                        @if($errors->get('email'))
                            <div style="margin-top:8px; color:#ffd0d0; font-size:.9rem;">
                                {{ $errors->get('email')[0] }}
                            </div>
                        @endif
                    </div>

                    {{-- Password --}}
                    <div class="field password-wrap">
                        <label class="label" for="password">{{ __('Password') }}</label>
                        <input
                            id="password"
                            class="input"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            placeholder="Nova password"
                        >
                        <button type="button" class="eye-btn" onclick="togglePw('password','eye1')" aria-label="Mostrar/esconder password">
                            <i id="eye1" class="bi bi-eye"></i>
                        </button>
                        @if($errors->get('password'))
                            <div style="margin-top:8px; color:#ffd0d0; font-size:.9rem;">
                                {{ $errors->get('password')[0] }}
                            </div>
                        @endif
                    </div>

                    {{-- Confirm Password --}}
                    <div class="field password-wrap">
                        <label class="label" for="password_confirmation">{{ __('Confirm Password') }}</label>
                        <input
                            id="password_confirmation"
                            class="input"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            placeholder="Confirmar password"
                        >
                        <button type="button" class="eye-btn" onclick="togglePw('password_confirmation','eye2')" aria-label="Mostrar/esconder confirmação">
                            <i id="eye2" class="bi bi-eye"></i>
                        </button>
                        @if($errors->get('password_confirmation'))
                            <div style="margin-top:8px; color:#ffd0d0; font-size:.9rem;">
                                {{ $errors->get('password_confirmation')[0] }}
                            </div>
                        @endif
                    </div>

                    <button class="btn" type="submit">
                        <i class="bi bi-check2-circle"></i>
                        {{ __('Redefinir Password') }}
                    </button>

                    <a class="back" href="{{ route('login') }}">
                        <i class="bi bi-arrow-left"></i> Voltar ao login
                    </a>

                    <div class="footer-note">
                        Sistema interno · Acesso restrito
                    </div>
                </form>
            </div>
        </section>
    </div>

    <script>
        function togglePw(inputId, iconId){
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);

            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';

            icon.classList.toggle('bi-eye', showing);
            icon.classList.toggle('bi-eye-slash', !showing);
        }
    </script>
</x-guest-layout>
