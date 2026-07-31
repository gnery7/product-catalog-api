<?php

namespace Contatoseguro\TesteBackend\Controller;

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

    public function getAll(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
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

    public function getOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $stm = $this->service->getOne($args['id']);
        $product = Product::hydrateByFetch($stm->fetch());

        $adminUserId = $request->getHeader('admin_user_id')[0];
        $productCategories = $this->categoryService->getProductCategory($adminUserId, $product->id)->fetchAll();
        $categoryTitles = [];
        foreach ($productCategories as $productCategory) {
            $categoryTitles[] = $productCategory->title;
        }
        $product->setCategories($categoryTitles);

        $response->getBody()->write(json_encode($product));
        return $response->withStatus(200);
    }

    public function insertOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->insertOne($body, $adminUserId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }

    public function updateOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $body = $request->getParsedBody();
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->updateOne($args['id'], $body, $adminUserId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }

    public function deleteOne(ServerRequestInterface $request, ResponseInterface $response, array $args): ResponseInterface
    {
        $adminUserId = $request->getHeader('admin_user_id')[0];

        if ($this->service->deleteOne($args['id'], $adminUserId)) {
            return $response->withStatus(200);
        } else {
            return $response->withStatus(404);
        }
    }
}
