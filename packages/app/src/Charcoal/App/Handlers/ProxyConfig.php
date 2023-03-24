<?php

declare(strict_types=1);

namespace Charcoal\App\Handlers;

use Charcoal\Config\AbstractConfig;

class ProxyConfig extends AbstractConfig
{
    protected string $url;

    /**
     * Guzzle client request options
     * @var array<mixed>
     * @see http://docs.guzzlephp.org/en/latest/request-options.html
     */
    protected array $requestOptions = [];

    protected string $proxyMethod;
}
