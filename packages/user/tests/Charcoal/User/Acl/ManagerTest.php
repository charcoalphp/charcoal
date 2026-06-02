<?php

namespace Charcoal\Tests\User\Acl;

// From Pimple
use Pimple\Container;

// From 'laminas/laminas-permissions-acl'
use Laminas\Permissions\Acl\Acl;
use Laminas\Permissions\Acl\Resource\GenericResource as Resource;

// From 'charcoal-user'
use Charcoal\User\Acl\Manager;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\User\ContainerProvider;

/**
 *
 */
class ManagerTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\User\Acl\Manager $obj;

    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $container = $this->container();

        $this->obj = new Manager([
            'logger' => $container['logger']
        ]);
    }

    public function testLoadPermissions(): void
    {
        $acl = new Acl();
        $rsc = new Resource('phpunit');
        $acl->addResource($rsc);

        $this->obj->loadPermissions($acl, [
            'test' => [
                'allowed' => [
                    'foo',
                    'foobar'
                ],
                'denied' => [
                    'baz'
                ]
            ],
            'test2' => [
                'parent' => 'test',
                'denied' => [
                    'foobar'
                ]
            ]
        ], 'phpunit');
        $this->assertTrue($acl->hasRole('test'));
        $this->assertTrue($acl->hasRole('test2'));
        $this->assertTrue($acl->isAllowed('test', 'phpunit', 'foo'));
        $this->assertTrue($acl->isAllowed('test', 'phpunit', 'foobar'));
        $this->assertFalse($acl->isAllowed('test', 'phpunit', 'baz'));
        $this->assertTrue($acl->isAllowed('test2', 'phpunit', 'foo'));
        $this->assertFalse($acl->isAllowed('test2', 'phpunit', 'foobar'));
        $this->assertFalse($acl->isAllowed('test2', 'phpunit', 'baz'));
    }

    public function testLoadPermissionsWithStringPermissions(): void
    {
        $acl = new Acl();
        $rsc = new Resource('phpunit');
        $acl->addResource($rsc);

        $this->obj->loadPermissions($acl, [
            'test' => [
                'allowed' => 'foo,foobar',
                'denied'  => 'baz'
            ],
            'test2' => [
                'parent' => 'test',
                'denied' => 'foobar,baz'

            ]
        ], 'phpunit');
        $this->assertTrue($acl->hasRole('test'));
        $this->assertTrue($acl->hasRole('test2'));
        $this->assertTrue($acl->isAllowed('test', 'phpunit', 'foo'));
        $this->assertTrue($acl->isAllowed('test', 'phpunit', 'foobar'));
        $this->assertFalse($acl->isAllowed('test', 'phpunit', 'baz'));
        $this->assertTrue($acl->isAllowed('test2', 'phpunit', 'foo'));
        $this->assertFalse($acl->isAllowed('test2', 'phpunit', 'foobar'));
        $this->assertFalse($acl->isAllowed('test2', 'phpunit', 'baz'));
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

            $this->container = $container;
        }

        return $this->container;
    }
}
