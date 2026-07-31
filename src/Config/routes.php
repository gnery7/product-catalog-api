<?php

use Contatoseguro\TesteBackend\Controller\CategoryController;
use Contatoseguro\TesteBackend\Controller\CompanyController;
use Contatoseguro\TesteBackend\Controller\HomeController;
use Contatoseguro\TesteBackend\Controller\ProductController;
use Contatoseguro\TesteBackend\Controller\ReportController;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/** @var App $app*/
$app->get('/', [HomeController::class, 'home']);

$app->group('/companies', function (RouteCollectorProxy $group) {
    $group->get('', [CompanyController::class, 'getAll']);
    $group->get('/{id}', [CompanyController::class, 'getOne']);
});

$app->group('/products', function (RouteCollectorProxy $group) {
    $group->get('', [ProductController::class, 'getAll']);
    $group->get('/{id}', [ProductController::class, 'getOne']);
    $group->post('', [ProductController::class, 'insertOne']);
    $group->put('/{id}', [ProductController::class, 'updateOne']);
    $group->delete('/{id}', [ProductController::class, 'deleteOne']);
});

$app->group('/categories', function (RouteCollectorProxy $group) {
    $group->get('', [CategoryController::class, 'getAll']);
    $group->get('/{id}', [CategoryController::class, 'getOne']);
    $group->post('', [CategoryController::class, 'insertOne']);
    $group->put('/{id}', [CategoryController::class, 'updateOne']);
    $group->delete('/{id}', [CategoryController::class, 'deleteOne']);
});

$app->get('/report', [ReportController::class, 'generate']);