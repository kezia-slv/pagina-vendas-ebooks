<?php

namespace Datislopo\Ebook\Rotas;

class Rotas
{
    public static function get()
    {
        return [
            'GET' => [

                // ── Ebooks ────────────────────────────────────────────
                '/ebook' => 'EbookController@index',
                '/ebook/listar' => 'EbookController@listar',
                '/ebook/listar/{pagina}' => 'EbookController@listar',
                '/ebook/criar' => 'EbookController@viewCriarEbook',
                '/ebook/editar/{id}' => 'EbookController@viewEditarEbook',
                '/ebook/deletar/{id}' => 'EbookController@viewDeletarEbook',
                '/ebook/pesquisar' => 'EbookController@pesquisar',
                '/ebook/selo' => 'EbookController@buscarPorSelo',
                '/ebook/stats' => 'EbookController@stats',
                '/ebook/{id}' => 'EbookController@buscarPorId',

                // ── Usuários ──────────────────────────────────────────
                '/usuario' => 'UsuarioController@index',
                '/usuario/{pagina}' => 'UsuarioController@index',
                '/usuario/criar' => 'UsuarioController@viewCriarUsuario',
                '/usuario/editar/{id}' => 'UsuarioController@viewEditarUsuario',
                '/usuario/deletar/{id}' => 'UsuarioController@viewDeletarUsuario',

                // ── Auth ──────────────────────────────────────────────
                '/login' => 'AuthController@login',
                '/register' => 'AuthController@register',
                '/logout' => 'AuthController@logout',
                '/forgot-password' => 'AuthController@forgotPassword',
                '/redefinir-senha' => 'AuthController@resetPassword',
                '/check-session' => 'AuthController@checkSession',

            ],

            'POST' => [

                // ── Ebooks ────────────────────────────────────────────
                '/ebook/salvar' => 'EbookController@cadastrar',
                '/ebook/atualizar' => 'EbookController@atualizar',
                '/ebook/deletar' => 'EbookController@deletar',

                // ── Usuários ──────────────────────────────────────────
                '/usuario/salvar' => 'UsuarioController@cadastrar',
                '/usuario/atualizar' => 'UsuarioController@atualizar',
                '/usuario/deletar' => 'UsuarioController@deletar',
                '/usuario/ativar' => 'UsuarioController@ativar',

                // ── Auth ──────────────────────────────────────────────
                '/login' => 'AuthController@authenticar',
                '/register' => 'AuthController@cadastrarUsuario',
                '/esqueci-senha/enviar' => 'AuthController@enviarLinkReset',
                '/redefinir-senha/processar' => 'AuthController@processarResetPassword',

            ],
        ];
    }
}