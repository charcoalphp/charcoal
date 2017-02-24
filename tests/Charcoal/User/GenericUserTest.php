<?php

namespace Charcoal\User\Tests;

use DateTime;

// From Pimple
use Pimple\Container;

// From 'charcoal-user'
use Charcoal\User\GenericUser;
use Charcoal\User\UserInterface;
use Charcoal\User\Tests\ContainerProvider;

/**
 *
 */
class GenericUserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var UserInterface
     */
    private $obj;

    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        if (session_id()) {
            session_unset();
        }

        $container = $this->container();

        $this->obj = new GenericUser([
            # 'container'        => $container,
            'logger'           => $container['logger'],
            'translator'       => $container['translator'],
            # 'property_factory' => $container['property/factory'],
            # 'metadata_loader'  => $container['metadata/loader']
        ]);
    }

    public function testSessionKey()
    {
        $obj = $this->obj;

        $sessionKey = $obj::sessionKey();
        $this->assertSame('charcoal.user', $sessionKey);
    }

    public function testSaveToSession()
    {
        $obj = $this->obj;

        $sessionKey = $obj::sessionKey();
        $this->obj['username'] = 'foo';
        $this->obj->saveToSession();
        $this->assertEquals($_SESSION[$sessionKey], $this->obj['username']);
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerModelFactory($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
