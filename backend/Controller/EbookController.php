<?php

namespace Datislopo\Ebook\Controllers;

use Datislopo\Ebook\Models\Ebook;
use Datislopo\Ebook\Core\View;
use Datislopo\Ebook\Core\Flash;
use Datislopo\Ebook\Core\Redirect;
use Datislopo\Ebook\Core\FileManager;
use Datislopo\Ebook\Database\Database;
use Exception;

/**
 * Controller para gerenciar Ebooks — CRUD com Views HTML
 */
class EbookController
{
    private Ebook $model;
    private FileManager $fileManager;

    public function __construct()
    {
        $db = Database::getInstance();
        $this->model = new Ebook($db);
        $this->fileManager = new FileManager(__DIR__ . '/../uploads');
    }

    // =========================================================
    // VIEWS — Renderizam páginas HTML
    // =========================================================

    /**
     * GET /ebook — Listagem de todos os ebooks
     */
    public function index(): void
    {
        try {
            $ebooks = $this->model->buscarEbooks();
            View::render('ebook/index', [
                'ebooks' => $ebooks,
                'tituloPagina' => 'Ebooks',
            ]);
        }
        catch (Exception $e) {
            error_log("ERRO ao carregar listagem: " . $e->getMessage());
            Flash::set('erro', 'Erro ao carregar a listagem de ebooks.');
            View::render('ebook/index', [
                'ebooks' => [],
                'tituloPagina' => 'Ebooks',
            ]);
        }
    }

    /**
     * GET /ebook/criar — Formulário de criação
     */
    public function viewCriarEbook(): void
    {
        View::render('ebook/criar', [
            'tituloPagina' => 'Novo Ebook',
        ]);
    }

    /**
     * GET /ebook/editar/{id} — Formulário de edição
     */
    public function viewEditarEbook(int $id): void
    {
        try {
            $ebook = $this->model->buscarEbookPorID($id);

            if (!$ebook) {
                Flash::set('erro', 'Ebook não encontrado.');
                Redirect::redirecionarPara(url('backend/ebook'));
                return;
            }

            View::render('ebook/editar', [
                'ebook' => $ebook,
                'tituloPagina' => 'Editar Ebook',
            ]);
        }
        catch (Exception $e) {
            error_log("ERRO ao carregar edição: " . $e->getMessage());
            Flash::set('erro', 'Erro ao carregar o ebook para edição.');
            Redirect::redirecionarPara(url('backend/ebook'));
        }
    }

    /**
     * GET /ebook/deletar/{id} — Confirmação de exclusão
     */
    public function viewDeletarEbook(int $id): void
    {
        try {
            $ebook = $this->model->buscarEbookPorID($id);

            if (!$ebook) {
                Flash::set('erro', 'Ebook não encontrado.');
                Redirect::redirecionarPara(url('backend/ebook'));
                return;
            }

            View::render('ebook/deletar', [
                'ebook' => $ebook,
                'tituloPagina' => 'Excluir Ebook',
            ]);
        }
        catch (Exception $e) {
            error_log("ERRO ao carregar exclusão: " . $e->getMessage());
            Flash::set('erro', 'Erro ao carregar o ebook para exclusão.');
            Redirect::redirecionarPara(url('backend/ebook'));
        }
    }

    // =========================================================
    // AÇÕES — Processam formulários HTML (POST)
    // =========================================================

    /**
     * POST /ebook/salvar — Cadastra um novo ebook
     */
    public function cadastrar(): void
    {
        try {
            // Validação campos obrigatórios
            $erros = $this->validarCamposObrigatorios($_POST, [
                'nome_ebook',
                'descricao_ebook',
                'preco_ebook',
                'link_pagamento',
            ]);

            if (!empty($erros)) {
                Flash::set('erro', implode(' ', $erros));
                Redirect::redirecionarPara(url('backend/ebook/criar'));
                return;
            }

            // Validar e processar imagem
            if (empty($_FILES['img_ebook']) || $_FILES['img_ebook']['error'] === UPLOAD_ERR_NO_FILE) {
                Flash::set('erro', 'A capa do ebook é obrigatória.');
                Redirect::redirecionarPara(url('backend/ebook/criar'));
                return;
            }

            $caminhoImagem = $this->fileManager->salvarArquivo($_FILES['img_ebook'], 'capas');

            // Montar dados
            $dados = $this->sanitizarDados($_POST, [
                'nome_ebook',
                'autor_ebook',
                'descricao_ebook',
                'preco_ebook',
                'preco_original',
                'link_pagamento',
                'selo_ebook',
            ]);

            $dados['img_ebook'] = $caminhoImagem;

            $idGerado = $this->model->inserirEbook($dados);

            if (!$idGerado) {
                Flash::set('erro', 'Falha ao cadastrar o ebook.');
                Redirect::redirecionarPara(url('backend/ebook/criar'));
                return;
            }

            Flash::set('sucesso', 'Ebook cadastrado com sucesso!');
            Redirect::redirecionarPara(url('backend/ebook'));

        }
        catch (Exception $e) {
            error_log("ERRO ao cadastrar ebook: " . $e->getMessage());
            Flash::set('erro', 'Erro ao cadastrar: ' . $e->getMessage());
            Redirect::redirecionarPara(url('backend/ebook/criar'));
        }
    }

    /**
     * POST /ebook/atualizar — Atualiza ebook existente
     */
    public function atualizar(): void
    {
        try {
            $id = (int)($_POST['id_ebooks'] ?? 0);

            if ($id <= 0) {
                Flash::set('erro', 'ID inválido.');
                Redirect::redirecionarPara(url('backend/ebook'));
                return;
            }

            $existe = $this->model->buscarEbookPorID($id);
            if (!$existe) {
                Flash::set('erro', 'Ebook não encontrado.');
                Redirect::redirecionarPara(url('backend/ebook'));
                return;
            }

            // Sanitiza dados do formulário
            $dados = $this->sanitizarDados($_POST, [
                'nome_ebook',
                'autor_ebook',
                'descricao_ebook',
                'preco_ebook',
                'preco_original',
                'link_pagamento',
                'selo_ebook',
            ]);

            // Processar nova imagem (se enviada)
            if (!empty($_FILES['img_ebook']) && $_FILES['img_ebook']['error'] === UPLOAD_ERR_OK) {
                $caminhoImagem = $this->fileManager->salvarArquivo($_FILES['img_ebook'], 'capas');
                $dados['img_ebook'] = $caminhoImagem;

                // Deletar imagem antiga
                $imgAntiga = $existe['img_ebook'] ?? null;
                if ($imgAntiga && strpos($imgAntiga, '/backend/uploads/') !== false) {
                    $caminhoRelativo = str_replace('/backend/uploads/', '', $imgAntiga);
                    $this->fileManager->deletar($caminhoRelativo);
                }
            }

            $atualizado = $this->model->atualizarEbook($id, $dados);

            if (!$atualizado) {
                Flash::set('erro', 'Falha ao atualizar o ebook.');
                Redirect::redirecionarPara(url('backend/ebook/editar/' . $id));
                return;
            }

            Flash::set('sucesso', 'Ebook atualizado com sucesso!');
            Redirect::redirecionarPara(url('backend/ebook'));

        }
        catch (Exception $e) {
            error_log("ERRO ao atualizar ebook: " . $e->getMessage());
            $id = (int)($_POST['id_ebooks'] ?? 0);
            Flash::set('erro', 'Erro ao atualizar: ' . $e->getMessage());
            Redirect::redirecionarPara(url('backend/ebook/editar/' . $id));
        }
    }

    /**
     * POST /ebook/deletar — Remove ebook permanentemente
     */
    public function deletar(): void
    {
        try {
            $id = (int)($_POST['id_ebooks'] ?? 0);

            if ($id <= 0) {
                Flash::set('erro', 'ID inválido.');
                Redirect::redirecionarPara(url('backend/ebook'));
                return;
            }

            $existe = $this->model->buscarEbookPorID($id);
            if (!$existe) {
                Flash::set('erro', 'Ebook não encontrado.');
                Redirect::redirecionarPara(url('backend/ebook'));
                return;
            }

            // Deletar imagem do disco
            $img = $existe['img_ebook'] ?? null;
            if ($img && strpos($img, '/backend/uploads/') !== false) {
                $caminhoRelativo = str_replace('/backend/uploads/', '', $img);
                $this->fileManager->deletar($caminhoRelativo);
            }

            $deletado = $this->model->deletarEbook($id);

            if (!$deletado) {
                Flash::set('erro', 'Falha ao excluir o ebook.');
                Redirect::redirecionarPara(url('backend/ebook'));
                return;
            }

            Flash::set('sucesso', "Ebook '{$existe['nome_ebook']}' excluído com sucesso!");
            Redirect::redirecionarPara(url('backend/ebook'));

        }
        catch (Exception $e) {
            error_log("ERRO ao deletar ebook: " . $e->getMessage());
            Flash::set('erro', 'Erro ao excluir: ' . $e->getMessage());
            Redirect::redirecionarPara(url('backend/ebook'));
        }
    }

    // =========================================================
    // API JSON (mantidas para uso futuro/frontend)
    // =========================================================

    /**
     * GET /ebook/listar — Retorna JSON com ebooks (paginação opcional)
     */
    public function listar(): void
    {
        try {
            $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : null;
            $porPagina = isset($_GET['por_pagina']) ? (int)$_GET['por_pagina'] : 12;
            $selo = $_GET['selo'] ?? null;

            if ($pagina !== null) {
                $resultado = $this->model->paginacao($pagina, $porPagina, $selo);
                $this->responder(200, 'Ebooks listados com sucesso.', $resultado);
                return;
            }

            $ebooks = $this->model->buscarEbooks();
            $this->responder(200, 'Ebooks listados com sucesso.', $ebooks);

        }
        catch (Exception $e) {
            $this->responderErro(500, 'Erro ao listar ebooks.', $e->getMessage());
        }
    }

    /**
     * GET /ebook/{id} — Retorna JSON de um ebook
     */
    public function buscarPorId(int $id): void
    {
        try {
            if ($id <= 0) {
                $this->responderErro(400, 'ID inválido.');
                return;
            }
            $ebook = $this->model->buscarEbookPorID($id);
            if (!$ebook) {
                $this->responderErro(404, 'Ebook não encontrado.');
                return;
            }
            $this->responder(200, 'Ebook encontrado.', $ebook);
        }
        catch (Exception $e) {
            $this->responderErro(500, 'Erro ao buscar ebook.', $e->getMessage());
        }
    }

    /**
     * GET /ebook/pesquisar?termo=...
     */
    public function pesquisar(): void
    {
        try {
            $termo = trim($_GET['termo'] ?? '');
            if (empty($termo)) {
                $this->responderErro(400, 'Informe um termo para pesquisa.');
                return;
            }
            if (strlen($termo) < 2) {
                $this->responderErro(400, 'O termo deve ter pelo menos 2 caracteres.');
                return;
            }
            $resultado = $this->model->pesquisarEbooks($termo);
            $this->responder(200, 'Pesquisa realizada com sucesso.', $resultado);
        }
        catch (Exception $e) {
            $this->responderErro(500, 'Erro ao pesquisar ebooks.', $e->getMessage());
        }
    }

    /**
     * GET /ebook/selo?tipo=...
     */
    public function buscarPorSelo(): void
    {
        try {
            $selo = trim($_GET['tipo'] ?? '');
            $selosValidos = ['Mais Vendido', 'Novidade'];

            if (empty($selo)) {
                $this->responderErro(400, 'Informe o selo.');
                return;
            }
            if (!in_array($selo, $selosValidos)) {
                $this->responderErro(400, 'Selo inválido.');
                return;
            }
            $ebooks = $this->model->buscarEbooksPorSelo($selo);
            $this->responder(200, "Ebooks com selo '{$selo}' listados.", $ebooks);
        }
        catch (Exception $e) {
            $this->responderErro(500, 'Erro ao buscar ebooks por selo.', $e->getMessage());
        }
    }

    /**
     * GET /ebook/stats
     */
    public function stats(): void
    {
        try {
            $dados = [
                'total_ebooks' => $this->model->totalDeEbooks(),
                'total_mais_vendido' => $this->model->totalPorSelo('Mais Vendido'),
                'total_novidade' => $this->model->totalPorSelo('Novidade'),
                'total_em_promocao' => $this->model->totalEmPromocao(),
            ];
            $this->responder(200, 'Estatísticas geradas com sucesso.', $dados);
        }
        catch (Exception $e) {
            $this->responderErro(500, 'Erro ao gerar estatísticas.', $e->getMessage());
        }
    }

    // =========================================================
    // HELPERS PRIVADOS
    // =========================================================

    private function validarCamposObrigatorios(array $dados, array $campos): array
    {
        $erros = [];
        foreach ($campos as $campo) {
            if (!isset($dados[$campo]) || $dados[$campo] === '' || $dados[$campo] === null) {
                $erros[] = "O campo '{$campo}' é obrigatório.";
            }
        }
        return $erros;
    }

    private function sanitizarDados(array $dados, array $camposPermitidos): array
    {
        $resultado = [];
        foreach ($camposPermitidos as $campo) {
            if (array_key_exists($campo, $dados)) {
                $valor = $dados[$campo];
                if (is_string($valor)) {
                    $valor = trim($valor);
                }
                $opcionais = ['autor_ebook', 'preco_original', 'selo_ebook'];
                if (in_array($campo, $opcionais) && $valor === '') {
                    $valor = null;
                }
                $resultado[$campo] = $valor;
            }
        }
        return $resultado;
    }

    private function responder(int $status, string $mensagem, mixed $dados = null): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        $resposta = ['sucesso' => true, 'mensagem' => $mensagem];
        if ($dados !== null) {
            $resposta['dados'] = $dados;
        }
        echo json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function responderErro(int $status, string $mensagem, mixed $detalhes = null): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        $resposta = ['sucesso' => false, 'mensagem' => $mensagem];
        if ($detalhes !== null) {
            $resposta['detalhes'] = $detalhes;
        }
        echo json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}