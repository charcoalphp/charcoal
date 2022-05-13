<?php

declare(strict_types=1);

namespace Charcoal\Email;

// From 'psr/http-message' (PSR-7)
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

// From 'locomotivemtl/charcoal-app'
use Charcoal\App\Module\AbstractModule;

use Charcoal\Email\Api\V1\LinkAction;
use Charcoal\Email\Api\V1\OpenAction;

/**
 *
 */
class ApiModule extends AbstractModule
{
    const BASE_PATH = '/email/v1';

    /**
     * @return self
     */
    public function setUp()
    {
        $this->setupPublicRoutes();

        return $this;
    }

    /**
     * @return void
     */
    private function setupPublicRoutes()
    {
        $container = $this->app()->getContainer();

        $this->app()->group(self::BASE_PATH, function() use ($container) {

            $group = $this;

            $group->get('/link/{linkId}', function(Request $request, Response $response, array $args) use ($container) {
                $action = new LinkAction(
                    $args['linkId'],
                    $container['email/tracker'],
                    $container['model/factory']
                );
                return $action($request, $response);
            });

            $group->get('/open/{emailId}[.png]', function(Request $request, Response $response, array $args) use ($container) {
                $action = new OpenAction(
                    $args['emailId'],
                    $container['email/tracker']
                );
                return $action($request, $response);
            });
        });
    }
}
