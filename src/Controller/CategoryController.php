<?php

namespace App\Controller;

use App\Service\CategoryService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CategoryController
{
    private CategoryService $service;

    public function __construct()
    {
        $this->service = new CategoryService();
    }

    public function getAll(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $adminUserId = $request->getHeader('admin_user_id')[0];
        $queryParams = $request->getQueryParams();
        $lang = null;
        if (isset($queryParams['lang'])) {
            if (!is_string($queryParams['lang']) || $queryParams['lang'] === '') {
                $response->getBody()->write(json_encode(['error' => 'valor do parametro lang invalido']));
                return $response->withStatus(400);
            }
            $lang = $queryParams['lang'];
        }
        $stm = $this->service->getAll($adminUserId, $lang);
        $response->getBody()->write(json_encode($stm->fetchAll()));
        return $response->withStatus(200);
    }

    public function getOne(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $adminUserId = $request->getHeader('admin_user_id')[0];
        $queryParams = $request->getQueryParams();
        $lang = null;
        if (isset($queryParams['lang'])) {
            if (!is_string($queryParams['lang']) || $queryParams['lang'] === '') {
                $response->getBody()->write(json_encode(['error' => 'valor do parametro lang invalido']));
                return $response->withStatus(400);
            }
            $lang = $queryParams['lang'];
        }
        $stm = $this->service->getOne($adminUserId, $args['id'], $lang);
        $response->getBody()->write(json_encode($stm->fetchAll()));
        return $response->withStatus(200);
    }

    public function insertOne(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->insertOne($body, $adminUserId)) {
            return $response->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['error' => 'nao foi possivel criar a categoria']));
            return $response->withStatus(404);
        }
    }

    public function insertTranslations(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $body = $request->getParsedBody();

        if (!isset($body['translations']) || !is_array($body['translations']) || $body['translations'] === []) {
            $response->getBody()->write(json_encode(['error' => 'lista de translations invalida ou vazia']));
            return $response->withStatus(400);
        }
        foreach ($body['translations'] as $translation) {
            if (!isset($translation['lang_code']) || !isset($translation['label'])) {
                $response->getBody()->write(json_encode(['error' => 'toda traducao precisa de lang_code e label']));
                return $response->withStatus(400);
            }
        }

        if ($this->service->insertTranslations($args['id'], $body['translations'])) {
            return $response->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['error' => 'traducao repetida, nenhuma foi cadastrada']));
            return $response->withStatus(400);
        }
    }

    public function updateOne(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->updateOne($args['id'], $body, $adminUserId)) {
            return $response->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['error' => 'categoria nao encontrada']));
            return $response->withStatus(404);
        }
    }

    public function deleteOne(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->deleteOne($args['id'], $adminUserId)) {
            return $response->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['error' => 'categoria nao encontrada']));
            return $response->withStatus(404);
        }
    }
}
