<?php

namespace Charcoal\App\Handlers\Errors;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Interfaces\ErrorHandlerInterface;
use Slim\Psr7\Factory\ResponseFactory;

class NotFound implements ErrorHandlerInterface
{
    public function __invoke(
        Request $request,
        \Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ): Response {
        //var_dump('404');
        $response = (new ResponseFactory())->createResponse(404);
        return $response->withHeader('X-Charcoal-404', 'Yes');
    }
}
