<?php

namespace Charcoal\Tests\App\ServiceProvider;

use DI\Container;

use Charcoal\App\ServiceProvider\DatabaseServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class DatabaseServiceProviderTest extends AbstractTestCase
{
    public function testProvider()
    {
        $container = new Container([
            'config' => []
        ]);
        $provider = new DatabaseServiceProvider();
        $provider->register($container);

        $this->assertTrue($container->has('databases/config'));
        $this->assertTrue($container->has('databases'));
        $this->assertTrue($container->has('database/config'));
        $this->assertTrue($container->has('database'));
    }
}
