<?php
namespace Datislopo\Ebook\Core;
class Flash
{
    /**
     * Define uma mensagem Flash.
     * @param string $type Tipo da mensagem (sucesso, erro, admin_error, etc)
     * @param string $message Conteúdo da mensagem
     */
    public static function set($type, $message)
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];

    }

    /**
     * Retorna a mensagem Flash atual e a remove da sessão.
     * @return array|null
     */
    public static function get()
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;

        }
        return null;
    }
}