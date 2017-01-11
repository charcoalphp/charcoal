<?php

namespace Charcoal\Tests\View;

use PHPUnit_Framework_TestCase;

use Pimple\Container;

use Charcoal\View\ViewServiceProvider;

/**
 *
 */
class ViewServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public function testProvider()
    {
        $container = new Container([
            'config' => []
        ]);
        $provider = new ViewServiceProvider();
        $provider->register($container);

        $this->assertTrue(isset($container['view/config']));
        $this->assertTrue(isset($container['view/engine']));
        $this->assertTrue(isset($container['view/renderer']));
        $this->assertTrue(isset($container['view']));
    }
}
