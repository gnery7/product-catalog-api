<?php

namespace Contatoseguro\TesteBackend\Config;

class DB
{
    public static \PDO $pdo;

    public static function connect()
    {
        $dbPath = dirname(__DIR__, 2) . '/db/db.sqlite3';

        if (!isset(self::$pdo))
            self::$pdo = new \PDO('sqlite:' . $dbPath, null, null, [
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ
            ]);

        return self::$pdo;
    }
}
