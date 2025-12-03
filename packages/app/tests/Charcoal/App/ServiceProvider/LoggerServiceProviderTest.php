<?php

namespace Charcoal\Tests\App\ServiceProvider;

use DI\Container;
use Charcoal\App\ServiceProvider\LoggerServiceProvider;
use Charcoal\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LoggerServiceProvider::class)]
class LoggerServiceProviderTest extends AbstractTestCase
{
    public function testProvider()
    {
        $container = new Container([
            'config' => []
        ]);
        $provider = new LoggerServiceProvider();
        $provider->register($container);

        $this->assertTrue($container->has('logger'));
    }
}
