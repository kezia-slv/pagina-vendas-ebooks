<?php

namespace Datislopo\Ebook\Core;

/**
 * Renderizador de Views — DatisLopo Ebooks
 *
 * Estrutura esperada de pastas:
 *   backend/Views/templates/
 *     ├── partials/
 *     │   ├── header.php
 *     │   └── footer.php
 *     ├── ebook/
 *     │   ├── index.php
 *     │   ├── criar.php
 *     │   ├── editar.php
 *     │   └── deletar.php
 *     └── auth/
 *         └── login.php
 */
class View
{
    /**
     * Renderiza uma view com header e footer automáticos.
     *
     * @param  string $nomeView  Caminho relativo da view (ex: 'ebook/listar')
     * @param  array  $dados     Variáveis disponíveis dentro da view
     * @throws \Exception        Se a view não for encontrada
     */
    public static function render(string $nomeView, array $dados = []): void
    {
        // Proteção contra LFI: bloqueia tentativas de path traversal
        $nomeView = str_replace(['../', '..\\', '../', '..\\'], '', $nomeView);

        $caminhoView = __DIR__ . "/../Views/templates/{$nomeView}.php";

        if (!file_exists($caminhoView)) {
            throw new \Exception("View não encontrada: '{$nomeView}'.");
        }

        // Disponibiliza as variáveis para a view
        extract($dados);

        $isPaginaAuth = str_starts_with($nomeView, 'auth/') || str_contains($nomeView, '/auth/');

        if ($isPaginaAuth) {
            // Layout auth — sem header/footer
            echo '<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DatisLopo — Acesso</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: #0f0e0b;
      color: #f0ead8;
      font-family: "DM Sans", sans-serif;
      font-weight: 300;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
    }
  </style>
</head>
<body>';

            require $caminhoView;

            echo '</body>
</html>';
        }
        else {
            // Layout principal — com header e footer
            $header = __DIR__ . '/../Views/templates/partials/header.php';
            $footer = __DIR__ . '/../Views/templates/partials/footer.php';

            if (!file_exists($header)) {
                throw new \Exception("Partial não encontrado: 'partials/header.php'.");
            }
            if (!file_exists($footer)) {
                throw new \Exception("Partial não encontrado: 'partials/footer.php'.");
            }

            require $header;
            require $caminhoView;
            require $footer;
        }
    }
}