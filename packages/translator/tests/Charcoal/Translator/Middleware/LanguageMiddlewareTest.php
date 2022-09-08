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
     *
     * @var LanguageMiddleware
     */
    private $obj;

    /**
     * Service Container.
     *
     * @var Container
     */
    private $container;

    /**
     * @return void
     */
    public static function setupBeforeClass(): void
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5';
        }
    }

    /**
     * @return void
     */
    public static function teardownAfterClass(): void
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }
    }

    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->obj = $this->middlewareFactory([
            'use_params' => true
        ]);
    }

    /**
     * Create LanguageMiddleware.
     *
     * @param  array $data Extra options to pass to the middleare.
     * @return LanguageMiddleware
     */
    protected function middlewareFactory(array $data = [])
    {
        $container = $this->getContainer();

        $defaults = [
            'translator'       => $container['translator'],
            'browser_language' => $container['locales/browser-language'],
            'default_language' => $container['translator']->getLocale(),
        ];

        $middleware = new LanguageMiddleware(array_replace($defaults, $data));

        return $middleware;
    }

    /**
     * @return Container
     */
    private function getContainer()
    {
        if ($this->container === null) {
            $this->container = new Container();

            $this->container['config'] = new AppConfig([
                'base_path' => realpath(__DIR__.'/../../..'),
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
    private function mockUri($path = '', array $params = [])
    {
        $uri = $this->createMock(UriInterface::class);

        $uri->expects($this->any())->method('getPath')->will($this->returnValue($path));
        $uri->expects($this->any())->method('getQuery')->will($this->returnValue(http_build_query($params)));

        return $uri;
    }

    /**
     * @param  string $path   The URI path.
     * @param  array  $params The URI query string parameters.
     * @return ServerRequestInterface
     */
    private function mockRequest($path = '', array $params = [])
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $request->expects($this->any())->method('getUri')->will($this->returnValue($this->mockUri($path)));
        $request->expects($this->any())->method('getRequestTarget')->will($this->returnValue($path));
        $request->expects($this->any())->method('getQueryParams')->will($this->returnValue($params));

        return $request;
    }

    /**
     * @return ResponseInterface
     */
    private function mockResponse()
    {
        $response = $this->createMock(ResponseInterface::class);

        return $response;
    }

    /**
     * @return void
     */
    public function testInvoke()
    {
        $request  = $this->mockRequest('/fr/foo/bar');
        $response = $this->mockResponse();
        $next     = function ($request, $response) {
            return $response;
        };

        $return = call_user_func([ $this->obj, '__invoke' ], $request, $response, $next);
        $this->assertEquals($response, $return);
    }

    /**
     * @return void
     */
    public function testInvokeWithExcludedPath()
    {
        $request  = $this->mockRequest('/admin/foo/bar');
        $response = $this->mockResponse();
        $next     = function ($request, $response) {
            return $response;
        };

        $return = call_user_func([ $this->obj, '__invoke' ], $request, $response, $next);
        $this->assertEquals($response, $return);
    }

    /**
     * @return void
     */
    public function testGetLanguageWithServerRequest()
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

    /**
     * @return void
     */
    public function testGetLanguageWithClientRequest()
    {
        $request = $this->createMock(ClientRequestInterface::class);
        $request->expects($this->any())->method('getUri')->will($this->returnValue($this->mockUri('/jp/foo/bar')));
        $request->expects($this->any())->method('getRequestTarget')->will($this->returnValue('/jp/foo/bar'));

        $return  = $this->callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('fr', $return);
    }

    /**
     * @return void
     */
    public function testGetLanguageUseHost()
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
        $uri->expects($this->any())->method('getHost')->will($this->returnValue('fr.example.com'));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->will($this->returnValue($uri));

        $return = $this->callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('fr', $return);

        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->any())->method('getHost')->will($this->returnValue('jp.example.com'));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->will($this->returnValue($uri));

        $return = $this->callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('en', $return);
    }

    /**
     * @return void
     */
    public function testGetLanguageUseHostWithBadHost()
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
        $uri->expects($this->any())->method('getHost')->will($this->returnValue('jp.example.com'));

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->will($this->returnValue($uri));

        $return = $this->callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('en', $return);
    }

    /**
     * @return void
     */
    public function testGetLanguageUseDefault()
    {
        $this->obj = $this->middlewareFactory([
            'browser_language' => null
        ]);

        $request = $this->mockRequest();
        $return  = $this->callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('en', $return);
    }
}
