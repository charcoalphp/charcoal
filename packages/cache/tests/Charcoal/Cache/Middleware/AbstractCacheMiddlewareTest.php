<?php

namespace Charcoal\Tests\Cache\Middleware;

// From PSR-7
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

// From Slim
use Slim\Http\Body;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Response;
use Slim\Http\Uri;

// From 'charcoal-cache'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Cache\CachePoolTrait;
use Charcoal\Tests\Mocks\DefaultsAwareCacheMiddlewares as CacheMiddleware;

/**
 * Test CacheMiddleware
 *
 * @coversDefaultClass \Charcoal\Cache\Middleware\CacheMiddleware
 */
abstract class AbstractCacheMiddlewareTest extends AbstractTestCase
{
    use CachePoolTrait;

    /**
     * Create a new CacheMiddleware instance.
     *
     * @param  array $args Parameters for the initialization of a CacheMiddleware.
     * @return CacheMiddleware
     */
    protected function middlewareFactory(array $args = [])
    {
        if (!isset($args['cache'])) {
            $args['cache'] = static::getCachePool();
            $args['processCacheKeyCallback'] = function ($key) {
                return $key;
            };
        }

        return new CacheMiddleware($args);
    }

    /**
     * Create a mock intermediate HTTP Middleware instance.
     *
     * @return callable
     */
    protected function mockNextMiddleware()
    {
        return function ($request, $response) {
            return $response;
        };
    }

    /**
     * Create a new mock HTTP Middleware instance.
     *
     * @param  mixed $body The response body.
     * @param  integer $status The response status code.
     * @return callable
     */
    protected function mockFinalMiddleware($body = null, $status = 200)
    {
        return function ($request, $response) use ($body, $status) {
            $response->getBody()->write($body);

            if (is_int($status)) {
                $response = $response->withStatus($status);
            }

            return $response;
        };
    }

    /**
     * Create a new URI instance.
     *
     * @param  string $uri A complete URI string.
     * @return UriInterface
     */
    protected function createUri($uri)
    {
        return Uri::createFromString($uri);
    }

    /**
     * Create a new Headers instance.
     *
     * @param  array $data A collection of HTTP headers.
     * @return StreamInterface
     */
    protected function createHeaders($data = [])
    {
        return new Headers($data);
    }

    /**
     * Create a new HTTP Request instance.
     *
     * @param  string       $method The request method.
     * @param  string       $uri    The URI path.
     * @param  string|array $query  The URI query parameters.
     * @return ServerRequestInterface
     */
    protected function createRequest($method = 'GET', $uri = '/', $query = null)
    {
        $env = Environment::mock();
        $env['REQUEST_METHOD'] = strtoupper($method);
        $env['REQUEST_URI']    = $uri;

        if ($query !== null) {
            $env['QUERY_STRING'] = is_array($query) ? http_build_query($query) : $query;
        }

        $request = Request::createFromEnvironment($env);
        return $request;
    }

    /**
     * Create a new Stream instance.
     *
     * @param  string|null $data The response body.
     * @return StreamInterface
     */
    protected function createResponseBody($data = null)
    {
        $body = new Body(fopen('php://temp', 'r+'));

        if ($data !== null) {
            $body->write($data);
        }

        return $body;
    }

    /**
     * Create a new HTTP Response instance.
     *
     * @param  integer $status The response status code.
     * @param  mixed   $body   The response body.
     * @return ResponseInterface
     */
    protected function createResponse($status = 200, $body = null)
    {
        if (is_string($body)) {
            $body = $this->createResponseBody($body);
        }

        $headers  = new Headers([ 'Content-Type' => 'text/html; charset=UTF-8' ]);
        $response = new Response($status, $headers, $body);
        return $response;
    }

    /**
     * Reports an error if the HTTP response headers does not have disabled cache headers.
     *
     * @covers ::disableCacheHeadersOnResponse
     *
     * @param  array $headers The HTTP response headers to test.
     * @return void
     */
    public function assertResponseHasDisabledCacheHeaders(array $headers)
    {
        $this->assertArrayHasKey('Cache-Control', $headers);
        $this->assertContains('no-cache, no-store, must-revalidate', $headers['Cache-Control']);

        $this->assertArrayHasKey('Pragma', $headers);
        $this->assertContains('no-cache', $headers['Pragma']);

        $this->assertArrayHasKey('Expires', $headers);
        $this->assertContains('0', $headers['Expires']);
    }
}
