<?php

declare(strict_types=1);

namespace Charcoal\App\Handlers;

use Charcoal\Slim\Exceptions\RouteException;
use Charcoal\Slim\Utils\ClassResolver;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Controller Route Handler.
 */
final class Controller
{
    public const DEFAULT_METHODS = [];

    private ContainerInterface $container;

    /**
     * @var array<string|callable>
     */
    private array $controllers;

    private ClassResolver $resolver;

    public function __construct(ContainerInterface $container)
    {
        // Keep a copy of the PSR-11 container, to pass it along the sub-controller
        $this->container = $container;
        $this->controllers = $container->has('app/controllers') ? $container->get('app/controllers') : [];
        $this->resolver = new ClassResolver();
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $config = new ControllerConfig($request->getAttribute('routeDefinition'));
        $request = $request->withoutAttribute('routeDefinition');

        if (!$config->has('controller')) {
            throw new RouteException(
                'Controller not defined in controller route definition.'
            );
        }

        if (isset($this->controllers[$config->get('controller')])) {
            $subController = $this->controllers[$config->get('controller')];
        } else {
            $subController = $this->resolver->resolve($config->get('controller'));
        }

        if (is_string($subController)) {
            if (!class_exists($subController)) {
                throw new RouteException(
                    sprintf('Invalid controller "%s" in route definition.', $subController)
                );
            }
            $subController = new $subController($this->container);
        }

        return $subController($request, $response);
    }
}
