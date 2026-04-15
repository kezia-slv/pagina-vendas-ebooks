<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DatisLopo — Autenticação</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
    /* ══════════════════════════════════════════════
       TOKENS — espelho do projeto
    ══════════════════════════════════════════════ */
    :root {
        --brand:      #e8a838;
        --brand-dk:   #a07020;
        --brand-deep: #402d0d;
        --sid:        #0f0e0b;
        --sid2:       #1a1916;
        --bg:         #0f0e0b;
        --card:       #1a1916;
        --border:     rgba(232, 168, 56, 0.15);
        --txt:        #f0ead8;
        --txt2:       #c2bba8;
        --txt3:       #8a8370;
        --red:        #DC2626;
        --red-bg:     #321010;
        --r:          12px;

        /* ─── Proporções do card ───
           Painel de formulário: 58%
           Painel da marca:      42%
           Para mover a marca ao lado direito:
           translateX = (58 / 42) × 100 ≈ 138.1%
        ─────────────────────────────────────── */
        --form-pct:   58%;
        --brand-pct:  42%;
        --brand-to-right: 138.1%; /* valor para login-mode */
        --slide-dur:  0.72s;
        --slide-ease: cubic-bezier(0.76, 0, 0.24, 1);
    }

    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html, body { height: 100%; font-family: 'Sora', sans-serif; }
    body {
        background: var(--bg);
        background-image: radial-gradient(rgba(232,168,56,.055) 1px, transparent 1px);
        background-size: 30px 30px;
        display: flex; align-items: center; justify-content: center;
        min-height: 100vh; overflow: hidden;
    }
    a { text-decoration: none; color: inherit; }

    /* ══════════════════════════════════════════════
       CARD  —  o container principal
    ══════════════════════════════════════════════ */
    .auth-card {
        width: min(900px, 96vw);
        height: min(520px, 90vh);
        border-radius: 24px;
        box-shadow: 0 28px 80px rgba(0,0,0,.13), 0 4px 16px rgba(0,0,0,.06);
        overflow: hidden;
        position: relative;
        background: var(--card);
        /* Entrda inicial: escala de 0.92 */
        animation: cardEnter .55s cubic-bezier(.22,1,.36,1) both;
    }
    @keyframes cardEnter {
        from { opacity:0; transform: scale(.92) translateY(12px); }
        to   { opacity:1; transform: scale(1)   translateY(0); }
    }

    /* ══════════════════════════════════════════════
       PAINÉIS DE FORMULÁRIO
       Ambos estão sempre no DOM.
       Ficam nas extremidades opostas do card.
    ══════════════════════════════════════════════ */
    .fp {
        position: absolute;
        top: 0; bottom: 0;
        width: var(--form-pct);
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        padding: 40px 52px;
        background: var(--card);
        z-index: 1;
        overflow-y: auto;
        /* sem scrollbar visível */
        scrollbar-width: none;
    }
    .fp::-webkit-scrollbar { display: none; }

    /* Login → lado esquerdo */
    .fp-login    { left: 0; }
    /* Registro → lado direito */
    .fp-register { right: 0; }

    /* ── Formulário inativo: invisível, sem cliques ──
       NÃO usa visibility (herdaria para inputs e bloquearia digitação).
       Apenas opacity + pointer-events.
    ── */
    .fp { transition: opacity .22s ease; }

    /* Inativo → some imediatamente */
    .mode-login    .fp-register,
    .mode-register .fp-login {
        opacity: 0;
        pointer-events: none;
        transition: opacity .18s ease 0s;
    }

    /* Ativo → aparece com leve fade após o painel ter deslizado */
    .mode-login    .fp-login,
    .mode-register .fp-register {
        opacity: 1;
        pointer-events: all;
        transition: opacity .3s ease .34s;
    }

    /* ── Logo badge ── */
    .form-badge {
        width: 52px; height: 52px; flex-shrink: 0;
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dk) 100%);
        border-radius: 15px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
        margin-bottom: 20px;
        box-shadow: 0 6px 20px rgba(232,168,56,.32);
    }

    .form-title {
        font-size: 1.6rem; font-weight: 800; color: var(--txt);
        letter-spacing: -.5px; text-align: center; margin-bottom: 6px;
        line-height: 1.15;
    }
    .form-sub {
        font-size: .76rem; color: var(--txt3);
        text-align: center; margin-bottom: 24px; line-height: 1.5;
    }

    /* ── Campos de input ── */
    .field { width: 100%; margin-bottom: 11px; position: relative; }
    .fi {   /* ícone dentro do campo */
        position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
        color: var(--txt3); font-size: .8rem; pointer-events: none;
        transition: color .18s; z-index: 1;
    }
    .field input {
        width: 100%; height: 46px;
        padding: 0 42px;
        border: 1.5px solid var(--border); border-radius: var(--r);
        font-family: 'Sora', sans-serif; font-size: .84rem; font-weight: 500;
        color: var(--txt); background: var(--bg); outline: none;
        transition: border-color .18s, box-shadow .18s, background .18s;
    }
    .field input::placeholder { color: var(--txt3); font-weight: 400; }
    .field input:focus {
        border-color: var(--brand);
        box-shadow: 0 0 0 3px rgba(232,168,56,.11);
        background: #201e18;
    }
    .field:focus-within .fi { color: var(--brand); }
    .eye-btn {
        position: absolute; right: 12px; top: 50%; transform: translateY(-50%);
        background: none; border: none; cursor: pointer;
        color: var(--txt3); font-size: .78rem; padding: 4px;
        transition: color .18s; z-index: 1;
    }
    .eye-btn:hover { color: var(--brand); }

    .form-meta {
        width: 100%; display: flex; justify-content: flex-end;
        margin: -2px 0 14px;
    }
    .form-meta a {
        font-size: .73rem; color: var(--brand); font-weight: 600;
        transition: opacity .18s;
    }
    .form-meta a:hover { opacity: .72; }

    /* ── Botão principal ── */
    .btn-submit {
        width: 100%; height: 48px;
        background: linear-gradient(135deg, var(--brand) 0%, var(--brand-dk) 100%);
        border: none; border-radius: var(--r); color: #0f0e0b;
        font-family: 'Sora', sans-serif; font-size: .87rem; font-weight: 700;
        cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px;
        box-shadow: 0 6px 20px rgba(232,168,56,.3);
        transition: transform .2s, box-shadow .2s;
        position: relative; overflow: hidden; margin-top: 2px;
    }
    .btn-submit::before {
        content: ''; position: absolute; inset: 0;
        background: linear-gradient(135deg, rgba(255,255,255,.13) 0%, transparent 55%);
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(232,168,56,.42); }
    .btn-submit:active { transform: translateY(0); }

    /* ── Flash de erro ── */
    .flash-err {
        width: 100%; display: flex; align-items: center; gap: 8px;
        padding: 10px 14px; margin-bottom: 12px;
        background: var(--red-bg); border: 1.5px solid #FCA5A5;
        border-radius: var(--r); color: var(--red);
        font-size: .77rem; font-weight: 600;
    }

    /* ── Indicador força de senha ── */
    .strength-bar {
        display: flex; gap: 4px; margin-top: -4px; margin-bottom: 10px;
        width: 100%;
    }
    .strength-bar span {
        flex: 1; height: 3px; border-radius: 3px;
        background: var(--border); transition: background .3s;
    }
    .str-1 span:nth-child(1)                       { background: var(--red); }
    .str-2 span:nth-child(-n+2)                    { background: var(--brand); }
    .str-3 span:nth-child(-n+3)                    { background: #FBBF24; }
    .str-4 span                                    { background: #16A34A; }

    /* ══════════════════════════════════════════════
       PAINEL DA MARCA  —  o bloco que DESLIZA
    ══════════════════════════════════════════════ */
    .brand-panel {
        position: absolute;
        top: 0; bottom: 0;
        width: var(--brand-pct);
        left: 0;
        z-index: 20;              /* acima dos formulários */
        background: linear-gradient(155deg, var(--sid) 0%, var(--sid2) 52%, #3D1F0A 100%);
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        padding: 44px 32px;
        text-align: center;
        overflow: hidden;

        /* ── A transição de deslizamento ── */
        transition: transform var(--slide-dur) var(--slide-ease);
    }

    /* LOGIN MODE  →  painel da marca na DIREITA */
    .mode-login .brand-panel {
        transform: translateX(var(--brand-to-right));
    }
    /* REGISTER MODE  →  painel da marca na ESQUERDA */
    .mode-register .brand-panel {
        transform: translateX(0%);
    }

    /* ── Decoração de fundo do painel ── */
    .brand-panel .orb-a {
        position: absolute; top: -70px; right: -60px;
        width: 260px; height: 260px; border-radius: 50%;
        background: radial-gradient(circle, rgba(232,168,56,.16) 0%, transparent 65%);
        pointer-events: none;
    }
    .brand-panel .orb-b {
        position: absolute; bottom: -55px; left: -40px;
        width: 200px; height: 200px; border-radius: 50%;
        background: radial-gradient(circle, rgba(232,168,56,.10) 0%, transparent 65%);
        pointer-events: none;
    }
    .brand-panel .dots {
        position: absolute; inset: 0;
        background-image: radial-gradient(rgba(255,255,255,.055) 1px, transparent 1px);
        background-size: 24px 24px; pointer-events: none;
    }

    .brand-inner { position: relative; z-index: 1; width: 100%; }

    /* Conteúdo da marca — dois estados que fazem crossfade */
    .bc {
        position: absolute; inset: 0;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        padding: 28px 24px;
        transition: opacity .3s ease, transform .3s ease;
        pointer-events: none;
    }
    .bc.active { opacity: 1; pointer-events: all; transform: translateY(0); }
    .bc.hidden { opacity: 0; pointer-events: none; transform: translateY(8px); }

    /* Ovo flutuante */
    .brand-egg {
        font-size: 3rem; display: block; margin-bottom: 18px;
        animation: eggFloat 3.2s ease-in-out infinite;
    }
    @keyframes eggFloat {
        0%,100% { transform: translateY(0) rotate(-4deg); }
        50%      { transform: translateY(-12px) rotate(4deg); }
    }

    .brand-title {
        font-size: 1.5rem; font-weight: 800; color: #fff;
        letter-spacing: -.4px; line-height: 1.2; margin-bottom: 12px;
    }
    .brand-title em { color: var(--brand); font-style: normal; }

    .brand-rule {
        width: 38px; height: 2px; margin: 0 auto 16px;
        background: linear-gradient(90deg, transparent, rgba(232,168,56,.65), transparent);
    }
    .brand-desc {
        font-size: .74rem; color: rgba(255,255,255,.38);
        line-height: 1.75; max-width: 200px; margin: 0 auto 28px;
    }

    /* Botão de troca de modo */
    .btn-switch {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 26px;
        border: 2px solid rgba(232,168,56,.45); border-radius: 50px;
        background: rgba(232,168,56,.08); color: rgba(255,255,255,.82);
        font-family: 'Sora', sans-serif; font-size: .79rem; font-weight: 700;
        cursor: pointer; transition: all .22s ease; letter-spacing: .01em;
    }
    .btn-switch:hover {
        background: rgba(232,168,56,.18); border-color: var(--brand); color: #fff;
        transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0,0,0,.22);
    }
    .btn-switch i { font-size: .7rem; transition: transform .22s ease; }
    .btn-switch.to-reg:hover i { transform: translateX(4px); }
    .btn-switch.to-log:hover i { transform: translateX(-4px); }

    /* ══════════════════════════════════════════════
       MOBILE  ─ empilha verticalmente e força scroll nativo
    ══════════════════════════════════════════════ */
    @media (max-width: 680px) {
        html, body {
            height: auto !important;
            min-height: 100vh;
        }
        body {
            display: block !important;
            overflow: visible !important;
            padding: 24px 16px;
        }
        .auth-card {
            width: 100% !important;
            height: auto !important; 
            min-height: 0;
            border-radius: 18px;
            margin: 0 auto;
            display: flex !important;
            flex-direction: column;
        }
        .fp {
            position: static !important; 
            width: 100% !important;
            height: auto !important;
            padding: 36px 20px 32px !important;
        }
        .brand-panel {
            position: static !important; 
            width: 100% !important;
            height: auto !important;
            transform: none !important;
            transition: none !important;
            padding: 32px 20px !important;
        }
        
        .bc.hidden { display: none !important; }
        .bc.active { position: static !important; padding: 0 !important; }
        .brand-egg { display: none !important; }
        
        /* ── MODO LOGIN: formulário no topo, convite embaixo ── */
        .mode-login .fp-login    { order: 1; display: flex !important; }
        .mode-login .brand-panel { order: 2; display: flex !important; }
        .mode-login .fp-register { display: none !important; }

        /* ── MODO REGISTRO: formulário no topo, convite embaixo ── */
        .mode-register .fp-register { order: 1; display: flex !important; }
        .mode-register .brand-panel { order: 2; display: flex !important; }
        .mode-register .fp-login    { display: none !important; }
    }
    </style>
</head>
<body>

<?php
/* ── Modo inicial desta página ─────────────────────────────
   login.php   → começa em 'login'
   register.php → começa em 'register'
   (Ambos os arquivos são idênticos exceto por esta variável)
────────────────────────────────────────────────────────── */
$initial_mode = 'login';
?>

<div class="auth-card" id="auth-card">

    <!-- ════════════════════════════════
         FORMULÁRIO DE LOGIN  (esquerda)
    ════════════════════════════════ -->
    <div class="fp fp-login" id="fp-login">

        <div class="form-badge">📖</div>
        <h1 class="form-title">Bem-vindo<br>de volta!</h1>
        <p class="form-sub">Entre com suas credenciais para acessar o painel</p>

        <?php if (!empty($_SESSION['flash_error'])): ?>
        <div class="flash-err">
            <i class="fas fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($_SESSION['flash_error']) ?></span>
        </div>
        <?php unset($_SESSION['flash_error']); endif; ?>

        <form action="<?= url('/backend/login') ?>" method="POST" style="width:100%;max-width:320px">

            <div class="field">
                <i class="fas fa-envelope fi"></i>
                <input name="email_usuario" type="email"
                       placeholder="seu@email.com" autocomplete="email" required>
            </div>

            <div class="field">
                <i class="fas fa-lock fi"></i>
                <input id="pwd-l" name="senha_usuario" type="password"
                       placeholder="Sua senha" autocomplete="current-password" required>
                <button type="button" class="eye-btn" onclick="toggleEye('pwd-l',this)">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <div class="form-meta">
                <a href="/backend/forgot-password">Esqueci a senha</a>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-arrow-right-to-bracket"></i>
                Entrar no painel
            </button>

            <div style="text-align: center; margin-top: 18px;">
                <a href="/index.html" style="font-size: .82rem; color: var(--txt3); font-weight: 500; text-decoration: none; transition: color .2s ease;" onmouseover="this.style.color='var(--brand)'" onmouseout="this.style.color='var(--txt3)'">Continue sem entrar &rarr;</a>
            </div>

        </form>
    </div>

    <!-- ════════════════════════════════
         FORMULÁRIO DE REGISTRO  (direita)
    ════════════════════════════════ -->
    <div class="fp fp-register" id="fp-register">

        <div class="form-badge">📖</div>
        <h1 class="form-title">Criar<br>conta</h1>
        <p class="form-sub">Preencha os dados para se registrar</p>

        <?php if (!empty($_SESSION['flash_error_reg'])): ?>
        <div class="flash-err">
            <i class="fas fa-circle-exclamation"></i>
            <span><?= htmlspecialchars($_SESSION['flash_error_reg']) ?></span>
        </div>
        <?php unset($_SESSION['flash_error_reg']); endif; ?>

        <form action="/backend/register" method="POST"
              id="form-reg" style="width:100%;max-width:320px">

            <div class="field">
                <i class="fas fa-user fi"></i>
                <input name="nome_usuario" type="text"
                       placeholder="Nome completo" autocomplete="name" required>
            </div>

            <div class="field">
                <i class="fas fa-envelope fi"></i>
                <input name="email_usuario" type="email"
                       placeholder="seu@email.com" autocomplete="email" required>
            </div>

            <div class="field">
                <i class="fas fa-lock fi"></i>
                <input id="pwd-r1" name="senha_usuario" type="password"
                       placeholder="Crie uma senha" autocomplete="new-password"
                       oninput="checkStr(this.value)" required>
                <button type="button" class="eye-btn" onclick="toggleEye('pwd-r1',this)">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <div class="strength-bar" id="sbar">
                <span></span><span></span><span></span><span></span>
            </div>

            <div class="field">
                <i class="fas fa-lock fi"></i>
                <input id="pwd-r2" name="senha_confirm" type="password"
                       placeholder="Confirmar senha" autocomplete="new-password"
                       oninput="checkMatch()" required>
                <button type="button" class="eye-btn" onclick="toggleEye('pwd-r2',this)">
                    <i class="fas fa-eye"></i>
                </button>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-user-plus"></i>
                Criar minha conta
            </button>

            <div style="text-align: center; margin-top: 18px;">
                <a href="/index.html" style="font-size: .82rem; color: var(--txt3); font-weight: 500; text-decoration: none; transition: color .2s ease;" onmouseover="this.style.color='var(--brand)'" onmouseout="this.style.color='var(--txt3)'">Continue sem entrar &rarr;</a>
            </div>

        </form>
    </div>

    <!-- ════════════════════════════════════════════════
         PAINEL DA MARCA  —  desliza entre os dois lados
    ════════════════════════════════════════════════ -->
    <div class="brand-panel" id="brand-panel">
        <div class="orb-a"></div>
        <div class="orb-b"></div>
        <div class="dots"></div>

        <!-- Conteúdo para quando estamos em MODO LOGIN
             (painel está do lado DIREITO, chama o usuário a se cadastrar) -->
        <div class="bc" id="bc-login">
            <span class="brand-egg">📖</span>
            <h2 class="brand-title">Olá,<br><em>bem-vindo!</em></h2>
            <div class="brand-rule"></div>
            <p class="brand-desc">
                Registre-se para ter acesso a todas as funcionalidades do sistema DatisLopo.
            </p>
            <button class="btn-switch to-reg" onclick="switchMode('register')">
                Criar conta
                <i class="fas fa-arrow-right"></i>
            </button>
        </div>

        <!-- Conteúdo para quando estamos em MODO REGISTRO
             (painel está do lado ESQUERDO, convida ao login) -->
        <div class="bc" id="bc-register">
            <span class="brand-egg">📖</span>
            <h2 class="brand-title">Já tem<br><em>uma conta?</em></h2>
            <div class="brand-rule"></div>
            <p class="brand-desc">
                Entre com suas credenciais e acesse o painel de gestão DatisLopo.
            </p>
            <button class="btn-switch to-log" onclick="switchMode('login')">
                <i class="fas fa-arrow-left"></i>
                Fazer login
            </button>
        </div>
    </div>

</div><!-- .auth-card -->

<script>
/* ══════════════════════════════════════════════════════
   AUTH SLIDE ENGINE
   — Modo inicial vem do PHP ($initial_mode)
   — switchMode() desliza o painel da marca e faz
     crossfade entre os conteúdos
══════════════════════════════════════════════════════ */

const INITIAL_MODE = '<?= $initial_mode ?>';
const card  = document.getElementById('auth-card');
const bcLog = document.getElementById('bc-login');
const bcReg = document.getElementById('bc-register');
let   currentMode = null;
let   switching   = false;

/* ── Aplica um modo SEM animação (carregamento inicial) ── */
function setModeInstant(mode) {
    card.classList.remove('mode-login', 'mode-register');
    card.classList.add('mode-' + mode);
    updateBrandContent(mode);
    currentMode = mode;
}

/* ── Troca de modo COM animação deslizante ── */
function switchMode(target) {
    if (switching || target === currentMode) return;
    switching = true;

    /* 1. Inicia o deslizamento do painel */
    card.classList.remove('mode-' + currentMode);
    card.classList.add('mode-' + target);

    /* 2. Crossfade do conteúdo da marca a meio caminho */
    const half = parseFloat(
        getComputedStyle(document.documentElement)
            .getPropertyValue('--slide-dur')
    ) * 1000 * 0.45; /* 45% do tempo de deslizamento */

    setTimeout(() => updateBrandContent(target), half);

    /* 3. Libera para nova interação após o slide completar */
    const full = parseFloat(
        getComputedStyle(document.documentElement)
            .getPropertyValue('--slide-dur')
    ) * 1000;

    setTimeout(() => {
        currentMode = target;
        switching   = false;
    }, full);
}

function updateBrandContent(mode) {
    if (mode === 'login') {
        bcLog.classList.replace('hidden', 'active') ||
            bcLog.classList.add('active');
        bcReg.classList.replace('active', 'hidden') ||
            bcReg.classList.add('hidden');
    } else {
        bcReg.classList.replace('hidden', 'active') ||
            bcReg.classList.add('active');
        bcLog.classList.replace('active', 'hidden') ||
            bcLog.classList.add('hidden');
    }
}

/* ── Inicializa sem animação ──
   Desliga transições do painel da marca E das forms
   para que o posicionamento inicial seja instantâneo. */
const bp     = document.getElementById('brand-panel');
const fpList = document.querySelectorAll('.fp');

bp.style.transition = 'none';
fpList.forEach(f => f.style.transition = 'none');

setModeInstant(INITIAL_MODE);

/* Reativa as transições no próximo frame */
requestAnimationFrame(() => {
    requestAnimationFrame(() => {
        bp.style.transition = '';
        fpList.forEach(f => f.style.transition = '');
    });
});

/* ══════════════════════════════════════════════════════
   UTILITÁRIOS
══════════════════════════════════════════════════════ */

/* Mostrar/ocultar senha */
function toggleEye(id, btn) {
    const el = document.getElementById(id);
    const ic = btn.querySelector('i');
    if (el.type === 'password') {
        el.type = 'text'; ic.className = 'fas fa-eye-slash';
    } else {
        el.type = 'password'; ic.className = 'fas fa-eye';
    }
}

/* Força de senha */
function checkStr(val) {
    const bar = document.getElementById('sbar');
    let s = 0;
    if (val.length >= 6)  s++;
    if (val.length >= 10) s++;
    if (/[A-Z]/.test(val) && /[0-9]/.test(val)) s++;
    if (/[^A-Za-z0-9]/.test(val)) s++;
    bar.className = 'strength-bar' + (s ? ' str-' + s : '');
}

/* Confirmação de senha */
function checkMatch() {
    const p2 = document.getElementById('pwd-r2');
    const ok  = p2.value === document.getElementById('pwd-r1').value;
    p2.style.borderColor = p2.value ? (ok ? '' : '#DC2626') : '';
    p2.style.boxShadow   = p2.value && !ok ? '0 0 0 3px rgba(220,38,38,.1)' : '';
}

/* Bloqueia envio do registro se senhas divergirem */
document.getElementById('form-reg').addEventListener('submit', function (e) {
    if (document.getElementById('pwd-r1').value !==
        document.getElementById('pwd-r2').value) {
        e.preventDefault();
        document.getElementById('pwd-r2').focus();
        checkMatch();
    }
});
</script>
</body>
</html>
