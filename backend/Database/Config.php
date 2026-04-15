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
                 'host' => 'sql211.infinityfree.com',
                'db_name' => 'if0_41664807_XXX',
                'username' => 'if0_41664807',
                'password' => 'ks200710',
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

