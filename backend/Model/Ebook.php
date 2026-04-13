<?php

namespace Datislopo\Ebook\Models;

use PDO;
use Exception;

/**
 * Model para gerenciar a tabela tbl_ebooks
 * Schema: id_ebooks, nome_ebook, autor_ebook, descricao_ebook,
 *         img_ebook, preco_original, preco_ebook, link_pagamento,
 *         selo_ebook, criado_em, atualizado_em, excluido_em
 */
class Ebook
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // =========================================================
    // CRIAÇÃO (CREATE)
    // =========================================================

    public function inserirEbook(array $dados): int|false
    {
        $colunas      = implode(', ', array_keys($dados));
        $placeholders = ':' . implode(', :', array_keys($dados));

        $sql = "INSERT INTO tbl_ebooks ($colunas) VALUES ($placeholders)";

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($dados as $coluna => &$valor) {
                $stmt->bindValue(":{$coluna}", $valor);
            }
            if ($stmt->execute()) {
                return (int) $this->db->lastInsertId();
            }
            return false;
        } catch (Exception $e) {
            error_log("ERRO ao inserir ebook: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // ATUALIZAÇÃO (UPDATE)
    // =========================================================

    public function atualizarEbook(int $id_ebooks, array $dados): bool
    {
        $dados['atualizado_em'] = date('Y-m-d H:i:s');

        $setParts = [];
        foreach ($dados as $coluna => $valor) {
            $setParts[] = "{$coluna} = :{$coluna}";
        }
        $setString = implode(', ', $setParts);

        $sql = "UPDATE tbl_ebooks SET {$setString} WHERE id_ebooks = :id_ebooks";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id_ebooks', $id_ebooks, PDO::PARAM_INT);
            foreach ($dados as $coluna => &$valor) {
                $stmt->bindValue(":{$coluna}", $valor);
            }
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("ERRO ao atualizar ebook #{$id_ebooks}: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // EXCLUSÃO DIRETA (DELETE)
    // =========================================================

    public function deletarEbook(int $id_ebooks): bool
    {
        $sql  = "DELETE FROM tbl_ebooks WHERE id_ebooks = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id_ebooks, PDO::PARAM_INT);

        try {
            return $stmt->execute();
        } catch (Exception $e) {
            error_log("ERRO ao deletar ebook #{$id_ebooks}: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // LEITURA (READ)
    // =========================================================

    public function buscarEbooks(): array
    {
        $sql = "SELECT * FROM tbl_ebooks
                ORDER BY id_ebooks DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dados as &$d) {
            $d['img_ebook'] = self::corrigirCaminhoImagem($d['img_ebook'] ?? null);
        }
        return $dados;
    }

    public function buscarEbookPorID(int $id_ebooks): array|false
    {
        $sql = "SELECT * FROM tbl_ebooks WHERE id_ebooks = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id_ebooks, PDO::PARAM_INT);
        $stmt->execute();
        $ebook = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($ebook) {
            $ebook['img_ebook'] = self::corrigirCaminhoImagem($ebook['img_ebook'] ?? null);
        }
        return $ebook;
    }

    public function buscarEbooksPorSelo(string $selo): array
    {
        $selosValidos = ['Mais Vendido', 'Novidade'];

        if (!in_array($selo, $selosValidos)) {
            error_log("ERRO: Selo inválido '{$selo}'. Use: Mais Vendido ou Novidade.");
            return [];
        }

        $sql = "SELECT * FROM tbl_ebooks
                WHERE selo_ebook = :selo
                ORDER BY nome_ebook ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':selo', $selo);
        $stmt->execute();
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dados as &$d) {
            $d['img_ebook'] = self::corrigirCaminhoImagem($d['img_ebook'] ?? null);
        }
        return $dados;
    }

    public function pesquisarEbooks(string $termo): array
    {
        $like = "%{$termo}%";
        $sql  = "SELECT id_ebooks, nome_ebook, autor_ebook, preco_ebook, preco_original, img_ebook
                 FROM tbl_ebooks
                 WHERE nome_ebook    LIKE :termo
                    OR autor_ebook   LIKE :termo
                    OR descricao_ebook LIKE :termo
                 ORDER BY nome_ebook ASC
                 LIMIT 20";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':termo', $like);
        $stmt->execute();
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dados as &$d) {
            $d['img_ebook'] = self::corrigirCaminhoImagem($d['img_ebook'] ?? null);
        }
        return $dados;
    }

    // =========================================================
    // PAGINAÇÃO
    // =========================================================

    public function paginacao(int $pagina = 1, int $por_pagina = 12, ?string $selo = null): array
    {
        $where  = "1 = 1";
        $params = [];

        if ($selo) {
            $where           .= " AND selo_ebook = :selo";
            $params[':selo']  = $selo;
        }

        // Total de registros
        $totalStmt = $this->db->prepare("SELECT COUNT(*) FROM tbl_ebooks WHERE {$where}");
        $totalStmt->execute($params);
        $total = (int) $totalStmt->fetchColumn();

        // Dados paginados
        $offset = ($pagina - 1) * $por_pagina;

        $sql = "SELECT * FROM tbl_ebooks
                WHERE {$where}
                ORDER BY id_ebooks DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit',  $por_pagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,     PDO::PARAM_INT);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($dados as &$d) {
            $d['img_ebook'] = self::corrigirCaminhoImagem($d['img_ebook'] ?? null);
        }

        $lastPage = (int) ceil($total / $por_pagina);

        return [
            'data'          => $dados,
            'total'         => $total,
            'por_pagina'    => $por_pagina,
            'pagina_atual'  => $pagina,
            'ultima_pagina' => $lastPage,
            'de'            => $total > 0 ? $offset + 1 : 0,
            'para'          => $offset + count($dados),
        ];
    }

    // =========================================================
    // CONTADORES (STATS)
    // =========================================================

    public function totalDeEbooks(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tbl_ebooks");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function totalPorSelo(string $selo): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM tbl_ebooks WHERE selo_ebook = :selo"
        );
        $stmt->bindParam(':selo', $selo);
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function totalEmPromocao(): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM tbl_ebooks WHERE preco_original IS NOT NULL"
        );
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    // =========================================================
    // HELPER — NORMALIZA CAMINHO DA IMAGEM
    // =========================================================

    public static function corrigirCaminhoImagem(?string $img): string
    {
        if (empty($img)) {
            return '/img/ebook-placeholder.webp';
        }

        // URL completa: retorna como está
        if (filter_var($img, FILTER_VALIDATE_URL)) {
            return $img;
        }

        // Já tem caminho base correto
        if (strpos($img, '/backend/uploads/') !== false) {
            return $img;
        }

        $caminhoLimpo = ltrim($img, '/');

        if (strpos($caminhoLimpo, 'uploads/') === 0) {
            return '/backend/' . $caminhoLimpo;
        }

        return '/backend/uploads/' . $caminhoLimpo;
    }
}