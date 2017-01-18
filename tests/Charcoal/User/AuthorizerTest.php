<?php

namespace Charcoal\User\Tests;

use \Psr\Log\NullLogger;

// Depentendies from phpunit
use \PHPUnit_Framework_TestCase as TestCase;

// Dependencies from `zendframework/zend-permissions`
use \Zend\Permissions\Acl\Acl;
use \Zend\Permissions\Acl\Role\GenericRole as Role;
use \Zend\Permissions\Acl\Resource\GenericResource as Resource;

use \Charcoal\User\Authorizer;

/**
 *
 */
class AuthorizerTest extends TestCase
{

    public function testRolesAllowed()
    {
        $acl = new Acl();
        $acl->addResource('test');

        $this->obj = new Authorizer([
            'logger'            => new NullLogger(),
            'acl'               => $acl,
            'resource'          => 'test'
        ]);

        $acl->addRole('foo');
        $this->assertFalse($this->obj->rolesAllowed(['foo'], ['bar']));

        $acl->allow('foo', 'test', 'bar');
        $this->assertTrue($this->obj->rolesAllowed(['foo'], ['bar']));

        $this->assertFalse($this->obj->rolesAllowed([null], ['bar']));

        $acl->allow(null, 'test');
        $this->assertTrue($this->obj->rolesAllowed([null], ['bar']));
    }
}
