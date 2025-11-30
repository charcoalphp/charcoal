<?php

namespace Charcoal\Tests\Translation\ServiceProvider;

use Charcoal\App\AppConfig;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\RequestInterface as ClientRequestInterface;
use DI\Container;
use Charcoal\Translator\Middleware\LanguageMiddleware;
use Charcoal\Translator\ServiceProvider\TranslatorServiceProvider;
use Charcoal\Tests\Translator\AbstractTestCase;
use Psr\Http\Server\RequestHandlerInterface;

#[CoversClass(LanguageMiddleware::class)]
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
            'translator'       => $container->get('translator'),
            'browser_language' => $container->get('locales/browser-language'),
            'default_language' => $container->get('translator')->getLocale(),
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

            $this->container->set('config', new AppConfig([
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
            ]));

            (new TranslatorServiceProvider())->register($this->container);
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

        $uri->expects($this->any())->method('getPath')->willReturn($path);
        $uri->expects($this->any())->method('getQuery')->willReturn(http_build_query($params));

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

        $request->expects($this->any())->method('getUri')->willReturn($this->mockUri($path));
        $request->expects($this->any())->method('getRequestTarget')->willReturn($path);
        $request->expects($this->any())->method('getQueryParams')->willReturn($params);

        return $request;
    }

    /**
     * @return RequestHandlerInterface
     */
    private function mockRequestHandler()
    {
        $handler = $this->createMock(RequestHandlerInterface::class);

        return $handler;
    }

    /**
     * @return void
     */
    public function testInvoke()
    {
        $request  = $this->mockRequest('/fr/foo/bar');
        $handler  = $this->mockRequestHandler();

        $return = call_user_func([ $this->obj, '__invoke' ], $request, $handler);
        $this->assertEquals($handler->handle($request), $return);
    }

    /**
     * @return void
     */
    public function testInvokeWithExcludedPath()
    {
        $request  = $this->mockRequest('/admin/foo/bar');
        $handler  = $this->mockRequestHandler();

        $return = call_user_func([ $this->obj, '__invoke' ], $request, $handler);
        $this->assertEquals($handler->handle($request), $return);
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
        $request->expects($this->any())->method('getUri')->willReturn($this->mockUri('/jp/foo/bar'));
        $request->expects($this->any())->method('getRequestTarget')->willReturn('/jp/foo/bar');

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
        $uri->expects($this->any())->method('getHost')->willReturn('fr.example.com');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn($uri);

        $return = $this->callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('fr', $return);

        $uri = $this->createMock(UriInterface::class);
        $uri->expects($this->any())->method('getHost')->willReturn('jp.example.com');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn($uri);

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
        $uri->expects($this->any())->method('getHost')->willReturn('jp.example.com');

        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects($this->any())->method('getUri')->willReturn($uri);

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
