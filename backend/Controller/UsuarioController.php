<?php

namespace Datislopo\Ebook\Controllers;

use Datislopo\Ebook\Models\Usuario;
use Datislopo\Ebook\Database\Database;
use Datislopo\Ebook\Core\View;
use Datislopo\Ebook\Core\Flash;
use Datislopo\Ebook\Core\Redirect;
use Exception;

/**
 * Controller para gerenciar Usuários — CRUD com Views HTML
 */
class UsuarioController
{
    private Usuario $model;
    private $db;

    public function __construct()
    {
        $this->db    = Database::getInstance();
        $this->model = new Usuario($this->db);
    }

    // =========================================================
    // VIEWS — Renderizam páginas HTML
    // =========================================================

    /**
     * GET /backend/usuario — Listagem paginada de usuários
     */
    public function index(int $pagina = 1): void
    {
        try {
            if ($pagina <= 0) $pagina = 1;

            $dados         = $this->model->paginacao($pagina);
            $total         = $this->model->totalDeUsuarios();
            $totalAtivos   = $this->model->totalDeUsuariosAtivos();
            $totalInativos = $this->model->totalDeUsuariosInativos();

            View::render('usuario/index', [
                'tituloPagina'   => 'Usuários',
                'usuarios'       => $dados['data'],
                'total_usuarios' => $total,
                'total_ativos'   => $totalAtivos,
                'total_inativos' => $totalInativos,
                'paginacao'      => $dados,
            ]);
        } catch (Exception $e) {
            error_log('ERRO ao listar usuários: ' . $e->getMessage());
            Flash::set('erro', 'Erro ao carregar a listagem de usuários.');
            View::render('usuario/index', [
                'tituloPagina'   => 'Usuários',
                'usuarios'       => [],
                'total_usuarios' => 0,
                'total_ativos'   => 0,
                'total_inativos' => 0,
                'paginacao'      => [],
            ]);
        }
    }

    /**
     * GET /backend/usuario/criar — Formulário de cadastro
     */
    public function viewCriarUsuario(): void
    {
        View::render('usuario/criar', [
            'tituloPagina' => 'Novo Usuário',
        ]);
    }

    /**
     * GET /backend/usuario/editar/{id} — Formulário de edição
     */
    public function viewEditarUsuario(int $id): void
    {
        try {
            $usuario = $this->model->buscarUsuariosPorID($id);

            if (!$usuario) {
                Flash::set('erro', 'Usuário não encontrado.');
                Redirect::redirecionarPara(url('backend/usuario'));
                return;
            }

            View::render('usuario/editar', [
                'tituloPagina' => 'Editar Usuário',
                'usuario'      => $usuario,
            ]);
        } catch (Exception $e) {
            error_log('ERRO ao carregar edição de usuário: ' . $e->getMessage());
            Flash::set('erro', 'Erro ao carregar o usuário para edição.');
            Redirect::redirecionarPara(url('backend/usuario'));
        }
    }

    /**
     * GET /backend/usuario/deletar/{id} — Confirmação de desativação
     */
    public function viewDeletarUsuario(int $id): void
    {
        try {
            $usuario = $this->model->buscarUsuariosPorID($id);

            if (!$usuario) {
                Flash::set('erro', 'Usuário não encontrado.');
                Redirect::redirecionarPara(url('backend/usuario'));
                return;
            }

            View::render('usuario/deletar', [
                'tituloPagina' => 'Desativar Usuário',
                'usuario'      => $usuario,
            ]);
        } catch (Exception $e) {
            error_log('ERRO ao carregar desativação de usuário: ' . $e->getMessage());
            Flash::set('erro', 'Erro ao carregar o usuário.');
            Redirect::redirecionarPara(url('backend/usuario'));
        }
    }

    // =========================================================
    // AÇÕES — Processam formulários HTML (POST)
    // =========================================================

    /**
     * POST /backend/usuario/salvar — Cadastra novo usuário
     */
    public function cadastrar(): void
    {
        try {
            $erros = $this->validarCampos($_POST, ['nome_usuario', 'email_usuario', 'senha_usuario', 'tipo_usuario']);

            if (!empty($erros)) {
                Flash::set('erro', implode(' ', $erros));
                Redirect::redirecionarPara(url('backend/usuario/criar'));
                return;
            }

            // Validar e-mail duplicado
            $existente = $this->model->buscarUsuariosPorEMail(trim($_POST['email_usuario']));
            if (!empty($existente)) {
                Flash::set('erro', 'Já existe um usuário cadastrado com este e-mail.');
                Redirect::redirecionarPara(url('backend/usuario/criar'));
                return;
            }

            // Validar senha mínima
            if (strlen(trim($_POST['senha_usuario'])) < 6) {
                Flash::set('erro', 'A senha deve ter pelo menos 6 caracteres.');
                Redirect::redirecionarPara(url('backend/usuario/criar'));
                return;
            }

            $id = $this->model->inseriUsuario(
                trim($_POST['nome_usuario']),
                trim($_POST['email_usuario']),
                $_POST['senha_usuario'],
                $_POST['tipo_usuario']
            );

            if (!$id) {
                Flash::set('erro', 'Falha ao cadastrar o usuário.');
                Redirect::redirecionarPara(url('backend/usuario/criar'));
                return;
            }

            Flash::set('sucesso', "Usuário #{$id} cadastrado com sucesso!");
            Redirect::redirecionarPara(url('backend/usuario'));

        } catch (Exception $e) {
            error_log('ERRO ao cadastrar usuário: ' . $e->getMessage());
            Flash::set('erro', 'Erro ao cadastrar: ' . $e->getMessage());
            Redirect::redirecionarPara(url('backend/usuario/criar'));
        }
    }

    /**
     * POST /backend/usuario/atualizar — Atualiza usuário existente
     */
    public function atualizar(): void
    {
        try {
            $id    = (int) ($_POST['id_usuario'] ?? 0);
            $erros = $this->validarCampos($_POST, ['nome_usuario', 'email_usuario', 'tipo_usuario']);

            if ($id <= 0) {
                Flash::set('erro', 'ID inválido.');
                Redirect::redirecionarPara(url('backend/usuario'));
                return;
            }

            if (!empty($erros)) {
                Flash::set('erro', implode(' ', $erros));
                Redirect::redirecionarPara(url("backend/usuario/editar/{$id}"));
                return;
            }

            $existe = $this->model->buscarUsuariosPorID($id);
            if (!$existe) {
                Flash::set('erro', 'Usuário não encontrado.');
                Redirect::redirecionarPara(url('backend/usuario'));
                return;
            }

            // Validar senha se foi informada
            $senha = !empty($_POST['senha_usuario']) ? $_POST['senha_usuario'] : null;
            if ($senha !== null && strlen(trim($senha)) < 6) {
                Flash::set('erro', 'A nova senha deve ter pelo menos 6 caracteres.');
                Redirect::redirecionarPara(url("backend/usuario/editar/{$id}"));
                return;
            }

            $sucesso = $this->model->atualizarUsuario(
                $id,
                trim($_POST['nome_usuario']),
                trim($_POST['email_usuario']),
                $senha,
                $_POST['tipo_usuario']
            );

            if (!$sucesso) {
                Flash::set('erro', 'Falha ao atualizar o usuário.');
                Redirect::redirecionarPara(url("backend/usuario/editar/{$id}"));
                return;
            }

            Flash::set('sucesso', "Usuário #{$id} atualizado com sucesso!");
            Redirect::redirecionarPara(url('backend/usuario'));

        } catch (Exception $e) {
            error_log('ERRO ao atualizar usuário: ' . $e->getMessage());
            $id = (int) ($_POST['id_usuario'] ?? 0);
            Flash::set('erro', 'Erro ao atualizar: ' . $e->getMessage());
            Redirect::redirecionarPara(url("backend/usuario/editar/{$id}"));
        }
    }

    /**
     * POST /backend/usuario/deletar — Soft delete (desativa) usuário
     */
    public function deletar(): void
    {
        try {
            $id = (int) ($_POST['id_usuario'] ?? 0);

            if ($id <= 0) {
                Flash::set('erro', 'ID inválido.');
                Redirect::redirecionarPara(url('backend/usuario'));
                return;
            }

            if (!$this->model->buscarUsuariosPorID($id)) {
                Flash::set('erro', 'Usuário não encontrado.');
                Redirect::redirecionarPara(url('backend/usuario'));
                return;
            }

            if ($this->model->excluirUsuario($id)) {
                Flash::set('sucesso', "Usuário #{$id} desativado com sucesso!");
            } else {
                Flash::set('erro', 'Falha ao desativar o usuário.');
            }

            Redirect::redirecionarPara(url('backend/usuario'));

        } catch (Exception $e) {
            error_log('ERRO ao desativar usuário: ' . $e->getMessage());
            Flash::set('erro', 'Erro ao desativar: ' . $e->getMessage());
            Redirect::redirecionarPara(url('backend/usuario'));
        }
    }

    /**
     * POST /backend/usuario/ativar — Reativa usuário desativado
     */
    public function ativar(): void
    {
        try {
            $id = (int) ($_POST['id_usuario'] ?? 0);

            if ($id <= 0) {
                Flash::set('erro', 'ID inválido.');
                Redirect::redirecionarPara(url('backend/usuario'));
                return;
            }

            if ($this->model->ativarUsuario($id)) {
                Flash::set('sucesso', "Usuário #{$id} reativado com sucesso!");
            } else {
                Flash::set('erro', 'Falha ao reativar o usuário.');
            }

            Redirect::redirecionarPara(url('backend/usuario'));

        } catch (Exception $e) {
            error_log('ERRO ao reativar usuário: ' . $e->getMessage());
            Flash::set('erro', 'Erro ao reativar: ' . $e->getMessage());
            Redirect::redirecionarPara(url('backend/usuario'));
        }
    }

    // =========================================================
    // HELPERS PRIVADOS
    // =========================================================

    private function validarCampos(array $dados, array $campos): array
    {
        $erros = [];
        foreach ($campos as $campo) {
            if (!isset($dados[$campo]) || trim((string) $dados[$campo]) === '') {
                $labels = [
                    'nome_usuario'  => 'Nome',
                    'email_usuario' => 'E-mail',
                    'senha_usuario' => 'Senha',
                    'tipo_usuario'  => 'Perfil',
                ];
                $label   = $labels[$campo] ?? $campo;
                $erros[] = "O campo '{$label}' é obrigatório.";
            }
        }
        return $erros;
    }
}