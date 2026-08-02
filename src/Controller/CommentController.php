<?php

namespace Contatoseguro\TesteBackend\Controller;

use Contatoseguro\TesteBackend\Service\CommentService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CommentController
{
    private CommentService $service;

    public function __construct()
    {
        $this->service = new CommentService();
    }

    public function insertOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if (!isset($body['content']) || !is_string($body['content']) || trim($body['content']) === '') {
            $response->getBody()->write(json_encode(['error' => 'campo content obrigatorio']));
            return $response->withStatus(400);
        }

        if ($this->service->insertOne($args['id'], $adminUserId, $body['content'])) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }

    public function insertReply(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if (!isset($body['content']) || !is_string($body['content']) || trim($body['content']) === '') {
            $response->getBody()->write(json_encode(['error' => 'campo content obrigatorio']));
            return $response->withStatus(400);
        }

        if ($this->service->insertReply($args['id'], $adminUserId, $body['content'])) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }

    public function insertLike(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        $result = $this->service->insertLike($args['id'], $adminUserId);

        if ($result === true) {
            return $response->withStatus(200);
        }
        if ($result === 'duplicado') {
            $response->getBody()->write(json_encode(['error' => 'comentario ja curtido por este usuario']));
            return $response->withStatus(400);
        }
        return $response->withStatus(404);
    }
}
