<?php

namespace Charcoal\Tests\App;

// From PSR-7
use Psr\Http\Message\ResponseInterface;
// From 'charcoal-app'
use Charcoal\App\App;
use Charcoal\App\AppConfig;
use Charcoal\App\AppContainer;
use Charcoal\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Factory\ServerRequestCreatorFactory;

#[CoversClass(App::class)]
class AppTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var App
     */
    private $obj;

    /**
     * Store the service container.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Set up the test.
     */
    public function setUp(): void
    {
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $request = $serverRequestCreator->createServerRequestFromGlobals();
        $request = $request->withUri($request->getUri()->withPort(null));

        $appConfig = new AppConfig([
            'base_path'   => sys_get_temp_dir(),
            'public_path' => __DIR__,
            'settings' => [
                'displayErrorDetails' => false,
            ],
        ]);

        $this->container = new AppContainer([
            'config' => $appConfig,
            'request' => $request,
        ]);

        $app = App::instance($this->container);
        $app->setConfig($appConfig);
        $app->setBasePath('');

        $this->obj = $app;
    }

    public function testAppIsConstructed()
    {
        $this->expectException(\LogicException::class);
        $app = new App($this->container);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(App::class, $this->obj);
    }

    public function testRun()
    {
        $serverRequestCreator = ServerRequestCreatorFactory::create();
        $request = $serverRequestCreator->createServerRequestFromGlobals();
        $request = $request->withUri($request->getUri()->withPort(null));

        $response = $this->obj->getResponseFactory()->createResponse();

        $this->obj->run($request, $response);
        $this->assertInstanceOf(ResponseInterface::class, $response);
    }
}
