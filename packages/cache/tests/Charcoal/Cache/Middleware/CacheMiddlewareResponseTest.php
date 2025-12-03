<?php

namespace Charcoal\Tests\Cache\Middleware;

use Charcoal\Cache\Middleware\CacheMiddleware;
use Nyholm\Psr7\Stream;
use PHPUnit\Framework\Attributes\CoversMethod;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;

#[CoversMethod(CacheMiddleware::class, '__invoke')]
#[CoversMethod(CacheMiddleware::class, 'cacheKeyFromRequest')]
class CacheMiddlewareResponseTest extends AbstractCacheMiddlewareTestCase
{
    /**
     * Prepare the cache pool.
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        static::createCachePool();
    }

    /**
     * Empty the cache pool.
     *
     * @return void
     */
    public static function tearDownAfterClass(): void
    {
        static::clearCachePool();
    }

    /**
     * Test the initial state.
     *
     * @covers CacheMiddleware::__invoke
     * @covers CacheMiddleware::cacheKeyFromRequest
     *
     * @return CacheMiddleware To use the same cache middleware for the next test.
     */
    public function testInitialState()
    {
        $txt = 'Lorem ipsum dolor sit amet.';

        $middleware = $this->middlewareFactory([ 'included_query' => '*' ]);
        $request    = $this->createRequest('GET', '/foo/bar?abc=123');
        $handler    = new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $response = new \Nyholm\Psr7\Response(200, [
                    'Content-Type' => 'text/html; charset=UTF-8',
                    'X-Charcoal-1' => 'foo',
                ], \Nyholm\Psr7\Stream::create('Lorem ipsum dolor sit amet.'));
                return $response;
            }
        };

        $result = $middleware($request, $handler);

        // Validate the HTTP response
        $this->assertEquals($txt, (string)$result->getBody());
        $this->assertEquals(200, $result->getStatusCode());

        // Validate that the HTTP response is cached
        $pool = static::getCachePool();
        $item = $pool->getItem('request/GET/' . md5((string)$request->getUri()));

        $this->assertTrue($item->isHit());

        $data = $item->get();
        $this->assertArrayHasKey('body', $data);
        $this->assertEquals($txt, $data['body']);

        $this->assertArrayHasKey('headers', $data);

        $this->assertArrayHasKey('X-Charcoal-1', $data['headers']);
        $this->assertContains('foo', $data['headers']['X-Charcoal-1']);

        return $middleware;
    }

    /**
     * Test the cached state.
     *
     * @covers  ::__invoke
     * @covers  ::cacheKeyFromRequest
     * @depends testInitialState
     *
     * @param  CacheMiddleware $middleware The cache middleware from the previous test.
     * @return void
     */
    public function testCachedState(CacheMiddleware $middleware)
    {
        $txt = 'Lorem ipsum dolor sit amet.';

        $request  = $this->createRequest('GET', '/foo/bar?abc=123');
        $handler = new class () implements RequestHandlerInterface {
            public function handle(ServerRequestInterface $request): ResponseInterface
            {
                $response = new \Nyholm\Psr7\Response(200, [
                    'Content-Type' => 'text/html; charset=UTF-8',
                ], \Nyholm\Psr7\Stream::create('Vestibulum gravida ultricies lacus ac porta.'));
                return $response;
            }
        };

        $result = $middleware($request, $handler);

        $result = $result->withAddedHeader('X-Charcoal-1', 'bar')
                         ->withAddedHeader('X-Charcoal-2', 'qux');

        // Validate the HTTP response
        $this->assertEquals($txt, (string)$result->getBody());
        $this->assertEquals(200, $result->getStatusCode());

        // Validate the HTTP response headers
        $headers = $result->getHeaders();
        $this->assertArrayHasKey('X-Charcoal-1', $headers);
        $this->assertContains('foo', $headers['X-Charcoal-1']);
        $this->assertArrayHasKey('X-Charcoal-2', $headers);
        $this->assertContains('qux', $headers['X-Charcoal-2']);

        // Validate that the HTTP response is cached
        $pool = static::getCachePool();
        $item = $pool->getItem('request/GET/' . md5((string)$request->getUri()));

        $data = $item->get();
        $this->assertArrayHasKey('body', $data);
        $this->assertEquals($txt, $data['body']);

        $this->assertArrayHasKey('headers', $data);
        $this->assertArrayHasKey('X-Charcoal-1', $data['headers']);
        $this->assertContains('foo', $data['headers']['X-Charcoal-1']);
        $this->assertArrayNotHasKey('X-Charcoal-2', $data['headers']);
    }
}
