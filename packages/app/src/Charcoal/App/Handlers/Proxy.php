<?php

declare(strict_types=1);

namespace Charcoal\App\Handlers;

use Charcoal\App\Exceptions\RouteException;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Proxy Route Handler.
 */
final class Proxy
{
    public const DEFAULT_METHODS = ['GET', 'POST'];

    private ClientInterface $client;

    public function __construct(ContainerInterface $container)
    {
        if ($container->has('app/http-client')) {
            $this->client = $container->get('app/http-client');
        } else {
            $this->client = new Client();
        }
    }

    public function __invoke(Request $request, Response $response): Response
    {
        $config = new ProxyConfig($request->getAttribute('routeDefinition'));
        $request = $request->withoutAttribute('routeDefinition');

        if (!$config->has('url')) {
            throw new RouteException(
                'Proxy URL not defined in route definition.'
            );
        }

        $method = $config->has('proxyMethod') ? $config->get('proxyMethod') : $request->getMethod();
        $target = new GuzzleRequest($method, $config->get('url'));

        $proxy = $this->client->send($target, $config->get('requestOptions'));
        return $response->withBody($proxy->getBody());
    }
}
