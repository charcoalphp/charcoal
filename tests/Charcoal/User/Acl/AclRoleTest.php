<?php

namespace Charcoal\User\Tests\Acl;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Charcoal\User\Acl\Role;

/**
 *
 */
class RoleTest extends PHPUnit_Framework_TestCase
{
    private $obj;

    public function setUp()
    {
        $this->obj = new Role([
            'logger' => new NullLogger()
        ]);
    }

    public function testToSTring()
    {
        $this->assertEquals('', (string)$this->obj);
        $this->obj->ident = 'foobar';
        $this->assertEquals('foobar', (string)$this->obj);

        $this->obj['ident'] = 'foo';
        $this->assertEquals('foo', (string)$this->obj);
    }

    public function testSetParent()
    {
        $ret = $this->obj->setParent('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->parent());
    }

    public function testSetAllowed()
    {
        $this->assertNull($this->obj->allowed());
        $ret = $this->obj->setAllowed('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['foo'], $this->obj->allowed());

        $this->obj->setAllowed(['bar', 'baz']);
        $this->assertSame(['bar', 'baz'], $this->obj->allowed());
    }

    public function testSuperuser()
    {
        $this->assertFalse($this->obj->superuser());
        $ret = $this->obj->setSuperuser(1);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj->superuser());
    }
}
