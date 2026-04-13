<?php
namespace Ovos\Ebenezer\Controllers\Admin;

use Ovos\Ebenezer\Core\Session;
use Ovos\Ebenezer\Core\Redirect;

abstract class AuthenticatedController
{
    protected Session $session;
    public function __construct()
    {
        $this->session = new Session();
        if (!$this->session->has('usuario_id')) {
            Redirect::redirecionarComMensagem(
                '/backend/login',
                'error',
                'Você precisa estar logado para acessar esta página.'
            );
        }

        // Bloqueia acesso ao painel admin se o usuário for apenas Cliente
        if ($this->session->get('usuario_tipo') === 'Cliente') {
            Redirect::redirecionarComMensagem(
                '/', // Redireciona para a home pública
                'error',
                'Acesso restrito: Sua conta não tem permissões de administrador.'
            );
        }
    }
}
