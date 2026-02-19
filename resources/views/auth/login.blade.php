<x-guest-layout>
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<style>
/* ============================= */
/* FULLSCREEN FIX (BREEZE)       */
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
/* MERCEDES COLOR PALETTE        */
/* ============================= */

:root{
    --black:#0a0a0a;
    --deep-black:#111111;
    --silver:#C8C8C8;
    --metal:#8F8F8F;
    --white:#F5F5F5;
    --accent:#00A19B; /* Mercedes teal accent */
    --glass:rgba(255,255,255,0.05);
    --stroke:rgba(255,255,255,0.12);
}

body{
    font-family:'Segoe UI', sans-serif;
    color:var(--white);
    background:
        radial-gradient(1200px 600px at 20% 20%, rgba(0,161,155,.15), transparent 60%),
        linear-gradient(180deg, var(--black), var(--deep-black));
}

/* ============================= */
/* LAYOUT                        */
/* ============================= */

.page{
    width:100vw;
    height:100vh;
    display:grid;
    grid-template-columns:1.1fr .9fr;
}

@media(max-width:980px){
    .page{grid-template-columns:1fr;}
    .left{display:none;}
}

/* ============================= */
/* LEFT PANEL                    */
/* ============================= */

.left{
    display:flex;
    align-items:center;
    justify-content:center;
    padding:70px;
    background:
        radial-gradient(800px 400px at 30% 30%, rgba(0,161,155,.12), transparent 60%),
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
    font-size:2.2rem;
    font-weight:600;
    margin-bottom:15px;
}

.desc{
    color:var(--silver);
    line-height:1.6;
    font-size:1rem;
}

/* ============================= */
/* RIGHT PANEL (LOGIN)           */
/* ============================= */

.right{
    display:flex;
    align-items:center;
    justify-content:center;
    padding:40px;
}

.card{
    width:100%;
    max-width:440px;
    background:var(--glass);
    border:1px solid var(--stroke);
    backdrop-filter:blur(12px);
    border-radius:24px;
    padding:40px;
    box-shadow:0 30px 80px rgba(0,0,0,.6);
}

.title{
    font-size:1.4rem;
    font-weight:700;
    margin-bottom:8px;
}

.sub{
    color:var(--metal);
    font-size:.9rem;
    margin-bottom:25px;
}

.alert{
    background:rgba(255,0,0,.08);
    border:1px solid rgba(255,0,0,.3);
    padding:12px;
    border-radius:12px;
    margin-bottom:15px;
    font-size:.9rem;
}

.field{margin-bottom:18px;}

.input{
    width:100%;
    padding:14px;
    border-radius:14px;
    border:1px solid var(--stroke);
    background:rgba(0,0,0,.4);
    color:var(--white);
    outline:none;
    transition:.2s;
}

.input:focus{
    border-color:var(--accent);
    box-shadow:0 0 0 3px rgba(0,161,155,.25);
}

.password-wrap{position:relative;}
.password-wrap .input{padding-right:50px;}

.eye-btn{
    position:absolute;
    right:12px;
    top:50%;
    transform:translateY(-50%);
    background:none;
    border:none;
    color:var(--metal);
    cursor:pointer;
}
.eye-btn:hover{color:var(--accent);}

.row{
    display:flex;
    justify-content:space-between;
    align-items:center;
    font-size:.85rem;
    margin-bottom:15px;
}

.remember input{accent-color:var(--accent);}

.link{
    color:var(--silver);
    text-decoration:underline;
}
.link:hover{color:white;}

.btn{
    width:100%;
    padding:14px;
    border-radius:14px;
    border:none;
    font-weight:700;
    letter-spacing:.5px;
    background:linear-gradient(180deg,#E6E6E6,#CFCFCF);
    color:#000;
    cursor:pointer;
    transition:.2s;
}

.btn:hover{
    transform:translateY(-2px);
    box-shadow:0 10px 30px rgba(0,0,0,.6);
}

.footer-note{
    margin-top:18px;
    text-align:center;
    font-size:.8rem;
    color:var(--metal);
}
</style>

<div class="page">

    <!-- LEFT -->
    <section class="left">
        <div class="brand">
            <div class="logo">
                <i class="bi bi-envelope-paper"></i>
            </div>

            <div class="company">Sociedade Comercial C. Santos</div>
            <div class="tagline">Plataforma Interna de Recados</div>

            <div class="headline">Comunicação clara. Execução precisa.</div>
            <div class="desc">
                Gere, encaminha e acompanha recados com elegância e eficiência.
                Histórico completo, controlo por perfil e rastreabilidade total.
            </div>
        </div>
    </section>

    <!-- RIGHT -->
    <section class="right">
        <div class="card">

            <div class="title">Iniciar sessão</div>
            <div class="sub">Acede à plataforma de recados</div>

            @if ($errors->any())
                <div class="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field">
                    <input type="email"
                           name="email"
                           value="{{ old('email') }}"
                           class="input"
                           required
                           placeholder="Email">
                </div>

                <div class="field password-wrap">
                    <input id="password"
                           type="password"
                           name="password"
                           class="input"
                           required
                           placeholder="Password">

                    <button type="button" class="eye-btn" onclick="togglePassword()">
                        <i id="eyeIcon" class="bi bi-eye"></i>
                    </button>
                </div>

                <div class="row">
                    <label class="remember">
                        <input type="checkbox" name="remember">
                        Lembrar-me
                    </label>

                    @if (Route::has('password.request'))
                        <a class="link" href="{{ route('password.request') }}">
                            Esqueceste-te da password?
                        </a>
                    @endif
                </div>

                <button type="submit" class="btn">
                    Entrar
                </button>

                <div class="footer-note">
                    Sistema interno · Acesso restrito
                </div>

            </form>
        </div>
    </section>
</div>

<script>
function togglePassword(){
    const password = document.getElementById('password');
    const icon = document.getElementById('eyeIcon');

    if(password.type === 'password'){
        password.type = 'text';
        icon.classList.replace('bi-eye','bi-eye-slash');
    }else{
        password.type = 'password';
        icon.classList.replace('bi-eye-slash','bi-eye');
    }
}
</script>
</x-guest-layout>
