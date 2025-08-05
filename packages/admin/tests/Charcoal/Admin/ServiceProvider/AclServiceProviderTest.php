<?php

namespace Charcoal\Tests\Admin\ServiceProvider;


use DI\Container;

// From 'charcoal-admin'
use Charcoal\Admin\ServiceProvider\AclServiceProvider;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AclServiceProviderTest extends AbstractTestCase
{
    /**
     * @return void
     */
    public function testProvider()
    {
        $container = new Container([
            'config' => []
        ]);
        $provider = new AclServiceProvider();
        $provider->register($container);

        $this->assertTrue($container->has('admin/acl')
    }
}
