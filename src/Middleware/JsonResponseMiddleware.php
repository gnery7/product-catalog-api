<?php

namespace Contatoseguro\TesteBackend\Middleware;

use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;


class JsonResponseMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);
        return $response->withAddedHeader("Content-Type", "application/json");
    }
}
