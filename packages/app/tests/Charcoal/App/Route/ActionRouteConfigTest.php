<?php

namespace Charcoal\Tests\App\Route;

use Charcoal\App\Route\ActionRouteConfig;
use Charcoal\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ActionRouteConfig::class)]
class ActionRouteConfigTest extends AbstractTestCase
{
    public $obj;

    public function setUp(): void
    {
        $this->obj = new ActionRouteConfig();
    }

    public function testSetActionData()
    {
        $ret = $this->obj->setActionData([ 'foo' => 'bar' ]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals([ 'foo' => 'bar' ], $this->obj->actionData());
    }
}
