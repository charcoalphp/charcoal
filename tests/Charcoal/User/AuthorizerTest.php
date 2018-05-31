<?php

namespace Charcoal\Tests\User;

// From Pimple
use Pimple\Container;

// From 'zendframework/zend-permissions'
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

// From 'charcoal-user'
use Charcoal\User\Authorizer;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\User\ContainerProvider;

/**
 *
 */
class AuthorizerTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var Authorizer
     */
    private $obj;

    /**
     * Store the ACL manager.
     *
     * @var Acl
     */
    private $acl;

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

        $this->acl = new Acl();
        $this->obj = new Authorizer([
            'logger'    => $container['logger'],
            'acl'       => $this->acl,
            'resource'  => 'test'
        ]);
    }

    /**
     * @return void
     */
    public function testRolesAllowed()
    {
        $acl = $this->acl;
        $acl->addResource('test');

        $acl->addRole('foo');
        $this->assertFalse($this->obj->rolesAllowed([ 'foo' ], [ 'bar' ]));

        $acl->allow('foo', 'test', 'bar');
        $this->assertTrue($this->obj->rolesAllowed([ 'foo' ], [ 'bar' ]));

        $this->assertFalse($this->obj->rolesAllowed([ null ], [ 'bar' ]));

        $acl->allow(null, 'test');
        $this->assertTrue($this->obj->rolesAllowed([ null ], [ 'bar' ]));
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
