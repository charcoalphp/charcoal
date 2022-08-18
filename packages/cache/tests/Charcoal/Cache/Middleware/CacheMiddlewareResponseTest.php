<?php

namespace Charcoal\Tests\Cache\Middleware;

// From PSR-7
use Psr\Http\Message\ResponseInterface;

// From 'tedivm/stash'
use Stash\Pool;

// From 'charcoal-cache'
use Charcoal\Cache\CacheConfig;
use Charcoal\Cache\Middleware\CacheMiddleware;

/**
 * Test HTTP Responses from CacheMiddleware.
 *
 * @coversDefaultClass \Charcoal\Cache\Middleware\CacheMiddleware
 */
class CacheMiddlewareResponseTest extends AbstractCacheMiddlewareTest
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
     * @covers ::__invoke
     * @covers ::cacheKeyFromRequest
     *
     * @return CacheMiddleware To use the same cache middleware for the next test.
     */
    public function testInitialState()
    {
        $txt = 'Lorem ipsum dolor sit amet.';

        $middleware = $this->middlewareFactory([ 'included_query' => '*' ]);
        $request    = $this->createRequest('GET', '/foo/bar?abc=123');
        $response   = $this->createResponse()->withHeader('X-Charcoal-1', 'foo');
        $finalize   = $this->mockFinalMiddleware($txt);

        $result = $middleware($request, $response, $finalize);

        // Validate the HTTP response
        $this->assertEquals($txt, (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());

        // Validate that the HTTP response is cached
        $pool = static::getCachePool();
        $item = $pool->getItem('request/GET/' . md5((string) $request->getUri()));

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
        $response = $this->createResponse()
                         ->withHeader('X-Charcoal-1', 'bar')
                         ->withHeader('X-Charcoal-2', 'qux');
        $finalize = $this->mockFinalMiddleware('Vestibulum gravida ultricies lacus ac porta.');

        $result = $middleware($request, $response, $finalize);

        // Validate the HTTP response
        $this->assertEquals($txt, (string) $result->getBody());
        $this->assertEquals(200, $result->getStatusCode());

        // Validate the HTTP response headers
        $headers = $result->getHeaders();
        $this->assertArrayHasKey('X-Charcoal-1', $headers);
        $this->assertContains('foo', $headers['X-Charcoal-1']);
        $this->assertArrayHasKey('X-Charcoal-2', $headers);
        $this->assertContains('qux', $headers['X-Charcoal-2']);

        // Validate that the HTTP response is cached
        $pool = static::getCachePool();
        $item = $pool->getItem('request/GET/' . md5((string) $request->getUri()));

        $data = $item->get();
        $this->assertArrayHasKey('body', $data);
        $this->assertEquals($txt, $data['body']);

        $this->assertArrayHasKey('headers', $data);
        $this->assertArrayHasKey('X-Charcoal-1', $data['headers']);
        $this->assertContains('foo', $data['headers']['X-Charcoal-1']);
        $this->assertArrayNotHasKey('X-Charcoal-2', $data['headers']);
    }
}
