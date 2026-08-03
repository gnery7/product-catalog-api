<?php

namespace Contatoseguro\TesteBackend\Config;

class DB
{
    public static \PDO $pdo;

    public static function connect()
    {
        if (!isset(self::$pdo)) {
            self::$pdo = new \PDO('mysql:host=db;dbname=teste_backend;charset=utf8mb4', 'teste_backend', 'teste_backend', [
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
            ]);
        }

        return self::$pdo;
    }
}
