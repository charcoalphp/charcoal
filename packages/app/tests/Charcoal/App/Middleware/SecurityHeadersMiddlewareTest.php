<?php

namespace Charcoal\Tests\App\Middleware;

use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;
use Charcoal\App\Middleware\SecurityHeadersMiddleware;
use Charcoal\Tests\AbstractTestCase;

class SecurityHeadersMiddlewareTest extends AbstractTestCase
{
    public function testDefaults()
    {
        $mw = new SecurityHeadersMiddleware([]);

        $defaults = $mw->defaults();

        $this->assertSame('nosniff', $defaults['headers']['X-Content-Type-Options']);
        $this->assertSame('SAMEORIGIN', $defaults['headers']['X-Frame-Options']);
        $this->assertTrue($defaults['hsts']['enabled']);
        $this->assertFalse($defaults['csp']['enabled']);
        $this->assertTrue($defaults['use_native_headers']);
    }

    public function testSecureHeadersAreApplied()
    {
        $response = $this->invoke($this->middleware(), $this->request('/', [ 'HTTPS' => 'on' ]));

        $this->assertSame('nosniff', $response->getHeaderLine('X-Content-Type-Options'));
        $this->assertSame('SAMEORIGIN', $response->getHeaderLine('X-Frame-Options'));
        $this->assertSame('strict-origin-when-cross-origin', $response->getHeaderLine('Referrer-Policy'));
        $this->assertSame('none', $response->getHeaderLine('X-Permitted-Cross-Domain-Policies'));
        $this->assertSame('max-age=15552000', $response->getHeaderLine('Strict-Transport-Security'));
    }

    public function testHstsIsNotSentOverHttp()
    {
        $response = $this->invoke($this->middleware(), $this->request('/', [
            'HTTPS' => 'off',
            'REQUEST_SCHEME' => 'http'
        ]));

        $this->assertFalse($response->hasHeader('Strict-Transport-Security'));
    }

    public function testHstsIsSentForProxiedHttps()
    {
        $response = $this->invoke($this->middleware(), $this->request('/', [
            'HTTPS' => 'off',
            'REQUEST_SCHEME' => 'http',
            'HTTP_X_FORWARDED_PROTO' => 'https'
        ]));

        $this->assertTrue($response->hasHeader('Strict-Transport-Security'));
    }

    public function testHstsOptions()
    {
        $response = $this->invoke($this->middleware([
            'hsts' => [
                'max_age' => 63072000,
                'include_subdomains' => true,
                'preload' => true
            ]
        ]), $this->request('/', [ 'HTTPS' => 'on' ]));

        $this->assertSame(
            'max-age=63072000; includeSubDomains; preload',
            $response->getHeaderLine('Strict-Transport-Security')
        );
    }

    public function testCspReportOnly()
    {
        $response = $this->invoke($this->middleware([
            'csp' => [
                'enabled' => 'report-only',
                'directives' => [
                    'default-src' => [ "'self'" ],
                    'object-src' => [ "'none'" ],
                    'upgrade-insecure-requests' => true
                ]
            ]
        ]), $this->request('/', [ 'HTTPS' => 'on' ]));

        $this->assertTrue($response->hasHeader('Content-Security-Policy-Report-Only'));
        $this->assertFalse($response->hasHeader('Content-Security-Policy'));
        $this->assertSame(
            "default-src 'self'; object-src 'none'; upgrade-insecure-requests",
            $response->getHeaderLine('Content-Security-Policy-Report-Only')
        );
    }

    public function testCspEnforced()
    {
        $response = $this->invoke($this->middleware([
            'csp' => [
                'enabled' => true,
                'directives' => [
                    'default-src' => [ "'self'" ]
                ]
            ]
        ]), $this->request('/', [ 'HTTPS' => 'on' ]));

        $this->assertTrue($response->hasHeader('Content-Security-Policy'));
        $this->assertFalse($response->hasHeader('Content-Security-Policy-Report-Only'));
    }

    public function testExcludedPath()
    {
        $middleware = $this->middleware([
            'excluded_path' => [ '^/admin' ]
        ]);

        $response = $this->invoke($middleware, $this->request('/admin/login', [ 'HTTPS' => 'on' ]));

        $this->assertFalse($response->hasHeader('X-Content-Type-Options'));
    }

    public function testProfileOverridesHeaders()
    {
        $middleware = $this->middleware([
            'profiles' => [
                '^/admin' => [
                    'headers' => [
                        'X-Frame-Options' => 'DENY'
                    ],
                    'csp' => [
                        'enabled' => false
                    ]
                ]
            ],
            'csp' => [
                'enabled' => 'report-only',
                'directives' => [
                    'default-src' => [ "'self'" ]
                ]
            ]
        ]);

        $adminResponse = $this->invoke($middleware, $this->request('/admin/login', [ 'HTTPS' => 'on' ]));
        $publicResponse = $this->invoke($middleware, $this->request('/', [ 'HTTPS' => 'on' ]));

        $this->assertSame('DENY', $adminResponse->getHeaderLine('X-Frame-Options'));
        $this->assertFalse($adminResponse->hasHeader('Content-Security-Policy-Report-Only'));
        $this->assertSame('SAMEORIGIN', $publicResponse->getHeaderLine('X-Frame-Options'));
        $this->assertTrue($publicResponse->hasHeader('Content-Security-Policy-Report-Only'));
    }

    public function testClearSiteDataIsPathScoped()
    {
        $middleware = $this->middleware([
            'clear_site_data' => [
                'paths' => [ '^/logout$' ]
            ]
        ]);

        $logoutResponse = $this->invoke($middleware, $this->request('/logout', [ 'HTTPS' => 'on' ]));
        $homeResponse = $this->invoke($middleware, $this->request('/', [ 'HTTPS' => 'on' ]));

        $this->assertSame(
            '"cache", "cookies", "storage", "executionContexts"',
            $logoutResponse->getHeaderLine('Clear-Site-Data')
        );
        $this->assertFalse($homeResponse->hasHeader('Clear-Site-Data'));
    }

    public function testHeadersCanBeDisabled()
    {
        $response = $this->invoke($this->middleware([
            'headers' => [
                'X-Frame-Options' => false
            ],
            'hsts' => [
                'enabled' => false
            ],
            'use_native_headers' => false
        ]), $this->request('/', [ 'HTTPS' => 'on' ]));

        $this->assertFalse($response->hasHeader('X-Frame-Options'));
        $this->assertFalse($response->hasHeader('Strict-Transport-Security'));
    }

    /**
     * @param  array $config The middleware configuration.
     * @return SecurityHeadersMiddleware
     */
    private function middleware(array $config = []): SecurityHeadersMiddleware
    {
        return new SecurityHeadersMiddleware($config);
    }

    /**
     * @param  string $uri    The request URI.
     * @param  array  $server Additional server parameters.
     * @return Request
     */
    private function request(string $uri, array $server = []): Request
    {
        $environment = Environment::mock(array_merge([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => $uri,
            'HTTP_HOST'      => 'example.com',
            'HTTPS'          => 'on'
        ], $server));

        return Request::createFromEnvironment($environment);
    }

    /**
     * @param  SecurityHeadersMiddleware $middleware The middleware.
     * @param  Request                   $request    The request.
     * @return Response
     */
    private function invoke(SecurityHeadersMiddleware $middleware, Request $request): Response
    {
        $next = function ($request, $response) {
            return $response;
        };

        return $middleware($request, new Response(), $next);
    }
}
