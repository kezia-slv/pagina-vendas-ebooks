<?php

// =========================================================
// CAMINHOS
// =========================================================

if (!function_exists('base_path')) {
    /**
     * Retorna o caminho absoluto da raiz do projeto.
     */
    function base_path(string $path = ''): string
    {
        return __DIR__ . '/../../' . ($path ? ltrim($path, '/') : '');
    }
}

if (!function_exists('public_path')) {
    /**
     * Retorna o caminho público (raiz do projeto).
     */
    function public_path(string $path = ''): string
    {
        return base_path($path);
    }
}

if (!function_exists('storage_path')) {
    /**
     * Retorna o caminho da pasta de capas dos ebooks.
     * Exemplo: storage_path('capas/minha_capa.jpg')
     */
    function storage_path(string $path = ''): string
    {
        return base_path('backend/uploads/capas/' . ($path ? ltrim($path, '/') : ''));
    }
}

// =========================================================
// VIEWS E URLS
// =========================================================

if (!function_exists('view')) {
    /**
     * Helper para renderizar views.
     * Exemplo: view('ebook/listar', ['ebooks' => $lista])
     */
    function view(string $name, array $data = []): void
    {
        \Ovos\Ebenezer\Core\View::render($name, $data);
    }
}

if (!function_exists('url')) {
    /**
     * Retorna URL absoluta a partir da APP_URL ou HTTP_HOST.
     * Exemplo: url('ebook/listar') → http://localhost/datislopo/ebook/listar
     */
    function url(string $path = ''): string
    {
        $baseUrl = getenv('APP_URL') ?: (
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') .
            '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
        );
        return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('base_url')) {
    /**
     * Retorna apenas o path base relativo (sem domínio), útil para assets.
     * Exemplo: base_url('css/style.css') → /datislopo/css/style.css
     */
    function base_url(string $path = ''): string
    {
        $appUrl   = getenv('APP_URL') ?: (
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') .
            '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
        );
        $basePath = parse_url($appUrl, PHP_URL_PATH) ?? '';
        return rtrim($basePath, '/') . '/' . ltrim($path, '/');
    }
}

// =========================================================
// IMAGENS DE EBOOKS
// =========================================================

if (!function_exists('asset_path')) {
    /**
     * Normaliza e retorna o caminho da capa de um ebook.
     * Usa placeholder padrão se a imagem estiver vazia.
     *
     * Exemplo: asset_path('capas/ainda_ha_perdao.jpg')
     *          → /backend/uploads/capas/ainda_ha_perdao.jpg
     */
    function asset_path(?string $path, string $default = '/img/ebook-placeholder.webp'): string
    {
        if (empty($path)) {
            return $default;
        }

        // URL externa: retorna como está
        if (str_starts_with($path, 'http')) {
            return $path;
        }

        // Já tem o caminho base correto
        if (str_contains($path, '/backend/uploads/')) {
            return $path;
        }

        $path = ltrim($path, '/');

        if (str_starts_with($path, 'uploads/')) {
            return '/backend/' . $path;
        }

        return '/backend/uploads/capas/' . $path;
    }
}

// =========================================================
// PREÇOS
// =========================================================

if (!function_exists('formatar_preco')) {
    /**
     * Formata um valor decimal para exibição em BRL.
     * Exemplo: formatar_preco(29.90) → R$ 29,90
     */
    function formatar_preco(?float $valor): string
    {
        if ($valor === null) {
            return '—';
        }
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }
}

// =========================================================
// DATAS
// =========================================================

if (!function_exists('calcularTempoDecorrido')) {
    /**
     * Retorna o tempo decorrido de uma data até agora em linguagem natural.
     * Exemplo: "há 3 dias", "há 2 meses"
     */
    function calcularTempoDecorrido(mixed $data): string
    {
        if (!$data) {
            return 'Data não informada';
        }

        $timestamp = is_numeric($data) ? (int) $data : strtotime($data);
        if (!$timestamp) {
            return (string) $data;
        }

        $diff = time() - $timestamp;

        return match (true) {
            $diff < 60       => 'agora mesmo',
            $diff < 3600     => 'há ' . round($diff / 60)    . (round($diff / 60) === 1.0    ? ' minuto'  : ' minutos'),
            $diff < 86400    => 'há ' . round($diff / 3600)  . (round($diff / 3600) === 1.0  ? ' hora'    : ' horas'),
            $diff < 2592000  => 'há ' . round($diff / 86400) . (round($diff / 86400) === 1.0 ? ' dia'     : ' dias'),
            $diff < 31536000 => 'há ' . round($diff / 2592000) . (round($diff / 2592000) === 1.0 ? ' mês' : ' meses'),
            default          => date('d/m/Y', $timestamp),
        };
    }
}

// =========================================================
// SEGURANÇA E DEBUG
// =========================================================

if (!function_exists('e')) {
    /**
     * Escapa string para saída HTML segura (prevenção de XSS).
     * Uso nas Views: <?= e($variavel) ?>
     */
    function e(mixed $value): string
    {
        return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('dd')) {
    /**
     * Dump and Die — exibe variáveis formatadas e encerra a execução.
     * Apenas para uso em desenvolvimento.
     */
    function dd(mixed ...$vars): never
    {
        foreach ($vars as $var) {
            echo '<pre style="background:#1a1916;color:#e8a838;padding:16px;border-radius:8px;font-size:13px;margin:8px 0;">';
            var_dump($var);
            echo '</pre>';
        }
        die();
    }
}