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
            // Layout auth — as páginas de auth já possuem HTML completo
            require $caminhoView;
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