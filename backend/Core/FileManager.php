<?php

namespace Datislopo\Ebook\Core;

/**
 * Gerenciador de arquivos para upload de capas de ebooks.
 * Tipos aceitos por padrão: JPEG, PNG e WebP.
 * Tamanho máximo padrão: 3MB.
 */
class FileManager
{
    private string $diretorioBase;

    // Tipos de imagem aceitos para capas de ebooks
    private const TIPOS_IMAGEM = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    // 3MB em bytes
    private const TAMANHO_MAXIMO = 3145728;

    public function __construct(string $diretorioBase)
    {
        $this->diretorioBase = rtrim($diretorioBase, '/');
    }

    /**
     * Salva a capa de um ebook.
     *
     * @param  array  $file            Array do arquivo ($_FILES['img_ebook'])
     * @param  string $subDiretorio    Subdiretório de destino (ex: 'capas')
     * @param  array  $tiposPermitidos Tipos MIME aceitos (usa padrão de imagens se omitido)
     * @param  int    $tamanhoMaximo   Limite em bytes (padrão: 3MB)
     * @return string                  Caminho relativo do arquivo salvo
     * @throws \Exception
     */
    public function salvarArquivo(
        array  $file,
        string $subDiretorio    = 'capas',
        array  $tiposPermitidos = self::TIPOS_IMAGEM,
        int    $tamanhoMaximo   = self::TAMANHO_MAXIMO
    ): string {
        $this->validarArquivo($file, $tiposPermitidos, $tamanhoMaximo);

        $diretorioDestino = $this->diretorioBase . '/' . trim($subDiretorio, '/');

        if (!is_dir($diretorioDestino)) {
            if (!mkdir($diretorioDestino, 0755, true)) {
                throw new \Exception("Falha ao criar o diretório de destino: {$diretorioDestino}");
            }
        }

        $novoNome      = $this->gerarNomeUnico($file);
        $caminhoFinal  = $diretorioDestino . '/' . $novoNome;

        if (!move_uploaded_file($file['tmp_name'], $caminhoFinal)) {
            throw new \Exception("Falha ao mover o arquivo enviado para o servidor.");
        }

        return trim($subDiretorio, '/') . '/' . $novoNome;
    }

    /**
     * Remove a capa de um ebook do disco.
     *
     * @param  string|null $caminhoRelativo Caminho relativo do arquivo
     * @return bool
     */
    public function deletar(?string $caminhoRelativo): bool
    {
        if (empty($caminhoRelativo)) {
            return true;
        }

        $caminhoCompleto = $this->diretorioBase . '/' . $caminhoRelativo;

        if (file_exists($caminhoCompleto) && is_file($caminhoCompleto)) {
            return unlink($caminhoCompleto);
        }

        return true;
    }

    // =========================================================
    // HELPERS PRIVADOS
    // =========================================================

    private function validarArquivo(array $file, array $tiposPermitidos, int $tamanhoMaximo): void
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $erros = [
                UPLOAD_ERR_INI_SIZE   => 'O arquivo excede o limite definido no php.ini.',
                UPLOAD_ERR_FORM_SIZE  => 'O arquivo excede o limite definido no formulário.',
                UPLOAD_ERR_PARTIAL    => 'O upload foi feito parcialmente.',
                UPLOAD_ERR_NO_FILE    => 'Nenhum arquivo foi enviado.',
                UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporária ausente no servidor.',
                UPLOAD_ERR_CANT_WRITE => 'Falha ao gravar o arquivo no disco.',
            ];
            throw new \Exception($erros[$file['error']] ?? "Erro desconhecido no upload. Código: {$file['error']}");
        }

        if ($file['size'] > $tamanhoMaximo) {
            $limite = number_format($tamanhoMaximo / 1024 / 1024, 1) . 'MB';
            throw new \Exception("A imagem excede o tamanho máximo permitido de {$limite}.");
        }

        $tipoReal = function_exists('mime_content_type')
            ? mime_content_type($file['tmp_name'])
            : $file['type'];

        if (!in_array($tipoReal, $tiposPermitidos)) {
            throw new \Exception(
                "Tipo de arquivo inválido ({$tipoReal}). " .
                "Formatos aceitos: JPEG, PNG e WebP."
            );
        }
    }

    private function gerarNomeUnico(array $file): string
    {
        $extensao = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        return uniqid('capa_', true) . '.' . $extensao;
    }
}