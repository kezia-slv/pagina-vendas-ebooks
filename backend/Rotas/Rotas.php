<?php

namespace Datislopo\Ebook\Rotas;

class Rotas
{
    public static function get()
    {
        return [
            'GET' => [

                // ── Ebooks ────────────────────────────────────────────
                '/ebook'                          => 'EbookController@index',
                '/ebook/listar'                   => 'EbookController@listar',
                '/ebook/listar/{pagina}'          => 'EbookController@listar',
                '/ebook/criar'                    => 'EbookController@viewCriarEbook',
                '/ebook/editar/{id}'              => 'EbookController@viewEditarEbook',
                '/ebook/deletar/{id}'             => 'EbookController@viewDeletarEbook',
                '/ebook/pesquisar'                => 'EbookController@pesquisar',
                '/ebook/selo'                     => 'EbookController@buscarPorSelo',
                '/ebook/stats'                    => 'EbookController@stats',
                '/ebook/{id}'                     => 'EbookController@buscarPorId',

            ],

            'POST' => [

                // ── Ebooks ────────────────────────────────────────────
                '/ebook/salvar'                   => 'EbookController@cadastrar',
                '/ebook/atualizar'                => 'EbookController@atualizar',
                '/ebook/deletar'                  => 'EbookController@deletar',

            ],
        ];
    }
}