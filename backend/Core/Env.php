<?php

namespace Datislopo\Ebook\Core;

class Env
{
    /**
     * Carrega variáveis do arquivo .env para getenv() / $_ENV.
     */
    public static function carregar(string $diretorio): void
    {
        $arquivo = rtrim($diretorio, '/') . '/.env';

        if (!file_exists($arquivo)) {
            return; // Sem .env? Sem problema — usa os defaults do Config.php
        }

        $linhas = file($arquivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($linhas as $linha) {
            $linha = trim($linha);

            // Ignora comentários
            if (str_starts_with($linha, '#') || !str_contains($linha, '=')) {
                continue;
            }

            [$chave, $valor] = explode('=', $linha, 2);
            $chave = trim($chave);
            $valor = trim($valor);

            // Remove aspas do valor, se houver
            if (
                (str_starts_with($valor, '"') && str_ends_with($valor, '"')) ||
                (str_starts_with($valor, "'") && str_ends_with($valor, "'"))
            ) {
                $valor = substr($valor, 1, -1);
            }

            if (!array_key_exists($chave, $_ENV)) {
                $_ENV[$chave]    = $valor;
                $_SERVER[$chave] = $valor;
                putenv("{$chave}={$valor}");
            }
        }
    }
}