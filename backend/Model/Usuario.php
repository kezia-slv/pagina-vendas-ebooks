<?php

namespace Datislopo\Ebook\Models;

use PDO;
use PDOException;
use Exception;

/**
 * Model para gerenciar a tabela tbl_usuario
 * Schema: id_usuario, nome_usuario, email_usuario, senha_usuario,
 *         tipo_usuario, criado_em, atualizado_em, excluido_em
 */
class Usuario
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // =========================================================
    // LEITURA (READ)
    // =========================================================

    public function buscarUsuarios(): array
    {
        $sql  = "SELECT * FROM tbl_usuario WHERE excluido_em IS NULL ORDER BY id_usuario DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function buscarUsuariosPorID(int $id): array|false
    {
        $sql  = "SELECT * FROM tbl_usuario WHERE id_usuario = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function buscarUsuariosPorEMail(string $email): array
    {
        $sql  = "SELECT * FROM tbl_usuario WHERE email_usuario = :email AND excluido_em IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function paginacao(int $pagina = 1, int $por_pagina = 10): array
    {
        $totalStmt = $this->db->prepare("SELECT COUNT(*) FROM tbl_usuario WHERE excluido_em IS NULL");
        $totalStmt->execute();
        $total = (int) $totalStmt->fetchColumn();

        $offset = ($pagina - 1) * $por_pagina;

        $sql  = "SELECT * FROM tbl_usuario
                 WHERE excluido_em IS NULL
                 ORDER BY id_usuario DESC
                 LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit',  $por_pagina, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,     PDO::PARAM_INT);
        $stmt->execute();
        $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

    public function totalDeUsuarios(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tbl_usuario");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function totalDeUsuariosAtivos(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tbl_usuario WHERE excluido_em IS NULL");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function totalDeUsuariosInativos(): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tbl_usuario WHERE excluido_em IS NOT NULL");
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    // =========================================================
    // CRIAÇÃO (CREATE)
    // =========================================================

    public function inseriUsuario(string $nome, string $email, string $senha, string $tipo = 'Cliente'): int|false
    {
        $tiposValidos = ['Cliente', 'Funcionario', 'Motorista', 'Admin'];
        if (!in_array($tipo, $tiposValidos)) {
            error_log("ERRO: Tipo '{$tipo}' inválido.");
            return false;
        }

        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO tbl_usuario (nome_usuario, email_usuario, senha_usuario, tipo_usuario)
                VALUES (:nome, :email, :senha, :tipo)";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':nome',  $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':senha', $senhaHash);
            $stmt->bindParam(':tipo',  $tipo);

            if ($stmt->execute()) {
                return (int) $this->db->lastInsertId();
            }
            return false;
        } catch (PDOException $e) {
            error_log("ERRO PDO ao inserir usuário: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // ATUALIZAÇÃO (UPDATE)
    // =========================================================

    public function atualizarUsuario(int $id, string $nome, string $email, ?string $senha, string $tipo): bool
    {
        $tiposValidos = ['Cliente', 'Funcionario', 'Motorista', 'Admin'];
        if (!in_array($tipo, $tiposValidos)) {
            error_log("ERRO: Tipo '{$tipo}' inválido.");
            return false;
        }

        try {
            if (!empty($senha)) {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $sql  = "UPDATE tbl_usuario
                         SET nome_usuario = :nome, email_usuario = :email,
                             senha_usuario = :senha, tipo_usuario = :tipo
                         WHERE id_usuario = :id";
                $stmt = $this->db->prepare($sql);
                $stmt->bindParam(':senha', $senhaHash);
            } else {
                $sql  = "UPDATE tbl_usuario
                         SET nome_usuario = :nome, email_usuario = :email, tipo_usuario = :tipo
                         WHERE id_usuario = :id";
                $stmt = $this->db->prepare($sql);
            }

            $stmt->bindParam(':id',   $id,    PDO::PARAM_INT);
            $stmt->bindParam(':nome', $nome);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':tipo', $tipo);

            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("ERRO PDO ao atualizar usuário #{$id}: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // EXCLUSÃO SOFT DELETE
    // =========================================================

    public function excluirUsuario(int $id): bool
    {
        $agora = date('Y-m-d H:i:s');
        $sql   = "UPDATE tbl_usuario SET excluido_em = :agora WHERE id_usuario = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':agora', $agora);
            $stmt->bindParam(':id',    $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("ERRO PDO ao desativar usuário #{$id}: " . $e->getMessage());
            return false;
        }
    }

    public function ativarUsuario(int $id): bool
    {
        $sql = "UPDATE tbl_usuario SET excluido_em = NULL WHERE id_usuario = :id";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            return $stmt->execute();
        } catch (PDOException $e) {
            error_log("ERRO PDO ao reativar usuário #{$id}: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================
    // AUTENTICAÇÃO
    // =========================================================

    public function checarCredenciais(string $email, string $senha): array|false
    {
        $usuarios = $this->buscarUsuariosPorEMail($email);

        if (count($usuarios) !== 1) {
            return false;
        }

        $usuario = $usuarios[0];

        if (password_verify($senha, $usuario['senha_usuario'])) {
            return $usuario;
        }

        return false;
    }
}