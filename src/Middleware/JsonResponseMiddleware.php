<?php

namespace Contatoseguro\TesteBackend\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class JsonResponseMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        $response = $handler->handle($request);
        return $response->withAddedHeader("Content-Type", "application/json");
    }
}
