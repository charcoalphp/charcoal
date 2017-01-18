<?php

namespace Charcoal\User\Tests\Acl;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;

use Charcoal\User\Acl\PermissionCategory;

/**
 *
 */
class PermissionCategoryTest extends PHPUnit_Framework_TestCase
{
    private $obj;

    public function setUp()
    {
        $this->obj = new PermissionCategory([
            'logger' => new NullLogger()
        ]);
    }

    public function testSetName()
    {
        $ret = $this->obj->setName('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->name());
    }
}
