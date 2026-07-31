<?php

namespace Contatoseguro\TesteBackend\Controller;

use Contatoseguro\TesteBackend\Config\DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CompanyController
{
    private \PDO $pdo;
    public function __construct(
    ) {
        $this->pdo = DB::connect();
    }

    public function getAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $stm = $this->pdo->prepare('SELECT * FROM company');
        $stm->execute();

        $response->getBody()->write(json_encode($stm->fetchAll()));
        return $response->withStatus(200);
    }

    public function getOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $stm = $this->pdo->prepare("SELECT * FROM company WHERE id = {$args['id']}");
        $stm->execute();

        $response->getBody()->write(json_encode($stm->fetch()));
        return $response->withStatus(200);
    }
}
