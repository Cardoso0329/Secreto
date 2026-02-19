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
                radial-gradient(1200px 600px at 20% 20%, rgba(0,161,155,.15), transparent 60%),
                linear-gradient(180deg, var(--black), var(--deep));
        }

        /* ============================= */
        /* LAYOUT                         */
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

        .brand{
            max-width:560px;
        }

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
            max-width:460px;
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

        .status{
            margin: 0 0 14px;
            padding: 12px 14px;
            border-radius: 14px;
            background: rgba(0,161,155,.10);
            border: 1px solid rgba(0,161,155,.22);
            color: var(--silver);
            font-size: .92rem;
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
            color: var(--silver);
            font-size: .9rem;
            font-weight: 600;
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

        .btn{
            width:100%;
            margin-top:18px;
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
                    <i class="bi bi-key"></i>
                </div>

                <div class="company">Sociedade Comercial C. Santos</div>
                <div class="tagline">Plataforma Interna de Recados</div>

                <div class="headline">Recuperar acesso com segurança</div>
                <div class="desc">
                    Vamos enviar um link de redefinição para o teu email.
                    Se não encontrares, verifica também a pasta de spam.
                </div>

                <div class="tips">
                    <div class="tip">
                        <i class="bi bi-envelope-check"></i>
                        <div>
                            <b>Confirma o email</b>
                            <span>Usa o mesmo email com que entras na plataforma.</span>
                        </div>
                    </div>
                    <div class="tip">
                        <i class="bi bi-shield-check"></i>
                        <div>
                            <b>Link temporário</b>
                            <span>O link tem validade limitada por motivos de segurança.</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- RIGHT --}}
        <section class="right">
            <div class="card">

                <h2 class="title"><i class="bi bi-envelope-paper"></i> Recuperar password</h2>

                <p class="sub">
                    {{ __('Esqueceu sua senha? Não tem problema. Basta nos informar seu endereço de e-mail e enviaremos um e-mail com um link de redefinição de senha que permitirá que você escolha uma nova.') }}
                </p>

                {{-- Session Status (mantém componente, mas dá estilo com wrapper) --}}
                @if (session('status'))
                    <div class="status">
                        <i class="bi bi-check-circle"></i>
                        {{ session('status') }}
                    </div>
                @endif

                {{-- Erros de email --}}
                @if ($errors->get('email'))
                    <div class="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <div>{{ $errors->get('email')[0] }}</div>
                    </div>
                @endif

                {{-- ✅ ROTAS ORIGINAIS (sem mudanças) --}}
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf

                    <div class="field">
                        <label class="label" for="email">{{ __('Email') }}</label>

                        <input
                            id="email"
                            class="input"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            placeholder="name@example.com"
                        >
                    </div>

                    <button class="btn" type="submit">
                        <i class="bi bi-send"></i>
                        {{ __('Link para redefinir a password') }}
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
</x-guest-layout>
