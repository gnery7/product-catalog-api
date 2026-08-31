<?php

use App\Middleware\JsonResponseMiddleware;
use Slim\App;
use Slim\Handlers\ErrorHandler;

/** @var App $app*/
$app->addBodyParsingMiddleware();
$app->add(JsonResponseMiddleware::class);

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorHandler = $errorMiddleware->getDefaultErrorHandler();
if ($errorHandler instanceof ErrorHandler) {
    $errorHandler->forceContentType('application/json');
}
