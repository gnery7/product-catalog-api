<?php

use Contatoseguro\TesteBackend\Middleware\JsonResponseMiddleware;
use Slim\App;

/** @var App $app*/
$app->addBodyParsingMiddleware();
$app->add(JsonResponseMiddleware::class);