<?php

declare(strict_types=1);

namespace Charcoal\Email;

// From 'psr/http-message' (PSR-7)
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
// From 'charcoal/app'
use Charcoal\App\Module\AbstractModule;
use Charcoal\Email\Api\V1\LinkAction;
use Charcoal\Email\Api\V1\OpenAction;

/**
 *
 */
class ApiModule extends AbstractModule
{
    public const BASE_PATH = '/email/v1';

    #[\Override]
    public function setUp(): static
    {
        $this->setupPublicRoutes();

        return $this;
    }

    private function setupPublicRoutes(): void
    {
        $container = $this->app()->getContainer();

        $this->app()->group(self::BASE_PATH, function () use ($container): void {

            $group = $this;

            $group->get('/link/{linkId}', function (Request $request, Response $response, array $args) use ($container): \Psr\Http\Message\ResponseInterface {
                $action = new LinkAction(
                    $args['linkId'],
                    $container['email/tracker'],
                    $container['model/factory']
                );
                return $action($request, $response);
            });

            $group->get('/open/{emailId}[.png]', function (Request $request, Response $response, array $args) use ($container): \Psr\Http\Message\ResponseInterface {
                $action = new OpenAction(
                    $args['emailId'],
                    $container['email/tracker']
                );
                return $action($request, $response);
            });
        });
    }
}
