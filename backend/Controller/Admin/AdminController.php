<?php

namespace Ovos\Ebenezer\Controllers\Admin;

use Ovos\Ebenezer\Core\Session;
use Ovos\Ebenezer\Core\Redirect;

class AdminController
{
    protected Session $session;

    public function __construct()
    {
        $this->session = new Session();
        $this->verificarAutenticacao();
    }

    /**
     * Garante que só usuários logados com perfil Admin ou Funcionario acessem.
     * Redireciona para login caso contrário.
     */
    private function verificarAutenticacao(): void
    {
        if (!$this->session->has('usuario_id')) {
            Redirect::redirecionarPara('/login');
        }

        $perfisPermitidos = ['Admin', 'Funcionario'];
        $tipo = $this->session->get('usuario_tipo');

        if (!in_array($tipo, $perfisPermitidos)) {
            Redirect::redirecionarPara('/acesso-negado');
        }
    }
}