<?php

namespace App\Config;

class DB
{
    public static \PDO $pdo;

    public static function connect()
    {
        if (!isset(self::$pdo)) {
            $dsn = 'mysql:host=db;dbname=product_catalog;charset=utf8mb4';
            self::$pdo = new \PDO($dsn, 'product_catalog', 'product_catalog', [
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
            ]);
        }

        return self::$pdo;
    }
}
