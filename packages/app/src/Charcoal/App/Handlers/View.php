<?php

declare(strict_types=1);

namespace Charcoal\App\Handlers;

use Charcoal\App\Exceptions\RouteException;
use Charcoal\App\Utils\ClassResolver;
use Charcoal\View\Renderer;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * View Route PSR-7 Handler.
 */
final class View
{
    public const DEFAULT_METHODS = ['GET'];

    private ContainerInterface $container;

    /**
     * @var array<string|array|callable>
     */
    private array $views;

    private Renderer $renderer;

    private ClassResolver $resolver;

    public function __construct(ContainerInterface $container)
    {
        // Keep a copy of the container to instantiate the view controller
        $this->container = $container;
        $this->views = $container->has('app/views') ? $container->get('app/views') : [];
        $this->renderer = $container->get('view/renderer');
        $this->resolver = new ClassResolver();
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $config = new ViewConfig($request->getAttribute('routeDefinition'));
        $request = $request->withoutAttribute('routeDefinition');

        if (!$config->has('template')) {
            throw new RouteException(
                'View template not defined in route definition.'
            );
        }

        $viewController = $this->getControllerFromConfig($config);

        if (is_string($viewController)) {
            if (!class_exists($viewController)) {
                throw new RouteException(
                    sprintf('View controller "%s" is invalid.', $viewController)
                );
            }
            $viewController = new $viewController($this->container);
            $context = $viewController($request, $response);
        } elseif (is_array($viewController)) {
            $context = $viewController;
        } elseif (is_callable($viewController)) {
            $context = $viewController($request, $response);
        } else {
            $context = [];
        }

        return $this->renderer->render($response, $config->get('template'), $context);
    }

    /**
     * @return array|callable|string
     */
    private function getControllerFromConfig(ViewConfig $config)
    {
        if ($config->has('view')) {
            $controller = $config->get('view');
            if (isset($this->views[$controller])) {
                $viewController = $this->views[$controller];
            } else {
                $viewController = $this->resolver->resolve($controller);
            }
        } else {
            $viewController = [];
        }

        return $viewController;
    }
}
