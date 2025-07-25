<?php

namespace Charcoal\Tests\App\ServiceProvider;

use DI\Container;

use Charcoal\App\ServiceProvider\AppServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AppServiceProviderTest extends AbstractTestCase
{
    public function testProvider()
    {
        $container = new Container();
        $provider  = new AppServiceProvider();
        $provider->register($container);

        $this->assertTrue(isset($container->get('base-url')));
        $this->assertTrue(isset($container->get('route/factory')));
        $this->assertTrue(isset($container->get('action/factory')));
        $this->assertTrue(isset($container->get('template/factory')));
        $this->assertTrue(isset($container->get('widget/factory')));
        $this->assertTrue(isset($container->get('widget/builder')));
        $this->assertTrue(isset($container->get('module/factory')));
    }
}
