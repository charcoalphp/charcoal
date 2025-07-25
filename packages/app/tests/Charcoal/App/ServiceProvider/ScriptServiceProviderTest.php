<?php

namespace Charcoal\Tests\App\ServiceProvider;

use DI\Container;

use Charcoal\App\ServiceProvider\ScriptServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class ScriptServiceProviderTest extends AbstractTestCase
{
    public function testProvider()
    {
        $container = new Container();
        $provider  = new ScriptServiceProvider();
        $provider->register($container);

        $this->assertTrue(isset($container->get('script/factory')));
        $this->assertTrue(isset($container->get('script/climate/reader')));
        $this->assertTrue(isset($container->get('script/climate')));
    }
}
