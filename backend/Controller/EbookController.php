<?php

namespace Datislopo\Ebook\Controllers;

use Datislopo\Ebook\Models\Ebook;
use Exception;

/**
 * Controller para gerenciar as requisições relacionadas a Ebooks
 * Métodos disponíveis:
 *   listar()          → GET    todos os ebooks (com paginação opcional)
 *   buscarPorId()     → GET    um ebook pelo ID
 *   pesquisar()       → GET    busca por termo
 *   buscarPorSelo()   → GET    filtra por selo (Mais Vendido | Novidade)
 *   cadastrar()       → POST   insere novo ebook
 *   atualizar()       → PUT    atualiza ebook existente
 *   deletar()         → DELETE remove ebook permanentemente
 *   stats()           → GET    totais e contadores
 */
class EbookController
{
    private Ebook $model;

    public function __construct($db)
    {
        $this->model = new Ebook($db);
    }

    // =========================================================
    // LISTAR — GET /ebooks
    // Query params opcionais:
    //   ?pagina=1 &por_pagina=12 &selo=Novidade
    // =========================================================

    public function listar(): void
    {
        try {
            $pagina    = isset($_GET['pagina'])    ? (int) $_GET['pagina']    : null;
            $porPagina = isset($_GET['por_pagina']) ? (int) $_GET['por_pagina'] : 12;
            $selo      = $_GET['selo'] ?? null;

            // Com paginação
            if ($pagina !== null) {
                $resultado = $this->model->paginacao($pagina, $porPagina, $selo);
                $this->responder(200, 'Ebooks listados com sucesso.', $resultado);
                return;
            }

            // Sem paginação: retorna todos
            $ebooks = $this->model->buscarEbooks();
            $this->responder(200, 'Ebooks listados com sucesso.', $ebooks);

        } catch (Exception $e) {
            $this->responderErro(500, 'Erro ao listar ebooks.', $e->getMessage());
        }
    }

    // =========================================================
    // BUSCAR POR ID — GET /ebooks/{id}
    // =========================================================

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

        } catch (Exception $e) {
            $this->responderErro(500, 'Erro ao buscar ebook.', $e->getMessage());
        }
    }

    // =========================================================
    // PESQUISAR — GET /ebooks/pesquisar?termo=perdão
    // =========================================================

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

        } catch (Exception $e) {
            $this->responderErro(500, 'Erro ao pesquisar ebooks.', $e->getMessage());
        }
    }

    // =========================================================
    // BUSCAR POR SELO — GET /ebooks/selo?tipo=Novidade
    // =========================================================

    public function buscarPorSelo(): void
    {
        try {
            $selo = trim($_GET['tipo'] ?? '');
            $selosValidos = ['Mais Vendido', 'Novidade'];

            if (empty($selo)) {
                $this->responderErro(400, 'Informe o selo. Valores aceitos: Mais Vendido, Novidade.');
                return;
            }

            if (!in_array($selo, $selosValidos)) {
                $this->responderErro(400, 'Selo inválido. Use: Mais Vendido ou Novidade.');
                return;
            }

            $ebooks = $this->model->buscarEbooksPorSelo($selo);
            $this->responder(200, "Ebooks com selo '{$selo}' listados.", $ebooks);

        } catch (Exception $e) {
            $this->responderErro(500, 'Erro ao buscar ebooks por selo.', $e->getMessage());
        }
    }

    // =========================================================
    // CADASTRAR — POST /ebooks
    // Body JSON esperado:
    // {
    //   "nome_ebook": "...",
    //   "descricao_ebook": "...",
    //   "img_ebook": "nome.jpg",
    //   "preco_ebook": 29.90,
    //   "link_pagamento": "https://...",
    //   "autor_ebook": "...",       (opcional)
    //   "preco_original": 49.90,   (opcional)
    //   "selo_ebook": "Novidade"   (opcional)
    // }
    // =========================================================

    public function cadastrar(): void
    {
        try {
            $body = $this->lerBody();

            $erros = $this->validarCamposObrigatorios($body, [
                'nome_ebook',
                'descricao_ebook',
                'img_ebook',
                'preco_ebook',
                'link_pagamento',
            ]);

            if (!empty($erros)) {
                $this->responderErro(422, 'Campos obrigatórios ausentes.', $erros);
                return;
            }

            if (!is_numeric($body['preco_ebook']) || $body['preco_ebook'] < 0) {
                $this->responderErro(422, 'O campo preco_ebook deve ser um número positivo.');
                return;
            }

            if (
                isset($body['preco_original']) &&
                !is_numeric($body['preco_original'])
            ) {
                $this->responderErro(422, 'O campo preco_original deve ser um número.');
                return;
            }

            if (
                isset($body['selo_ebook']) &&
                !in_array($body['selo_ebook'], ['Mais Vendido', 'Novidade'])
            ) {
                $this->responderErro(422, 'selo_ebook inválido. Use: Mais Vendido ou Novidade.');
                return;
            }

            $dados = $this->sanitizarDados($body, [
                'nome_ebook',
                'autor_ebook',
                'descricao_ebook',
                'img_ebook',
                'preco_original',
                'preco_ebook',
                'link_pagamento',
                'selo_ebook',
            ]);

            $idGerado = $this->model->inserirEbook($dados);

            if (!$idGerado) {
                $this->responderErro(500, 'Falha ao cadastrar o ebook.');
                return;
            }

            $novoEbook = $this->model->buscarEbookPorID($idGerado);
            $this->responder(201, 'Ebook cadastrado com sucesso.', $novoEbook);

        } catch (Exception $e) {
            $this->responderErro(500, 'Erro ao cadastrar ebook.', $e->getMessage());
        }
    }

    // =========================================================
    // ATUALIZAR — PUT /ebooks/{id}
    // Body JSON: apenas os campos que deseja alterar
    // =========================================================

    public function atualizar(int $id): void
    {
        try {
            if ($id <= 0) {
                $this->responderErro(400, 'ID inválido.');
                return;
            }

            $existe = $this->model->buscarEbookPorID($id);
            if (!$existe) {
                $this->responderErro(404, 'Ebook não encontrado.');
                return;
            }

            $body = $this->lerBody();

            if (empty($body)) {
                $this->responderErro(400, 'Nenhum dado enviado para atualização.');
                return;
            }

            // Valida campos numéricos se enviados
            if (isset($body['preco_ebook']) && !is_numeric($body['preco_ebook'])) {
                $this->responderErro(422, 'O campo preco_ebook deve ser um número.');
                return;
            }

            if (isset($body['preco_original']) && !is_numeric($body['preco_original'])) {
                $this->responderErro(422, 'O campo preco_original deve ser um número.');
                return;
            }

            if (
                isset($body['selo_ebook']) &&
                !in_array($body['selo_ebook'], ['Mais Vendido', 'Novidade', null])
            ) {
                $this->responderErro(422, 'selo_ebook inválido. Use: Mais Vendido, Novidade ou null.');
                return;
            }

            // Sanitiza apenas os campos permitidos
            $dados = $this->sanitizarDados($body, [
                'nome_ebook',
                'autor_ebook',
                'descricao_ebook',
                'img_ebook',
                'preco_original',
                'preco_ebook',
                'link_pagamento',
                'selo_ebook',
            ]);

            $atualizado = $this->model->atualizarEbook($id, $dados);

            if (!$atualizado) {
                $this->responderErro(500, 'Falha ao atualizar o ebook.');
                return;
            }

            $ebookAtualizado = $this->model->buscarEbookPorID($id);
            $this->responder(200, 'Ebook atualizado com sucesso.', $ebookAtualizado);

        } catch (Exception $e) {
            $this->responderErro(500, 'Erro ao atualizar ebook.', $e->getMessage());
        }
    }

    // =========================================================
    // DELETAR — DELETE /ebooks/{id}
    // =========================================================

    public function deletar(int $id): void
    {
        try {
            if ($id <= 0) {
                $this->responderErro(400, 'ID inválido.');
                return;
            }

            $existe = $this->model->buscarEbookPorID($id);
            if (!$existe) {
                $this->responderErro(404, 'Ebook não encontrado.');
                return;
            }

            $deletado = $this->model->deletarEbook($id);

            if (!$deletado) {
                $this->responderErro(500, 'Falha ao deletar o ebook.');
                return;
            }

            $this->responder(200, "Ebook '{$existe['nome_ebook']}' deletado com sucesso.");

        } catch (Exception $e) {
            $this->responderErro(500, 'Erro ao deletar ebook.', $e->getMessage());
        }
    }

    // =========================================================
    // STATS — GET /ebooks/stats
    // =========================================================

    public function stats(): void
    {
        try {
            $dados = [
                'total_ebooks'       => $this->model->totalDeEbooks(),
                'total_mais_vendido' => $this->model->totalPorSelo('Mais Vendido'),
                'total_novidade'     => $this->model->totalPorSelo('Novidade'),
                'total_em_promocao'  => $this->model->totalEmPromocao(),
            ];

            $this->responder(200, 'Estatísticas geradas com sucesso.', $dados);

        } catch (Exception $e) {
            $this->responderErro(500, 'Erro ao gerar estatísticas.', $e->getMessage());
        }
    }

    // =========================================================
    // HELPERS PRIVADOS
    // =========================================================

    /**
     * Lê e decodifica o body JSON da requisição
     */
    private function lerBody(): array
    {
        $raw = file_get_contents('php://input');
        return json_decode($raw, true) ?? [];
    }

    /**
     * Verifica se os campos obrigatórios estão presentes e preenchidos
     */
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

    /**
     * Filtra o array de entrada mantendo apenas os campos permitidos
     */
    private function sanitizarDados(array $dados, array $camposPermitidos): array
    {
        $resultado = [];
        foreach ($camposPermitidos as $campo) {
            if (array_key_exists($campo, $dados)) {
                $valor = $dados[$campo];

                // Strings: remove espaços extras
                if (is_string($valor)) {
                    $valor = trim($valor);
                }

                // Campos opcionais que chegam vazios viram null
                $opcionais = ['autor_ebook', 'preco_original', 'selo_ebook'];
                if (in_array($campo, $opcionais) && $valor === '') {
                    $valor = null;
                }

                $resultado[$campo] = $valor;
            }
        }
        return $resultado;
    }

    /**
     * Envia resposta JSON padronizada de sucesso
     */
    private function responder(int $status, string $mensagem, mixed $dados = null): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        $resposta = [
            'sucesso'  => true,
            'mensagem' => $mensagem,
        ];

        if ($dados !== null) {
            $resposta['dados'] = $dados;
        }

        echo json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Envia resposta JSON padronizada de erro
     */
    private function responderErro(int $status, string $mensagem, mixed $detalhes = null): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        $resposta = [
            'sucesso'  => false,
            'mensagem' => $mensagem,
        ];

        if ($detalhes !== null) {
            $resposta['detalhes'] = $detalhes;
        }

        echo json_encode($resposta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}