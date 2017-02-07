<?php

namespace Charcoal\User\Tests;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From Pimple
use Pimple\Container;

// From 'zendframework/zend-permissions'
use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Role\GenericRole as Role;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

// From 'charcoal-user'
use Charcoal\User\Authorizer;
use Charcoal\User\Tests\ContainerProvider;

/**
 *
 */
class AuthorizerTest extends PHPUnit_Framework_TestCase
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
     */
    public function setUp()
    {
        $container = $this->container();

        $this->acl = $container['acl'];
        $this->obj = new Authorizer([
            'logger'    => $container['logger'],
            'acl'       => $container['acl'],
            'resource'  => 'test'
        ]);
    }

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
            $containerProvider->registerAcl($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
