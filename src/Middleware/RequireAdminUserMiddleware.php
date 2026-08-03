<?php

namespace Contatoseguro\TesteBackend\Middleware;

use Contatoseguro\TesteBackend\Config\DB;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;

class RequireAdminUserMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0] ?? null;

        if ($adminUserId === null || !ctype_digit((string)$adminUserId)) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'header admin_user_id ausente ou invalido']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $pdo = DB::connect();
        $stm = $pdo->prepare('SELECT id FROM admin_user WHERE id = :id');
        $stm->execute([':id' => $adminUserId]);
        if ($stm->fetch() === false) {
            $response = new Response();
            $response->getBody()->write(json_encode(['error' => 'usuario admin_user_id nao encontrado']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        return $handler->handle($request);
    }
}
