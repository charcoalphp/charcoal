<?php

declare(strict_types=1);

namespace Charcoal\App\Services;

use Pimple\{
    Container,
    ServiceProviderInterface
};

/**
 *
 */
class ControllersProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        /**
         * @return array<string,array|string|callable>
         */
        $container['app/controllers'] = function (): array {
            return [];
        };

        /**
         * Holds the various contexts for JSON route handler.
         * (Also available for any other type of route handlers.)
         *
         * Can be just a string as FQN of the context controller class or callable or an instance of the class itself.
         * The PSR-11 container will be passed to the context's class constructor.
         *
         * Required callback signature:
         * ```php
         * use Psr\Http\Message\ServerRequestInterface as Request;
         * use Psr\Http\Message\ResponseInterface as Response;
         *
         * public function __invoke(Request $request, Response $response) : array;
         * ```
         *
         * @return array<string,array|string|callable>
         */
        $container['app/contexts'] = function (): array {
            return [];
        };

        /**
         * Holds the various view controllers, for the View route handler.
         * Typically associated with a template, but are also available for any other type of route handlers.
         *
         * Can be just a string as FQN of the view controller class or callable or an instance of the class itself.
         * Can also simply be an array.
         *
         * The PSR-11 container will be passed to the view's class constructor.
         *
         * Required callback signature:
         * ```php
         * use Psr\Http\Message\ServerRequestInterface as Request;
         * use Psr\Http\Message\ResponseInterface as Response;
         *
         * public function __invoke(Request $request, Response $response) : array;
         * ```
         *
         * @return array<string,array|string|callable>
         */
        $container['app/views'] = function (): array {
            return [];
        };
    }
}
