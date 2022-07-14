<?php

namespace Charcoal\Cache\Middleware;

use Closure;
// From PSR-6
use Psr\Cache\CacheItemPoolInterface;
// From PSR-7
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
// From 'charcoal-cache'
use Charcoal\Cache\CacheConfig;

/**
 * Charcoal HTTP Cache Middleware
 *
 * Saves or loads the HTTP response from a {@link https://www.php-fig.org/psr/psr-6/ PSR-6 cache pool}.
 * It uses {@see https://packagist.org/packages/tedivm/stash Stash} as the caching library, so you
 * have plenty of driver choices.
 *
 * The middleware saves the response body and headers in a cache pool and returns.
 *
 * The middleware will attempt to load a cached HTTP response based on the HTTP request's route.
 * The route must matched the middleware's conditons for allowed methods, paths, and query parameters,
 * as well as the response's status code.
 *
 * If the cache is a hit, the response is immediately returned; meaning that any subsequent middleware
 * in the stack will be ignored.
 *
 * Ideally, this middleware should be the first the execute on the stack, in most cases
 * (with Slim, this means adding it last).
 */
class CacheMiddleware
{
    /**
     * PSR-6 cache item pool.
     *
     * @var CacheItemPoolInterface
     */
    private $cachePool;

    /**
     * Cache response if the request matches one of the HTTP methods.
     *
     * @var string[]
     */
    private $methods;

    /**
     * Cache response if the request matches one of the HTTP status codes.
     *
     * @var integer[]
     */
    private $statusCodes;

    /**
     * Time-to-live in seconds.
     *
     * @var integer
     */
    private $cacheTtl;

    /**
     * Cache response if the request matches one of the URI path patterns.
     *
     * One or more regex patterns (excluding the outer delimiters).
     *
     * @var null|string|array
     */
    private $includedPath;

    /**
     * Cache response if the request does not match any of the URI path patterns.
     *
     * One or more regex patterns (excluding the outer delimiters).
     *
     * @var null|string|array
     */
    private $excludedPath;

    /**
     * Cache response if the request matches one of the query parameters.
     *
     * One or more query string fields.
     *
     * @var array|string|null
     */
    private $includedQuery;

    /**
     * Cache response if the request does not match any of the query parameters.
     *
     * One or more query string fields.
     *
     * @var array|string|null
     */
    private $excludedQuery;

    /**
     * Ignore query parameters from the request.
     *
     * @var array|string|null
     */
    private $ignoredQuery;

    /**
     * Skip cache early on various conditions.
     *
     * @var array|null
     */
    private $skipCache;

    /**
     * @var Closure|null
     */
    private $processCacheKeyCallback;

    /**
     * @param array $data Constructor dependencies and options.
     */
    public function __construct(array $data)
    {
        $data = array_replace($this->defaults(), $data);

        $this->cachePool = $data['cache'];
        $this->cacheTtl  = $data['ttl'];

        $this->methods       = (array)$data['methods'];
        $this->statusCodes   = (array)$data['status_codes'];

        $this->includedPath  = $data['included_path'];
        $this->excludedPath  = $data['excluded_path'];

        $this->includedQuery = $data['included_query'];
        $this->excludedQuery = $data['excluded_query'];
        $this->ignoredQuery  = $data['ignored_query'];

        $this->skipCache = $data['skip_cache'];

        $this->processCacheKeyCallback = $data['processCacheKeyCallback'];
    }

    /**
     * Default middleware options.
     *
     * @return array
     */
    public function defaults()
    {
        return [
            'ttl'            => CacheConfig::DAY_IN_SECONDS,

            'included_path'  => '*',
            'excluded_path'  => [ '^/admin\b' ],

            'methods'        => [ 'GET' ],
            'status_codes'   => [ 200 ],

            'included_query' => null,
            'excluded_query' => null,
            'ignored_query'  => null,

            'skip_cache' => [
                'session_vars' => [],
            ],

            'processCacheKeyCallback' => null,
        ];
    }

    /**
     * Load a route content from path's cache.
     *
     * This method is as dumb / simple as possible.
     * It does not rely on any sort of settings / configuration.
     * Simply: if the cache for the route exists, it will be used to display the page.
     * The `$next` callback will not be called, therefore stopping the middleware stack.
     *
     * To generate the cache used in this middleware,
     * @see \Charcoal\App\Middleware\CacheGeneratorMiddleware.
     *
     * @param  RequestInterface  $request  The PSR-7 HTTP request.
     * @param  ResponseInterface $response The PSR-7 HTTP response.
     * @param  callable          $next     The next middleware callable in the stack.
     * @return ResponseInterface
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        // Bail early
        if (!$this->isRequestMethodValid($request)) {
            return $next($request, $response);
        }

        if ($this->isSkipCache($request)) {
            return $next($request, $response);
        }

        $cacheKey  = $this->cacheKeyFromRequest($request);
        $cacheItem = $this->cachePool->getItem($cacheKey);

        if ($cacheItem->isHit()) {
            $cached = $cacheItem->get();
            $response->getBody()->write($cached['body']);
            foreach ($cached['headers'] as $name => $header) {
                $response = $response->withHeader($name, $header);
            }

            return $response;
        }

        $uri   = $request->getUri();
        $path  = $uri->getPath();
        $query = [];

        parse_str($uri->getQuery(), $query);

        $response = $next($request, $response);

        if (!$this->isResponseStatusValid($response)) {
            return $this->disableCacheHeadersOnResponse($response);
        }

        if (!$this->isPathIncluded($path)) {
            return $this->disableCacheHeadersOnResponse($response);
        }

        if ($this->isPathExcluded($path)) {
            return $this->disableCacheHeadersOnResponse($response);
        }

        if (!$this->isQueryIncluded($query)) {
            $queryArr = $this->parseIgnoredParams($query);
            if (!empty($queryArr)) {
                return $this->disableCacheHeadersOnResponse($response);
            }
        }

        if ($this->isQueryExcluded($query)) {
            return $this->disableCacheHeadersOnResponse($response);
        }

        // Nothing has excluded the cache so far: add it to the pool.
        $cacheItem->expiresAfter($this->cacheTtl);
        $cacheItem->set([
            'body'    => (string)$response->getBody(),
            'headers' => (array)$response->getHeaders(),
        ]);
        $this->cachePool->save($cacheItem);

        return $response;
    }

    /**
     * Generate the cache key from the HTTP request.
     *
     * @param  RequestInterface $request The PSR-7 HTTP request.
     * @return string
     */
    private function cacheKeyFromRequest(RequestInterface $request)
    {
        $uri = $request->getUri();

        $queryStr = $uri->getQuery();
        if (!empty($queryStr)) {
            $queryArr = [];

            parse_str($queryStr, $queryArr);

            $queryArr = $this->parseIgnoredParams($queryArr);
            $queryStr = http_build_query($queryArr);

            $uri = $uri->withQuery($queryStr);
        }

        $cacheKey = 'request/' . $request->getMethod() . '/' . md5((string)$uri);

        $callback = $this->processCacheKeyCallback;
        if (is_callable($callback)) {
            return $callback($cacheKey);
        }

        return $cacheKey;
    }

    /**
     * Determine if the HTTP request method matches the accepted choices.
     *
     * @param  RequestInterface $request The PSR-7 HTTP request.
     * @return boolean
     */
    private function isRequestMethodValid(RequestInterface $request)
    {
        return in_array($request->getMethod(), $this->methods);
    }

    /**
     * Determine if the HTTP request method matches the accepted choices.
     *
     * @param  RequestInterface $request The PSR-7 HTTP request.
     * @return boolean
     */
    private function isSkipCache(RequestInterface $request)
    {
        if (isset($this->skipCache['session_vars'])) {
            $skip = $this->skipCache['session_vars'];

            if (!empty($skip)) {
                if (!session_id()) {
                    session_cache_limiter(false);
                    session_start();
                }

                if (array_intersect_key($_SESSION, array_flip($skip))) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determine if the HTTP response status matches the accepted choices.
     *
     * @param  ResponseInterface $response The PSR-7 HTTP response.
     * @return boolean
     */
    private function isResponseStatusValid(ResponseInterface $response)
    {
        return in_array($response->getStatusCode(), $this->statusCodes);
    }

    /**
     * Determine if the request should be cached based on the URI path.
     *
     * @param  string $path The request path (route) to verify.
     * @return boolean
     */
    private function isPathIncluded($path)
    {
        if ($this->includedPath === '*') {
            return true;
        }

        if (empty($this->includedPath) && !is_numeric($this->includedPath)) {
            return false;
        }

        foreach ((array)$this->includedPath as $included) {
            if (preg_match('@' . $included . '@', $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the request should NOT be cached based on the URI path.
     *
     * @param  string $path The request path (route) to verify.
     * @return boolean
     */
    private function isPathExcluded($path)
    {
        if ($this->excludedPath === '*') {
            return true;
        }

        if (empty($this->excludedPath) && !is_numeric($this->excludedPath)) {
            return false;
        }

        foreach ((array)$this->excludedPath as $excluded) {
            if (preg_match('@' . $excluded . '@', $path)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the request should be cached based on the URI query.
     *
     * @param  array $queryParams The query parameters to verify.
     * @return boolean
     */
    private function isQueryIncluded(array $queryParams)
    {
        if (empty($queryParams)) {
            return true;
        }

        if ($this->includedQuery === '*') {
            return true;
        }

        if (empty($this->includedQuery) && !is_numeric($this->includedQuery)) {
            return false;
        }

        $includedParams = array_intersect_key($queryParams, array_flip((array)$this->includedQuery));
        return (count($includedParams) > 0);
    }

    /**
     * Determine if the request should NOT be cached based on the URI query.
     *
     * @param  array $queryParams The query parameters to verify.
     * @return boolean
     */
    private function isQueryExcluded(array $queryParams)
    {
        if (empty($queryParams)) {
            return false;
        }

        if ($this->excludedQuery === '*') {
            return true;
        }

        if (empty($this->excludedQuery) && !is_numeric($this->excludedQuery)) {
            return false;
        }

        $excludedParams = array_intersect_key($queryParams, array_flip((array)$this->excludedQuery));
        return (count($excludedParams) > 0);
    }

    /**
     * Returns the query parameters that are NOT ignored.
     *
     * @param  array $queryParams The query parameters to filter.
     * @return array
     */
    private function parseIgnoredParams(array $queryParams)
    {
        if (empty($queryParams)) {
            return $queryParams;
        }

        if ($this->ignoredQuery === '*') {
            if ($this->includedQuery === '*') {
                return $queryParams;
            }

            if (empty($this->includedQuery) && !is_numeric($this->includedQuery)) {
                return [];
            }

            return array_intersect_key($queryParams, array_flip((array)$this->includedQuery));
        }

        if (empty($this->ignoredQuery) && !is_numeric($this->ignoredQuery)) {
            return $queryParams;
        }

        return array_diff_key($queryParams, array_flip((array)$this->ignoredQuery));
    }

    /**
     * Disable the HTTP cache headers.
     *
     * - `Cache-Control` is the proper HTTP header.
     * - `Pragma` is for HTTP 1.0 support.
     * - `Expires` is an alternative that is also supported by 1.0 proxies.
     *
     * @param  ResponseInterface $response The PSR-7 HTTP response.
     * @return ResponseInterface The new HTTP response.
     */
    private function disableCacheHeadersOnResponse(ResponseInterface $response)
    {
        return $response
                ->withHeader('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->withHeader('Pragma', 'no-cache')
                ->withHeader('Expires', '0');
    }

    /**
     * @param Closure|null $processCacheKeyCallback ProcessCacheKeyCallback for CacheMiddleware.
     * @return self
     */
    public function setProcessCacheKeyCallback($processCacheKeyCallback)
    {
        $this->processCacheKeyCallback = $processCacheKeyCallback;

        return $this;
    }
}
