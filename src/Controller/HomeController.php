<?php

namespace Contatoseguro\TesteBackend\Controller;


use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class HomeController
{
    public function __construct()
    {
    }

    public function home(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $response->getBody()->write('Hello World');
        return $response->withStatus(200);
    }
}
