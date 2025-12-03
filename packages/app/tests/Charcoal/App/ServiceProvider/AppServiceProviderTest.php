<?php

namespace Charcoal\Tests\App\ServiceProvider;

use DI\Container;
use Charcoal\App\ServiceProvider\AppServiceProvider;
use Charcoal\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Charcoal\Tests\App\ContainerProvider;
use Charcoal\App\AppContainer;
use Charcoal\App\AppConfig;
use Slim\Factory\ServerRequestCreatorFactory;

#[CoversClass(AppServiceProvider::class)]
class AppServiceProviderTest extends AbstractTestCase
{
    /** @var Container */
    private $container;

    public function testProvider()
    {
        $container = $this->container();
        $provider  = new AppServiceProvider();
        $provider->register($container);

        $this->assertTrue($container->has('base-url'));
        $this->assertTrue($container->has('route/factory'));
        $this->assertTrue($container->has('action/factory'));
        $this->assertTrue($container->has('template/factory'));
        $this->assertTrue($container->has('widget/factory'));
        $this->assertTrue($container->has('widget/builder'));
        $this->assertTrue($container->has('module/factory'));
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    private function container()
    {
        if (!isset($this->container)) {
            $appConfig = new AppConfig([
                'base_path'   => sys_get_temp_dir(),
                'public_path' => __DIR__,
            ]);

            $serverRequestCreator = ServerRequestCreatorFactory::create();
            $request = $serverRequestCreator->createServerRequestFromGlobals();
            $request = $request->withUri($request->getUri()->withPort(null));

            $container = new Container([
                'config' => $appConfig,
                'request' => $request,
                'settings' => [
                    'displayErrorDetails' => $appConfig['dev_mode'],
                ],
            ]);
            $containerProvider = new ContainerProvider();
            $containerProvider->registerLogger($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
