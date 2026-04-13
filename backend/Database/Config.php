<?php
/**
 * Configuração do Sistema
 * Sebo Alfarrábio Dashboard
 * @version 2.1
 */

namespace Datislopo\Ebook\Database;
use PDO;
use PDOException;

// ================================
// CONFIGURAÇÃO PRINCIPAL (OBRIGATÓRIA)
// ================================
class Config
{
    public static function get()
    {
        return [
            'database' => array(
                'driver' => 'mysql',
                'mysql' => array(
                 'host' => '127.0.0.1',
                'db_name' => 'ebooks',
                'username' => 'root',
                'password' => '',
                'charset' => 'utf8',
                'port' =>  '3306',
                ),
            ),
            'app' => [
                'name'  => 'Datislopo Ebooks',
                'url'   => 'http://localhost/datislopo-ebooks',
                'email' => 'admin@datislopo.com'
            ]
        ];
    }
}

