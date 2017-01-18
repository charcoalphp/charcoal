<?php

namespace Charcoal\User\Tests\Acl;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Charcoal\User\Acl\Permission;

/**
 *
 */
class PermissionTest extends PHPUnit_Framework_TestCase
{
    private $obj;

    public function setUp()
    {
        $this->obj = new Permission([
            'logger' => new NullLogger()
        ]);
    }

    public function testSetIdent()
    {
        $ret = $this->obj->setIdent('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->ident());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setIdent(false);
    }

    public function testSetName()
    {
        $ret = $this->obj->setName('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', (string)$this->obj->name());
    }

    public function testCastToString()
    {
        $this->obj->setIdent('foobar');
        $this->assertEquals('foobar', (string)$this->obj);
        $this->obj->setIdent('baz');
        $this->assertEquals('baz', (string)$this->obj);
    }
}
