<?php

declare(strict_types=1);

namespace Charcoal\App\Handlers;

use Charcoal\App\Exceptions\RouteException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Redirection Route Handler.
 */
final class Redirection
{
    public const DEFAULT_METHODS = ['GET'];


    public function __invoke(Request $request, Response $response): Response
    {
        $config = new RedirectionConfig($request->getAttribute('routeDefinition'));

        if (!$config->has('target')) {
            throw new RouteException(
                'Redirection target not defined in route definition.'
            );
        }

        return $response
            ->withHeader('Location', $config->get('target'))
            ->withStatus($config->get('code'));
    }
}
