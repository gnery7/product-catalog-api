<?php

use Contatoseguro\TesteBackend\Controller\CategoryController;
use Contatoseguro\TesteBackend\Controller\CompanyController;
use Contatoseguro\TesteBackend\Controller\HomeController;
use Contatoseguro\TesteBackend\Controller\ProductController;
use Contatoseguro\TesteBackend\Controller\ReportController;
use Contatoseguro\TesteBackend\Controller\CommentController;
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
    $group->post('/{id}/comments', [CommentController::class, 'insertOne']);
});

$app->group('/comments', function (RouteCollectorProxy $group) {
    $group->post('/{id}/replies', [CommentController::class, 'insertReply']);
    $group->post('/{id}/like', [CommentController::class, 'insertLike']);
    $group->delete('/{id}', [CommentController::class, 'deleteOne']);
});


$app->group('/categories', function (RouteCollectorProxy $group) {
    $group->get('', [CategoryController::class, 'getAll']);
    $group->get('/{id}', [CategoryController::class, 'getOne']);
    $group->post('', [CategoryController::class, 'insertOne']);
    $group->post('/{id}', [CategoryController::class, 'insertTranslations']);
    $group->put('/{id}', [CategoryController::class, 'updateOne']);
    $group->delete('/{id}', [CategoryController::class, 'deleteOne']);
});

$app->get('/report', [ReportController::class, 'generate']);
