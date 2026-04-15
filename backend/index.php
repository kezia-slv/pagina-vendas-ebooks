<?php

namespace Datislopo\Ebook;

// =========================================================
// CONFIGURAÇÃO DE ERROS
// =========================================================
ini_set('display_errors', 1);
ini_set('error_log', __DIR__ . '/php_error.log');
error_reporting(E_ALL);

// =========================================================
// SESSÃO
// =========================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =========================================================
// AUTOLOAD E VARIÁVEIS DE AMBIENTE
// =========================================================
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Core/Env.php';

\Datislopo\Ebook\Core\Env::carregar(__DIR__);

// =========================================================
// IMPORTS
// =========================================================
use Bramus\Router\Router;
use Datislopo\Ebook\Rotas\Rotas;

// =========================================================
// ROTEADOR
// =========================================================
$router = new Router();

// Define o base path dinamicamente
$basePath = dirname($_SERVER['SCRIPT_NAME']);

// Ajuste para servidor embutido do PHP (php -S)
if (str_contains($_SERVER['REQUEST_URI'], $basePath . '/index.php')) {
    $basePath .= '/index.php';
}

// Garantia: em hospedagem compartilhada, o basePath deve ser /backend
// Se dirname() retornar algo inesperado (ex: '/' ou '\'), forçar /backend
if ($basePath === '/' || $basePath === '\\' || $basePath === '.') {
    $basePath = '/backend';
}

$router->setBasePath($basePath);
$router->setNamespace('Datislopo\Ebook\Controllers');

// =========================================================
// REGISTRO DAS ROTAS
// =========================================================
foreach (Rotas::get() as $metodoHttp => $rotas) {
    foreach ($rotas as $uri => $acao) {
        $metodo = strtolower($metodoHttp);
        $router->{ $metodo}($uri, $acao);
    }
}

// =========================================================
// ROTA 404
// =========================================================
$router->set404(function () {
    http_response_code(404);
    echo json_encode([
    'sucesso' => false,
    'mensagem' => 'Rota não encontrada.',
    'uri' => $_SERVER['REQUEST_URI'],
    ], JSON_UNESCAPED_UNICODE);
});

// =========================================================
// EXECUÇÃO
// =========================================================
try {
    $router->run();
}
catch (\Throwable $e) {
    http_response_code(500);
    error_log('[DatisLopo] Erro fatal: ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());

    echo json_encode([
        'sucesso' => false,
        'mensagem' => 'Erro interno no servidor.',
        'detalhes' => $e->getMessage(),
    ], JSON_UNESCAPED_UNICODE);
}