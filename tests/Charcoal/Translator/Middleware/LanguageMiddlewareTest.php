<?php

namespace Charcoal\Tests\Translation\ServiceProvider;

use ReflectionClass;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From PSR-7
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\RequestInterface as ClientRequestInterface;
use Psr\Http\Message\ResponseInterface;

// From Pimple
use Pimple\Container;

// From `charcoal-translator`
use Charcoal\Translator\Middleware\LanguageMiddleware;
use Charcoal\Translator\ServiceProvider\TranslatorServiceProvider;
use Charcoal\Tests\Translator\ContainerProvider;

/**
 *
 */
class LanguageMiddlewareTest extends PHPUnit_Framework_TestCase
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

    public static function setupBeforeClass()
    {
        if (!isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-CH, fr;q=0.9, en;q=0.8, de;q=0.7, *;q=0.5';
        }
    }

    public static function teardownAfterClass()
    {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        }
    }

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new LanguageMiddleware([
            'translator'       => $container['translator'],
            'browser_language' => $container['locales/browser-language'],
            'default_language' => $container['translator']->getLocale(),
            'use_params'       => true
        ]);
    }

    /**
     * @return Container
     */
    private function getContainer()
    {
        if ($this->container === null) {
            $this->container = new Container();

            $this->container['config'] = [
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
                        '/fixtures/translations'
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
            ];

            $this->container->register(new TranslatorServiceProvider());
        }

        return $this->container;
    }

    /**
     * @param  string $path
     * @param  array  $params
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
     * @param  string $path
     * @param  array  $params
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

    public static function getMethod($obj, $name)
    {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public static function callMethod($obj, $name, array $args = [])
    {
        $method = static::getMethod($obj, $name);

        return $method->invokeArgs($obj, $args);
    }

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

    public function testGetLanguageWithServerRequest()
    {
        $request = $this->mockRequest('/fr/foo/bar');
        $return  = static::callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('fr', $return);

        $request = $this->mockRequest('/jp/foo/bar', [ 'current_language' => 'fr' ]);
        $return  = static::callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('fr', $return);

        $_SESSION['current_language'] = 'fr';
        $request = $this->mockRequest();
        $return  = static::callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('fr', $return);
        unset($_SESSION['current_language']);

        $request = $this->mockRequest();
        $return  = static::callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('fr', $return);
    }

    public function testGetLanguageWithClientRequest()
    {
        $request = $this->createMock(ClientRequestInterface::class);
        $request->expects($this->any())->method('getUri')->will($this->returnValue($this->mockUri('/jp/foo/bar')));
        $request->expects($this->any())->method('getRequestTarget')->will($this->returnValue('/jp/foo/bar'));

        $return  = static::callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('fr', $return);
    }

    public function testGetLanguageUseDefault()
    {
        $container = $this->getContainer();

        $this->obj = new LanguageMiddleware([
            'translator'       => $container['translator'],
            'browser_language' => null,
            'default_language' => $container['translator']->getLocale()
        ]);

        $request = $this->mockRequest();
        $return  = static::callMethod($this->obj, 'getLanguage', [ $request ]);
        $this->assertEquals('en', $return);
    }
}
