<?php

namespace Charcoal\Tests\Cache\Middleware;

// From PSR-7
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Response;
use Nyholm\Psr7\Stream;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Cache\CachePoolTrait;
use Charcoal\Tests\Mocks\DefaultsAwareCacheMiddlewares as CacheMiddleware;
use Nyholm\Psr7\Uri;
use Psr\Http\Server\RequestHandlerInterface;
use Charcoal\Cache\Middleware\CacheMiddleware as CharcoalCacheMiddleware;
use PHPUnit\Framework\Attributes\CoversMethod;

#[CoversMethod(CharcoalCacheMiddleware::class, 'disableCacheHeadersOnResponse')]
abstract class AbstractCacheMiddlewareTestCase extends AbstractTestCase
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
        return (new Uri($uri));
    }

    /**
     * Create a new Headers instance.
     *
     * @param  array $data A collection of HTTP headers.
     * @return array
     */
    protected function createHeaders($data = [])
    {
        return $data;
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
        $method = strtoupper($method);
        $uriObj = $this->createUri($uri);

        $request = new ServerRequest($method, $uriObj);

        if ($query !== null) {
            if (is_array($query)) {
                $request = $request->withQueryParams($query);
            } else {
                // parse query string into array
                parse_str($query, $qp);
                $request = $request->withQueryParams($qp);
            }
        }

         return $request;
    }

    /**
     * Create a new HTTP Request instance.
     */
    protected function createHandler(): RequestHandlerInterface
    {
        return new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                return new \Nyholm\Psr7\Response(
                    200,
                    ['Content-Type' => 'text/html; charset=UTF-8'],
                    \Nyholm\Psr7\Stream::create('')
                );
            }
        };
    }

    /**
     * Create a new Stream instance.
     *
     * @param  string|null $data The response body.
     * @return StreamInterface
     */
    protected function createResponseBody($data = null)
    {
        $body = Stream::create(($data ?? ''));
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

        $headers = [ 'Content-Type' => 'text/html; charset=UTF-8' ];
        $response = new Response($status, $headers, $body);
        return $response;
    }

    /**
     * Reports an error if the HTTP response headers does not have disabled cache headers.
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
