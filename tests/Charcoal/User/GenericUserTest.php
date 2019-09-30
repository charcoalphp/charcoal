<?php

namespace Charcoal\Tests\User;

use DateTime;

// From Pimple
use Pimple\Container;

// From 'charcoal-user'
use Charcoal\User\GenericUser;
use Charcoal\User\UserInterface;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\User\ContainerProvider;

/**
 *
 */
class GenericUserTest extends AbstractTestCase
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
     *
     * @return void
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

    /**
     * @return void
     */
    public function testSessionKey()
    {
        $obj = $this->obj;

        $sessionKey = $obj::sessionKey();
        $this->assertSame('charcoal.user', $sessionKey);
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
