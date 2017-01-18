<?php

namespace Charcoal\User\Tests\Acl;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Charcoal\User\Acl\Manager;

use Zend\Permissions\Acl\Acl;
use Zend\Permissions\Acl\Resource\GenericResource as Resource;

/**
 *
 */
class ManagerTest extends PHPUnit_Framework_TestCase
{
    private $obj;

    public function setUp()
    {
        $this->obj = new Manager([
            'logger' => new NullLogger()
        ]);
    }

    public function testLoadPermissions()
    {
        $acl = new Acl();
        $acl->addResource(new Resource('phpunit'));

        $this->obj->loadPermissions($acl, [
            'test'=>[
                'allowed'=>[
                    'foo',
                    'foobar'
                ],
                'denied'=>[
                    'baz'
                ]
            ],
            'test2'=>[
                'parent'=>'test',
                'denied'=>[
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
            'test'=>[
                'allowed'=>'foo,foobar',
                'denied'=>'baz'
            ],
            'test2'=>[
                'parent'=>'test',
                'denied'=>'foobar,baz'

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
}
