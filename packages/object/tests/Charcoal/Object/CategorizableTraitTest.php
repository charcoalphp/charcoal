<?php

namespace Charcoal\Tests\Object;

// From 'charcoal-object'
use Charcoal\Object\CategorizableTrait;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Object\ContainerProvider;

/**
 *
 */
class CategorizableTraitTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private $obj;

    /**
     * Set up the test.
     */
    public function setUp(): void
    {
        $this->obj = new class {
            use CategorizableTrait;
        };
    }

    public function testSetCategoryType(): void
    {
        $obj = $this->obj;
        $this->assertNull($obj->getCategoryType());

        $ret = $obj->setCategoryType('foobar');
        $this->assertSame($ret, $obj);
        $this->assertEquals('foobar', $obj->getCategoryType());

        $this->expectException('\InvalidArgumentException');
        $obj->setCategoryType(false);
    }
}
