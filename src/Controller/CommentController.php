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

    public function insertOne(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if (!isset($body['content']) || !is_string($body['content']) || trim($body['content']) === '') {
            $response->getBody()->write(json_encode(['error' => 'campo content obrigatorio']));
            return $response->withStatus(400);
        }

        if ($this->service->insertOne($args['id'], $adminUserId, $body['content'])) {
            return $response->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['error' => 'produto nao encontrado']));
            return $response->withStatus(404);
        }
    }


    public function insertReply(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if (!isset($body['content']) || !is_string($body['content']) || trim($body['content']) === '') {
            $response->getBody()->write(json_encode(['error' => 'campo content obrigatorio']));
            return $response->withStatus(400);
        }

        if ($this->service->insertReply($args['id'], $adminUserId, $body['content'])) {
            return $response->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['error' => 'comentario nao encontrado']));
            return $response->withStatus(404);
        }
    }

    public function insertLike(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        $result = $this->service->insertLike($args['id'], $adminUserId);

        if ($result === true) {
            return $response->withStatus(200);
        }
        if ($result === 'duplicado') {
            $response->getBody()->write(json_encode(['error' => 'comentario ja curtido por este usuario']));
            return $response->withStatus(400);
        }
        $response->getBody()->write(json_encode(['error' => 'comentario nao encontrado']));
        return $response->withStatus(404);
    }

    public function deleteOne(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        $result = $this->service->deleteOne($args['id'], $adminUserId);

        if ($result === true) {
            return $response->withStatus(200);
        }
        if ($result === 'proibido') {
            $response->getBody()->write(json_encode(['error' => 'so e possivel remover os proprios comentarios']));
            return $response->withStatus(403);
        }
        $response->getBody()->write(json_encode(['error' => 'comentario nao encontrado']));
        return $response->withStatus(404);
    }

    public function getByProduct(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $stm = $this->service->getByProduct($args['id']);
        if ($stm === false) {
            $response->getBody()->write(json_encode(['error' => 'produto nao encontrado']));
            return $response->withStatus(404);
        }
        $fetchedComments = $stm->fetchAll();

        $commentsById = [];
        foreach ($fetchedComments as $fetchedComment) {
            $fetchedComment->replies = [];
            $commentsById[$fetchedComment->id] = $fetchedComment;
        }

        $commentTree = [];
        foreach ($commentsById as $comment) {
            if ($comment->parent_id === null) {
                $commentTree[] = $comment;
            } elseif (isset($commentsById[$comment->parent_id])) {
                $commentsById[$comment->parent_id]->replies[] = $comment;
            }
        }

        $response->getBody()->write(json_encode($commentTree));
        return $response->withStatus(200);
    }
}
