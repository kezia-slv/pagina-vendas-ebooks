<?php
/**
 * Header / Layout principal do painel administrativo — DatisLopo Ebooks
 * Inclui <head>, sidebar e abertura do container principal.
 */

$flash = \Datislopo\Ebook\Core\Flash::get();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DatisLopo — Painel</title>

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;700&display=swap" rel="stylesheet">

  <!-- Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <style>
    /* ── Reset & Base ─────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg-body:      #0f0e0b;
      --bg-sidebar:   #131210;
      --bg-card:      #1a1916;
      --bg-input:     #242219;
      --bg-hover:     #2a2820;
      --border:       #2e2c26;
      --border-light: #3d3a32;
      --gold:         #e8a838;
      --gold-dim:     #c4912e;
      --gold-light:   #f0c060;
      --text:         #f0ead8;
      --text-muted:   #9a9484;
      --text-dim:     #6b6560;
      --success:      #34d399;
      --success-bg:   rgba(52,211,153,.12);
      --danger:       #f87171;
      --danger-bg:    rgba(248,113,113,.12);
      --warning:      #fbbf24;
      --warning-bg:   rgba(251,191,36,.12);
      --info:         #60a5fa;
      --info-bg:      rgba(96,165,250,.12);
      --radius:       10px;
      --radius-lg:    14px;
      --shadow:       0 4px 24px rgba(0,0,0,.35);
      --transition:   .25s cubic-bezier(.4,0,.2,1);
    }

    body {
      background: var(--bg-body);
      color: var(--text);
      font-family: 'Inter', -apple-system, sans-serif;
      font-weight: 400;
      font-size: 14px;
      line-height: 1.6;
      min-height: 100vh;
      display: flex;
      -webkit-font-smoothing: antialiased;
    }

    a { color: var(--gold); text-decoration: none; transition: color var(--transition); }
    a:hover { color: var(--gold-light); }

    /* ── Sidebar ──────────────────────────────── */
    .sidebar {
      width: 260px;
      min-height: 100vh;
      background: var(--bg-sidebar);
      border-right: 1px solid var(--border);
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0; left: 0; bottom: 0;
      z-index: 100;
      overflow-y: auto;
      transition: transform var(--transition);
    }

    .sidebar-brand {
      padding: 28px 24px 20px;
      border-bottom: 1px solid var(--border);
    }

    .sidebar-brand h1 {
      font-family: 'Playfair Display', serif;
      font-size: 22px;
      font-weight: 700;
      color: var(--gold);
      letter-spacing: .5px;
    }

    .sidebar-brand span {
      display: block;
      font-size: 11px;
      color: var(--text-dim);
      margin-top: 2px;
      text-transform: uppercase;
      letter-spacing: 1.5px;
    }

    .sidebar-nav {
      flex: 1;
      padding: 16px 12px;
      list-style: none;
    }

    .sidebar-nav li { margin-bottom: 2px; }

    .sidebar-nav a {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 11px 16px;
      border-radius: var(--radius);
      color: var(--text-muted);
      font-size: 13.5px;
      font-weight: 500;
      transition: all var(--transition);
    }

    .sidebar-nav a:hover {
      background: var(--bg-hover);
      color: var(--text);
    }

    .sidebar-nav a.active {
      background: linear-gradient(135deg, rgba(232,168,56,.15), rgba(232,168,56,.06));
      color: var(--gold);
      box-shadow: inset 3px 0 0 var(--gold);
    }

    .sidebar-nav a i { width: 20px; text-align: center; font-size: 15px; }

    /* ── Main Content ─────────────────────────── */
    .main-wrapper {
      flex: 1;
      margin-left: 260px;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .topbar {
      height: 64px;
      background: var(--bg-card);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 32px;
      position: sticky;
      top: 0;
      z-index: 50;
    }

    .topbar-title {
      font-size: 16px;
      font-weight: 600;
      color: var(--text);
    }

    .topbar-right {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .btn-menu-mobile {
      display: none;
      background: none;
      border: none;
      color: var(--text);
      font-size: 22px;
      cursor: pointer;
      padding: 6px;
    }

    .main-content {
      flex: 1;
      padding: 32px;
    }

    /* ── Flash Messages ───────────────────────── */
    .flash-message {
      padding: 14px 20px;
      border-radius: var(--radius);
      margin-bottom: 24px;
      font-size: 13.5px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 10px;
      animation: flashIn .4s ease;
    }

    .flash-message.sucesso  { background: var(--success-bg); color: var(--success); border: 1px solid rgba(52,211,153,.2); }
    .flash-message.erro     { background: var(--danger-bg);  color: var(--danger);  border: 1px solid rgba(248,113,113,.2); }
    .flash-message.aviso    { background: var(--warning-bg); color: var(--warning); border: 1px solid rgba(251,191,36,.2); }
    .flash-message.info     { background: var(--info-bg);    color: var(--info);    border: 1px solid rgba(96,165,250,.2); }

    @keyframes flashIn { from { opacity:0; transform:translateY(-8px); } to { opacity:1; transform:translateY(0); } }

    /* ── Cards & Panels ───────────────────────── */
    .panel {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 28px;
      box-shadow: var(--shadow);
    }

    .panel-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
      flex-wrap: wrap;
      gap: 12px;
    }

    .panel-header h2 {
      font-size: 20px;
      font-weight: 700;
      color: var(--text);
    }

    /* ── Buttons ───────────────────────────────── */
    .btn {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 10px 20px;
      border-radius: var(--radius);
      font-size: 13px;
      font-weight: 600;
      border: none;
      cursor: pointer;
      transition: all var(--transition);
      text-decoration: none;
      line-height: 1;
    }

    .btn-gold {
      background: linear-gradient(135deg, var(--gold), var(--gold-dim));
      color: #0f0e0b;
    }
    .btn-gold:hover {
      background: linear-gradient(135deg, var(--gold-light), var(--gold));
      transform: translateY(-1px);
      box-shadow: 0 4px 16px rgba(232,168,56,.3);
      color: #0f0e0b;
    }

    .btn-outline {
      background: transparent;
      border: 1px solid var(--border-light);
      color: var(--text-muted);
    }
    .btn-outline:hover {
      border-color: var(--gold);
      color: var(--gold);
      background: rgba(232,168,56,.06);
    }

    .btn-danger {
      background: var(--danger-bg);
      color: var(--danger);
      border: 1px solid rgba(248,113,113,.2);
    }
    .btn-danger:hover {
      background: rgba(248,113,113,.2);
      box-shadow: 0 4px 16px rgba(248,113,113,.15);
    }

    .btn-sm { padding: 7px 14px; font-size: 12px; }

    /* ── Table ─────────────────────────────────── */
    .table-responsive { overflow-x: auto; }

    .data-table {
      width: 100%;
      border-collapse: collapse;
      font-size: 13.5px;
    }

    .data-table thead th {
      text-align: left;
      padding: 12px 16px;
      color: var(--text-dim);
      font-weight: 600;
      font-size: 11px;
      text-transform: uppercase;
      letter-spacing: 1px;
      border-bottom: 1px solid var(--border);
      white-space: nowrap;
    }

    .data-table tbody tr {
      border-bottom: 1px solid var(--border);
      transition: background var(--transition);
    }

    .data-table tbody tr:hover { background: var(--bg-hover); }

    .data-table tbody td {
      padding: 14px 16px;
      vertical-align: middle;
      color: var(--text);
    }

    .data-table .thumb {
      width: 48px;
      height: 64px;
      object-fit: cover;
      border-radius: 6px;
      border: 1px solid var(--border);
    }

    .data-table .actions {
      display: flex;
      gap: 6px;
      flex-wrap: nowrap;
    }

    /* ── Badges ────────────────────────────────── */
    .badge {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      padding: 4px 10px;
      border-radius: 20px;
      font-size: 11px;
      font-weight: 600;
      white-space: nowrap;
    }
    .badge-gold     { background: rgba(232,168,56,.15); color: var(--gold); }
    .badge-success  { background: var(--success-bg); color: var(--success); }
    .badge-muted    { background: var(--bg-hover); color: var(--text-dim); }

    /* ── Forms ─────────────────────────────────── */
    .form-group {
      margin-bottom: 20px;
    }

    .form-group label {
      display: block;
      font-size: 12px;
      font-weight: 600;
      color: var(--text-muted);
      text-transform: uppercase;
      letter-spacing: .8px;
      margin-bottom: 8px;
    }

    .form-control {
      width: 100%;
      padding: 12px 16px;
      background: var(--bg-input);
      border: 1px solid var(--border);
      border-radius: var(--radius);
      color: var(--text);
      font-size: 14px;
      font-family: inherit;
      transition: border-color var(--transition), box-shadow var(--transition);
    }

    .form-control:focus {
      outline: none;
      border-color: var(--gold);
      box-shadow: 0 0 0 3px rgba(232,168,56,.12);
    }

    .form-control::placeholder { color: var(--text-dim); }

    textarea.form-control {
      resize: vertical;
      min-height: 100px;
    }

    select.form-control {
      appearance: none;
      background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%239a9484' viewBox='0 0 16 16'%3E%3Cpath d='M1.5 5.5l6.5 6 6.5-6'/%3E%3C/svg%3E");
      background-repeat: no-repeat;
      background-position: right 14px center;
      padding-right: 40px;
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    .form-actions {
      display: flex;
      gap: 12px;
      margin-top: 28px;
      padding-top: 24px;
      border-top: 1px solid var(--border);
    }

    .form-hint {
      font-size: 11px;
      color: var(--text-dim);
      margin-top: 4px;
    }

    /* ── File Upload ───────────────────────────── */
    .file-upload-area {
      border: 2px dashed var(--border-light);
      border-radius: var(--radius);
      padding: 24px;
      text-align: center;
      cursor: pointer;
      transition: all var(--transition);
    }

    .file-upload-area:hover {
      border-color: var(--gold);
      background: rgba(232,168,56,.04);
    }

    .file-upload-area i {
      font-size: 28px;
      color: var(--text-dim);
      margin-bottom: 8px;
    }

    .file-upload-area p {
      font-size: 12px;
      color: var(--text-dim);
    }

    .current-image {
      display: flex;
      align-items: center;
      gap: 16px;
      margin-bottom: 12px;
    }

    .current-image img {
      width: 60px;
      height: 80px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid var(--border);
    }

    .current-image span {
      font-size: 12px;
      color: var(--text-dim);
    }

    /* ── Delete Confirmation ──────────────────── */
    .delete-preview {
      display: flex;
      gap: 24px;
      padding: 24px;
      background: var(--bg-input);
      border-radius: var(--radius);
      border: 1px solid var(--border);
      margin-bottom: 24px;
    }

    .delete-preview img {
      width: 80px;
      height: 110px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid var(--border);
    }

    .delete-preview-info h3 {
      font-size: 18px;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 8px;
    }

    .delete-preview-info p {
      font-size: 13px;
      color: var(--text-muted);
      margin-bottom: 4px;
    }

    /* ── Empty State ───────────────────────────── */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: var(--text-dim);
    }

    .empty-state i {
      font-size: 48px;
      margin-bottom: 16px;
      opacity: .4;
    }

    .empty-state h3 {
      font-size: 18px;
      color: var(--text-muted);
      margin-bottom: 8px;
    }

    .empty-state p {
      font-size: 13px;
      margin-bottom: 20px;
    }

    /* ── Price ─────────────────────────────────── */
    .price-current {
      font-weight: 700;
      color: var(--gold);
    }

    .price-original {
      text-decoration: line-through;
      color: var(--text-dim);
      font-size: 12px;
      margin-left: 6px;
    }

    /* ── Responsive ────────────────────────────── */
    .overlay {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(0,0,0,.6);
      z-index: 90;
    }

    @media (max-width: 768px) {
      .sidebar {
        transform: translateX(-100%);
      }
      .sidebar.open {
        transform: translateX(0);
      }
      .overlay.active { display: block; }
      .main-wrapper { margin-left: 0; }
      .btn-menu-mobile { display: block; }
      .topbar { padding: 0 16px; }
      .main-content { padding: 20px 16px; }
      .form-row { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<!-- Overlay mobile -->
<div class="overlay" id="overlay"></div>

<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
  <div class="sidebar-brand">
    <h1>DatisLopo</h1>
    <span>Painel de Ebooks</span>
  </div>

  <ul class="sidebar-nav">
    <li>
      <a href="<?= url('backend/ebook') ?>"
         class="<?= str_contains($_SERVER['REQUEST_URI'], '/ebook') ? 'active' : '' ?>">
        <i class="fas fa-book"></i> Ebooks
      </a>
      <a href="<?= url('backend/usuario') ?>"
         class="<?= str_contains($_SERVER['REQUEST_URI'], '/usuario') ? 'active' : '' ?>">
        <i class="fas fa-user"></i> Usuários
      </a>
    </li>
    <li>
      <a href="/index.html" style="margin-top: 20px; border-top: 1px solid var(--border); padding-top: 20px;">
        <i class="fas fa-globe"></i> Ver Site
      </a>
    </li>
  </ul>
</aside>

<!-- Main -->
<div class="main-wrapper">
  <header class="topbar">
    <div style="display:flex;align-items:center;gap:12px;">
      <button class="btn-menu-mobile" id="btnMenu" aria-label="Menu">
        <i class="fas fa-bars"></i>
      </button>
      <span class="topbar-title"><?= $tituloPagina ?? 'Painel' ?></span>
    </div>
    <div class="topbar-right">
      <span style="font-size:12px;color:var(--text-dim);"><?= date('d/m/Y') ?></span>
    </div>
  </header>

  <main class="main-content">

    <?php if ($flash): ?>
      <div class="flash-message <?= e($flash['type']) ?>">
        <i class="fas <?= $flash['type'] === 'sucesso' ? 'fa-check-circle' : ($flash['type'] === 'erro' ? 'fa-exclamation-circle' : 'fa-info-circle') ?>"></i>
        <?= e($flash['message']) ?>
      </div>
    <?php endif; ?>
