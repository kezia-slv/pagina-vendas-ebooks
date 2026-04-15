<?php
// Serve a página principal do site (catálogo de ebooks)
$indexHtml = __DIR__ . '/index.html';
if (file_exists($indexHtml)) {
    readfile($indexHtml);
} else {
    echo 'Página não encontrada.';
}
