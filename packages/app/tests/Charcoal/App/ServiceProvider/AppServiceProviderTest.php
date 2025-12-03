<?php

namespace Charcoal\Tests\App\ServiceProvider;

use DI\Container;
use Charcoal\App\ServiceProvider\AppServiceProvider;
use Charcoal\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AppServiceProvider::class)]
class AppServiceProviderTest extends AbstractTestCase
{
    public function testProvider()
    {
        $container = new Container();
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
}
