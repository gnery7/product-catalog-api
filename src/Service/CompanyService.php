<?php

namespace Contatoseguro\TesteBackend\Service;

use Contatoseguro\TesteBackend\Config\DB;

class CompanyService
{
    private \PDO $pdo;
    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo ?? DB::connect();
    }

    public function getNameById($id): \PDOStatement
    {
        $stm = $this->pdo->prepare("SELECT name FROM company WHERE id = :id");
        $stm->execute([':id' => $id]);

        return $stm;
    }
}
