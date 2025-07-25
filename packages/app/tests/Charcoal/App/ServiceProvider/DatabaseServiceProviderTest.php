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

        $this->assertTrue(isset($container->get('databases/config')));
        $this->assertTrue(isset($container->get('databases')));
        $this->assertTrue(isset($container->get('database/config')));
        $this->assertTrue(isset($container->get('database')));
    }
}
