<?php

declare(strict_types=1);

namespace Charcoal\App\Services;

use Charcoal\App\Handlers\{
    Action,
    Controller,
    Errors\NotFound,
    Json,
    Proxy,
    Redirection,
    Template,
    View
};
use Pimple\{
    Container,
    ServiceProviderInterface
};
use Slim\Exception\HttpNotFoundException;

/**
 *
 */
class HandlersProvider implements ServiceProviderInterface
{
    public function register(Container $container): void
    {
        /**
         * Holds the various PSR-7 route handlers.
         * Typically defined as the "type" in route definitions.
         * @return array<string,string>
         */
        $container['app/handlers'] = function (): array {
            return [
                'action' => Action::class,
                'controller' => Controller::class,
                'json' => Json::class,
                'proxy' => Proxy::class,
                'redirection' => Redirection::class,
                'template' => Template::class,
                'view' => View::class
            ];
        };

        $container['app/handlers/errors'] = function (): array {
            return [
                HttpNotFoundException::class => NotFound::class
            ];
        };
    }
}
