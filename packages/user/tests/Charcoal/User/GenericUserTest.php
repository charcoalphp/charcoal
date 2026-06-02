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
     */
    private \Charcoal\User\GenericUser $obj;

    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    /**
     * Set up the test.
     */
    protected function setUp(): void
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

    public function testSessionKey(): void
    {
        $obj = $this->obj;

        $sessionKey = $obj::sessionKey();
        $this->assertSame('charcoal.user', $sessionKey);
    }

    /**
     * Set up the service container.
     */
    private function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerModelFactory($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
