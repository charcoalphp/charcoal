<?php

namespace Charcoal\User\Tests\Acl;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From Pimple
use Pimple\Container;

// From 'zendframework/zend-permissions'
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

use Charcoal\User\Acl\Manager;
use Charcoal\User\Tests\ContainerProvider;

/**
 *
 */
class ManagerTest extends PHPUnit_Framework_TestCase
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
     */
    public function setUp()
    {
        $container = $this->container();

        $this->obj = new Manager([
            'logger' => $container['logger']
        ]);
    }

    public function testLoadPermissions()
    {
        $acl = new Acl();
        $acl->addResource(new Resource('phpunit'));

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

    public function testLoadPermissionsWithStringPermissions()
    {
        $acl = new Acl();
        $acl->addResource(new Resource('phpunit'));

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
            $containerProvider->registerAcl($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
