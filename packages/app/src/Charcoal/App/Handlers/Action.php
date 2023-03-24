<?php

declare(strict_types=1);

namespace Charcoal\App\Handlers;

use Charcoal\App\Exceptions\RouteException;
use Charcoal\Slim\Utils\ClassResolver;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class Action
{
    public const DEFAULT_METHODS = ['POST'];

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $config = new ActionConfig($request->getAttribute('routeDefinition'));
        $request = $request->withoutAttribute('routeDefinition');

        if (!$config->has('controller')) {
            throw new RouteException(
                'Controller not defined in action route definition.'
            );
        }

        $action = $this->container->get('action/factory')->create($config->get('controller'));
        $action->init($request);

        // Set custom data from config.
        $action->setData($config->get('action_data'));

        if ($config->has('headers')) {
            $customHeaders = $config->get('headers');
            foreach ($customHeaders as $name => $val) {
                $response = $response->withHeader($name, $val);
            }
        }

        // Run (invoke) action.
        return $action($request, $response);
    }
}
