<?php

namespace Charcoal\Tests\Cache\Middleware;

use Charcoal\Cache\Middleware\CacheMiddleware;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversClass(CacheMiddleware::class)]
class CacheMiddlewareRequestTest extends AbstractCacheMiddlewareTestCase
{
    /**
     * Prepare the cache pool.
     *
     * @return void
     */
    public function setUp(): void
    {
        static::createCachePool();
    }

    /**
     * Empty the cache pool.
     *
     * @return void
     */
    public function tearDown(): void
    {
        static::clearCachePool();
    }

    /**
     * Test middleware with an invalid HTTP request method.
     *
     * @covers CacheMiddleware::__invoke
     * @covers CacheMiddleware::isRequestMethodValid
     * @covers CacheMiddleware::isResponseStatusValid
     * @covers CacheMiddleware::isPathIncluded
     * @covers CacheMiddleware::isPathExcluded
     * @covers CacheMiddleware::isQueryIncluded
     * @covers CacheMiddleware::isQueryExcluded
     * @covers CacheMiddleware::parseIgnoredParams
     * @covers CacheMiddleware::disableCacheHeadersOnResponse
     *
     * @dataProvider provideInvokableSituations
     *
     * @param  boolean $expected         The expected result from {@see \Psr\Cache\CacheItemInterface::isHit()}.
     * @param  boolean $checkHttpHeaders Whether to test the HTTP response's headers.
     * @param  stromg  $requestUri       The request URI for {@see self::createRequest()}.
     * @param  array   $cacheConfig      The CacheMiddleware settings.
     * @return void
     */
    public function testInvoke($expected, $checkHttpHeaders, $requestUri, array $cacheConfig)
    {
        $middleware = $this->middlewareFactory($cacheConfig);
        $request    = $this->createRequest('GET', $requestUri);
        $handler    = $this->createHandler();

        $response = $middleware($request, $handler);

        // Validate that the HTTP response is NOT cached
        $pool = static::getCachePool();
        $item = $pool->getItem('request/GET/' . md5((string)$request->getUri()));

        $this->assertEquals($expected, $item->isHit());

        if ($checkHttpHeaders) {
            $this->assertResponseHasDisabledCacheHeaders($response->getHeaders());
        }
    }

    /**
     * Provide data for testing the middleware.
     *
     * @used-by self::testInvoke()
     * @return  array
     */
    public static function provideInvokableSituations()
    {
        $target1 = '/foo/bar';
        $target2 = '/foo/bar?abc=123';
        $target3 = '/foo/bar?abc=123&def=456';
        $target4 = '/foo/bar?=';

        // phpcs:disable Generic.Files.LineLength.TooLong
        return [
            'methods → accept one'          => [ false, false, $target1, [ 'methods'        => 'HEAD' ] ],

            'status_codes → accept one'     => [ false, true,  $target1, [ 'status_codes'   => 201 ] ],

            'included_path → accept all'    => [ true,  false, $target1, [ 'included_path'  => '*' ] ],
            'included_path → accept #1'     => [ true,  false, $target1, [ 'included_path'  => '^/(foo|qux)/bar' ] ],
            'included_path → accept #2'     => [ false, true,  $target1, [ 'included_path'  => '^/[xyz]+/bar' ] ],
            'included_path → empty'         => [ false, true,  $target1, [ 'included_path'  => [] ] ],

            'excluded_path → reject all'    => [ false, true,  $target1, [ 'excluded_path'  => '*' ] ],
            'excluded_path → reject #1'     => [ false, true,  $target1, [ 'excluded_path'  => '^/(foo|qux)/bar' ] ],
            'excluded_path → reject #2'     => [ true,  false, $target1, [ 'excluded_path'  => '^/[xyz]+/bar' ] ],
            'excluded_path → empty'         => [ true,  false, $target1, [ 'excluded_path'  => [] ] ],

            'included_query → accept all'   => [ true,  false, $target2, [ 'included_query' => '*' ] ],
            'included_query → accept #1'    => [ true,  false, $target2, [ 'included_query' => 'abc' ] ],
            'included_query → accept #2'    => [ false, true,  $target2, [ 'included_query' => 'def' ] ],
            'included_query → empty'        => [ false, true,  $target2, [ 'included_query' => [] ] ],

            'ignored_query → ignore all #1' => [ false, false, $target3, [ 'ignored_query'  => '*' ] ],
            'ignored_query → ignore all #2' => [ true,  false, $target3, [ 'ignored_query'  => '*', 'included_query'   => '*' ] ],
            'ignored_query → ignore all #3' => [ false, false, $target3, [ 'ignored_query'  => '*', 'included_query'   => 'abc' ] ],
            'ignored_query → ignore #1'     => [ false, false, $target3, [ 'ignored_query'  => [ 'abc', 'def' ] ] ],
            'ignored_query → ignore #2'     => [ false, true,  $target3, [ 'ignored_query'  => 'def' ] ],
            'ignored_query → bad query'     => [ false, false, $target4, [ 'ignored_query'  => [] ] ],
            'ignored_query → empty'         => [ false, true,  $target3, [ 'ignored_query'  => [] ] ],

            'excluded_query → reject all'   => [ false, true,  $target3, [ 'excluded_query' => '*',   'included_query' => '*' ] ],
            'excluded_query → reject #1'    => [ false, true,  $target3, [ 'excluded_query' => 'abc', 'included_query' => '*' ] ],
            'excluded_query → reject #2'    => [ false, true,  $target3, [ 'excluded_query' => 'def', 'included_query' => '*' ] ],
            'excluded_query → empty'        => [ true,  false, $target3, [ 'excluded_query' => [],    'included_query' => '*' ] ],
        ];
        // phpcs:enable
    }
}
