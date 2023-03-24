<?php

declare(strict_types=1);

namespace Charcoal\App;

use Charcoal\Config\AbstractConfig;

class RouteConfig extends AbstractConfig
{
    /**
     * Route handler type.
     */
    protected string $type;

    /**
     * Route pattern.
     */
    protected string $route;

    /**
     * HTTP methods supported by this route.
     *
     * @var string[]
     */
    protected array $methods = [ 'GET' ];

    /**
     * Optional headers to set on response.
     *
     * @var array<string, string>
     */
    protected $headers = [];
}
