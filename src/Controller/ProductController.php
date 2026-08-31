<?php

namespace Contatoseguro\TesteBackend\Controller;

use Contatoseguro\TesteBackend\Exception\InvalidCategoryException;
use Contatoseguro\TesteBackend\Exception\InvalidCompanyException;
use Contatoseguro\TesteBackend\Model\Product;
use Contatoseguro\TesteBackend\Service\CategoryService;
use Contatoseguro\TesteBackend\Service\ProductService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ProductController
{
    private ProductService $service;
    private CategoryService $categoryService;

    public function __construct()
    {
        $this->service = new ProductService();
        $this->categoryService = new CategoryService();
    }

    public function getAll(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $adminUserId = $request->getHeader('admin_user_id')[0];
        $queryParams = $request->getQueryParams();

        $filters = [];
        if (isset($queryParams['active'])) {
            if (!in_array($queryParams['active'], ['0', '1'], true)) {
                $response->getBody()->write(json_encode(['error' => 'valor do parametro active invalido']));
                return $response->withStatus(400);
            }
            $filters['active'] = $queryParams['active'];
        }
        if (isset($queryParams['category'])) {
            if (!ctype_digit($queryParams['category'])) {
                $response->getBody()->write(json_encode(['error' => 'valor do parametro category invalido']));
                return $response->withStatus(400);
            }
            $filters['category'] = $queryParams['category'];
        }
        if (isset($queryParams['order'])) {
            if (!in_array($queryParams['order'], ['asc', 'desc'], true)) {
                $response->getBody()->write(json_encode(['error' => 'valor do parametro order invalido']));
                return $response->withStatus(400);
            }
            $filters['order'] = $queryParams['order'];
        }
        if (isset($queryParams['lang'])) {
            if (!is_string($queryParams['lang']) || $queryParams['lang'] === '') {
                $response->getBody()->write(json_encode(['error' => 'valor do parametro lang invalido']));
                return $response->withStatus(400);
            }
            $filters['lang'] = $queryParams['lang'];
        }

        if (isset($queryParams['min_stock'])) {
            if (!ctype_digit($queryParams['min_stock'])) {
                $response->getBody()->write(json_encode(['error' => 'valor do parametro min_stock invalido']));
                return $response->withStatus(400);
            }
            $filters['min_stock'] = $queryParams['min_stock'];
        }

        $stm = $this->service->getAll($adminUserId, $filters);

        $fetchedProducts = $stm->fetchAll();
        $products = [];
        foreach ($fetchedProducts as $fetchedProduct) {
            $categoryTitle = $fetchedProduct->category_title;
            unset($fetchedProduct->category_title);
            if (!isset($products[$fetchedProduct->id])) {
                $fetchedProduct->categories = [];
                $products[$fetchedProduct->id] = $fetchedProduct;
            }
            $products[$fetchedProduct->id]->categories[] = $categoryTitle;
        }
        $response->getBody()->write(json_encode(array_values($products)));
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
        $stm = $this->service->getOne($args['id'], $adminUserId);
        $fetch = $stm->fetch();
        if ($fetch === false) {
            $response->getBody()->write(json_encode(['error' => 'produto nao encontrado']));
            return $response->withStatus(404);
        }
        $product = Product::hydrateByFetch($fetch);

        $productCategories = $this->categoryService->getProductCategory($adminUserId, $product->id, $lang)->fetchAll();
        $categoryTitles = [];
        foreach ($productCategories as $productCategory) {
            $categoryTitles[] = $productCategory->title;
        }
        $product->setCategories($categoryTitles);

        $response->getBody()->write(json_encode($product));
        return $response->withStatus(200);
    }


    public function insertOne(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if (isset($body['stock']) && !ctype_digit((string)$body['stock'])) {
            $response->getBody()->write(json_encode(['error' => 'valor do campo stock invalido']));
            return $response->withStatus(400);
        }

        try {
            $result = $this->service->insertOne($body, $adminUserId);
        } catch (InvalidCompanyException $e) {
            $response->getBody()->write(json_encode(['error' => 'empresa nao encontrada']));
            return $response->withStatus(404);
        } catch (InvalidCategoryException $e) {
            $response->getBody()->write(json_encode(['error' => 'categoria nao encontrada']));
            return $response->withStatus(404);
        }

        if ($result === true) {
            return $response->withStatus(200);
        }
        $response->getBody()->write(json_encode(['error' => 'nao foi possivel criar o produto']));
        return $response->withStatus(404);
    }


    public function updateOne(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];
        if (isset($body['stock']) && !ctype_digit((string)$body['stock'])) {
            $response->getBody()->write(json_encode(['error' => 'valor do campo stock invalido']));
            return $response->withStatus(400);
        }

        if ($this->service->updateOne($args['id'], $body, $adminUserId)) {
            return $response->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['error' => 'produto nao encontrado']));
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
            $response->getBody()->write(json_encode(['error' => 'produto nao encontrado']));
            return $response->withStatus(404);
        }
    }
}
