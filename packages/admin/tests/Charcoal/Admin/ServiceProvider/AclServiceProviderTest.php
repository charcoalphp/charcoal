<?php

namespace Charcoal\Tests\Admin\ServiceProvider;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\ServiceProvider\AclServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AclServiceProviderTest extends AbstractTestCase
{
    public function testProvider(): void
    {
        $container = new Container([
            'config' => []
        ]);
        $provider = new AclServiceProvider();
        $provider->register($container);

        $this->assertTrue(isset($container['admin/acl']));
    }
}
