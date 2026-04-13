<?php

/**
 * Router para o servidor embutido do PHP.
 *
 * Uso: php -S localhost:3020 router.php
 *
 * Fluxo:
 *   - Arquivos estáticos (CSS, JS, imagens, HTML) → servidos diretamente
 *   - /backend/*  → roteado para backend/index.php
 *   - Qualquer outra rota → serve o index.html (front-end de vendas)
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// ── Arquivos estáticos existentes: serve diretamente ──────
if ($uri !== '/' && file_exists(__DIR__ . $uri) && !is_dir(__DIR__ . $uri)) {
    return false;
}

// ── Rotas do backend (painel de ebooks) ───────────────────
if (str_starts_with($uri, '/backend')) {
    $_SERVER['SCRIPT_NAME'] = '/backend/index.php';
    require __DIR__ . '/backend/index.php';
    return;
}

// ── Front-end público (página de vendas) ou Redirecionamento ────────
if ($uri === '/' || $uri === '') {
    header('Location: /backend/login');
    return;
}

$indexHtml = __DIR__ . '/index.html';

if (file_exists($indexHtml)) {
    readfile($indexHtml);
    return;
}

// ── Fallback 404 ──────────────────────────────────────────
http_response_code(404);
echo '404 — Página não encontrada.';