<?php
namespace Datislopo\Ebook\Core;
use Datislopo\Ebook\Core\Flash;

class Redirect
{
    /**
     * Redireciona para uma URL.
     * @param string $url
     */
    public static function redirecionarPara($url)
    {
        header("Location: " . $url);
        exit;
    }
    /**
     * Redireciona com mensagem Flash.
     * @param string $url
     * @param string $type
     * @param string $message
     */
    public static function redirecionarComMensagem($url, $type, $message)
    {
        Flash::set($type, $message);
        self::redirecionarPara($url);
    }
    /**
     * Retorna para a página anterior com mensagem.
     */
    public static function voltarPaginaAnteriorComMensagem($type, $message)
    {
        $url = $_SERVER['HTTP_REFERER'] ?? '/';
        self::redirecionarComMensagem($url, $type, $message);
    }
}
