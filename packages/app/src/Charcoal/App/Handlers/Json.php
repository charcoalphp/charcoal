<?php

declare(strict_types=1);

namespace Charcoal\App\Handlers;

// From 'psr/http-message' (PSR-7)
use Charcoal\App\Exceptions\RouteException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
// From 'psr/container' (PSR-11)
use Psr\Container\ContainerInterface;
use Charcoal\App\Utils\ClassResolver;

/**
 * Json Route PSR-7 Handler.
 */
final class Json
{
    public const DEFAULT_METHODS = ['POST'];

    private ContainerInterface $container;

    /**
     * @var array<string|array|callable>
     */
    private array $contexts;

    private ClassResolver $resolver;

    public function __construct(ContainerInterface $container)
    {
        // Keep a copy of the container to instantiate the data controller
        $this->container = $container;
        $this->contexts = $container->has('app/contexts') ? $container->get('app/contexts') : [];
        $this->resolver = new ClassResolver();
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $config = new JsonConfig($request->getAttribute('routeDefinition'));
        $request = $request->withoutAttribute('routeDefinition');

        if (!$config->has('context')) {
            throw new RouteException(
                'JSON context not defined in route definition.'
            );
        }

        $dataController = $this->getContextFromConfig($config);

        if (is_string($dataController)) {
            if (!class_exists($dataController)) {
                throw new RouteException(
                    sprintf('JSON context controller "%s" is invalid.', $dataController)
                );
            }
            $dataController = new $dataController($this->container);
            $context = $dataController($request, $response);
        } elseif (is_callable($dataController)) {
            $context = $dataController($request, $response);
        } else {
            $context = $dataController;
        }

        $response->getBody()->write(json_encode($context));

        return $response
            ->withHeader('Content-Type', 'application/json');
    }

    /**
     * @return array|callable|string
     */
    private function getContextFromConfig(JsonConfig $config)
    {
        if (is_string($config->get('context'))) {
            if (isset($this->contexts[$config->get('context')])) {
                $dataController = $this->contexts[$config->get('context')];
            } else {
                $dataController = $this->resolver->resolve($config->get('context'));
            }
        } else {
            $dataController = $config->get('context');
        }

        return $dataController;
    }
}
