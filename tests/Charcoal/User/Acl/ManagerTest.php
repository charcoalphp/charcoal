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
     *
     * @var Manager
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
        $container = $this->container();

        $this->obj = new Manager([
            'logger' => $container['logger']
        ]);
    }

    /**
     * @return void
     */
    public function testLoadPermissions()
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

    /**
     * @return void
     */
    public function testLoadPermissionsWithStringPermissions()
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
     *
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseServices($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
