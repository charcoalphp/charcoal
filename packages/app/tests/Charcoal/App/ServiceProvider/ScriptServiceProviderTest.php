<?php

namespace Charcoal\Tests\App\ServiceProvider;

use DI\Container;
use Charcoal\App\ServiceProvider\ScriptServiceProvider;
use Charcoal\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ScriptServiceProvider::class)]
class ScriptServiceProviderTest extends AbstractTestCase
{
    public function testProvider()
    {
        $container = new Container();
        $provider  = new ScriptServiceProvider();
        $provider->register($container);

        $this->assertTrue($container->has('script/factory'));
        $this->assertTrue($container->has('script/climate/reader'));
        $this->assertTrue($container->has('script/climate'));
    }
}
