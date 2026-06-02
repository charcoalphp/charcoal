<?php

namespace Charcoal\Tests\Translation\ServiceProvider;

use Charcoal\App\AppConfig;
use ReflectionClass;

// From PSR-7
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\RequestInterface as ClientRequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From 'charcoal-translator'
use Charcoal\Translator\Middleware\LanguageMiddleware;
use Charcoal\Translator\ServiceProvider\TranslatorServiceProvider;
use Charcoal\Tests\Translator\ContainerProvider;
use Charcoal\Tests\Translator\AbstractTestCase;

/**
 *
 */
class LanguageMiddlewareTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\Translator\Middleware\LanguageMiddleware $obj;

    /**
     * Service Container.
     */
    private \Pimple\Container|array|null $container = null;

    public static function setupBeforeClass(): void
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5';
        }
    }

    public static function teardownAfterClass(): void
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }
    }

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $this->getContainer();

        $this->obj = $this->middlewareFactory([
            'use_params' => true
        ]);
    }

    /**
     * Create LanguageMiddleware.
     *
     * @param  array $data Extra options to pass to the middleare.
     */
    protected function middlewareFactory(array $data = []): \Charcoal\Translator\Middleware\LanguageMiddleware
    {
        $container = $this->getContainer();

        $defaults = [
            'translator'       => $container['translator'],
            'browser_language' => $container['locales/browser-language'],
            'default_language' => $container['translator']->getLocale(),
        ];

        return new LanguageMiddleware(array_replace($defaults, $data));
    }

    /**
     * @return Container
     */
    private function getContainer(): \Pimple\Container|array
    {
        if ($this->container === null) {
            $this->container = new Container();

            $this->container['config'] = new AppConfig([
                'base_path' => realpath(__DIR__ . '/../../..'),
                'locales'   => [
                    'languages' => [
                        'en' => [ 'locale' => 'en-US', 'locales' => [ 'en_US.UTF-8', 'en_US.utf8', 'en_US' ] ],
                        'fr' => [ 'locale' => 'fr-FR' ]
                    ],
                    'default_language'   => 'en',
                    'fallback_languages' => [ 'en' ]
                ],
                'translator' => [
                    'paths' => [
                        '/Charcoal/Translator/Fixture/translations'
                    ],
                    'translations' => [
                        'messages' => [
                            'en' => [
                                'foo' => 'FOO'
                            ],
                            'fr' => [
                                'foo' => 'OOF'
                            ]
                        ]
                    ],
                    'auto_detect' => true,
                    'debug' => false
                ]
            ]);

            $this->container->register(new TranslatorServiceProvider());
        }

        return $this->container;
    }

    /**
     * @param  string $path   The URI path.
     * @param  array  $params The URI query string parameters.
     * @return UriInterface
     */
    private function mockUri(string $path = '', array $params = []): \PHPUnit\Framework\MockObject\MockObject
    {
        $uri = $this->createMock(UriInterface::class);

        $uri->method('getPath')->willReturn($path);
        $uri->method('getQuery')->willReturn(http_build_query($params));

        return $uri;
    }

    /**
     * @param  string $path   The URI path.
     * @param  array  $params The URI query string parameters.
     * @return ServerRequestInterface
     */
    private function mockRequest(string $path = '', array $params = []): \PHPUnit\Framework\MockObject\MockObject
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $request->method('getUri')->willReturn($this->mockUri($path));
        $request->method('getRequestTarget')->willReturn($path);
        $request->method('getQueryParams')->willReturn($params);

        return $request;
    }

    /**
     * @return ResponseInterface
     */
    private function mockResponse(): \PHPUnit\Framework\MockObject\MockObject
    {
        return $this->createMock(ResponseInterface::class);
    }

    public function testInvoke(): void
    {
        $request  = $this->mockRequest('/fr/foo/bar');
        $response = $this->mockResponse();
        $next     = (fn($request, $response) => $response);

        $return = call_user_func($this->obj->__invoke(...), $request, $response, $next);
        $this->assertEquals($response, $return);
    }

    public function testInvokeWithExcludedPath(): void
    {
        $request  = $this->mockRequest('/admin/foo/bar');
        $response = $this->mockResponse();
        $next     = (fn($request, $response) => $response);

        $return = call_user_func($this->obj->__invoke(...), $request, $response, $next);
        $this->assertEquals($response, $return);
    }

    public function testGetLanguageWithServerRequest(): void
    {
        $request = $this->mockRequest('/fr/foo/bar');
        $return  = $this->callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('fr', $return);

        $request = $this->mockRequest('/jp/foo/bar', [ 'current_language' => 'fr' ]);
        $return  = $this->callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('fr', $return);

        $_SESSION['current_language'] = 'fr';
        $request = $this->mockRequest();
        $return  = $this->callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('fr', $return);
        unset($_SESSION['current_language']);

        $request = $this->mockRequest();
        $return  = $this->callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('fr', $return);
    }

    public function testGetLanguageWithClientRequest(): void
    {
        $request = $this->createMock(ClientRequestInterface::class);
        $request->method('getUri')->willReturn($this->mockUri('/jp/foo/bar'));
        $request->method('getRequestTarget')->willReturn('/jp/foo/bar');

        $return  = $this->callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('fr', $return);
    }

    public function testGetLanguageUseHost(): void
    {
        $this->obj = $this->middlewareFactory([
            'browser_language' => null,
            'use_browser'      => false,
            'use_session'      => false,
            'use_params'       => false,
            'use_path'         => false,
            'use_host'         => true,
            'host_map'         => [
                'en' => 'en.example.com',
                'fr' => 'fr.example.com',
            ]
        ]);

        $uri = $this->createMock(UriInterface::class);

        $uri->method('getHost')->willReturn('fr.example.com');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $return = $this->callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('fr', $return);

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getHost')->willReturn('jp.example.com');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $return = $this->callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('en', $return);
    }

    public function testGetLanguageUseHostWithBadHost(): void
    {
        $this->obj = $this->middlewareFactory([
            'browser_language' => null,
            'use_browser'      => false,
            'use_session'      => false,
            'use_params'       => false,
            'use_path'         => false,
            'use_host'         => true,
            'host_map'         => [
                'en' => 'en.example.com',
                'fr' => 'fr.example.com',
            ]
        ]);

        $uri = $this->createMock(UriInterface::class);
        $uri->method('getHost')->willReturn('jp.example.com');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getUri')->willReturn($uri);

        $return = $this->callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('en', $return);
    }

    public function testGetLanguageUseDefault(): void
    {
        $this->obj = $this->middlewareFactory([
            'browser_language' => null
        ]);

        $request = $this->mockRequest();
        $return  = $this->callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('en', $return);
    }
}
