<?php

namespace Charcoal\Tests\Object;

// From 'charcoal-object'
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Object\Mocks\CategoryTraitTestDouble;

/**
 *
 */
class CategoryTraitTest extends AbstractTestCase
{
    /**
     * Set up the test.
     */
    public function createTrait()
    {
        return $this->createPartialMock(CategoryTraitTestDouble::class, [ 'loadCategoryItems' ]);
    }

    public function testUnsetCategoryItemTypeThrowsException(): void
    {
        $mock = $this->createTrait();

        $this->expectException('\Exception');
        $mock->getCategoryItemType();
    }

    public function testSetCategoryItemType(): void
    {
        $mock = $this->createTrait();

        $ret = $mock->setCategoryItemType('foobar');
        $this->assertSame($ret, $mock);
        $this->assertEquals('foobar', $mock->getCategoryItemType());

        $this->expectException('\InvalidArgumentException');
        $mock->setCategoryItemType(false);
    }

    public function testNumCategoryItems(): void
    {
        $mock = $this->createTrait();
        $mock->expects($this->once())
            ->method('loadCategoryItems')
            ->willReturn([]);

        $this->assertEquals(0, $mock->getNumCategoryItems());

        $mock = $this->createTrait();
        $mock->expects($this->once())
            ->method('loadCategoryItems')
            ->willReturn([ 'item' ]);

        $this->assertEquals(1, $mock->getNumCategoryItems());
    }

    public function testHasCategoryItems(): void
    {
        $mock = $this->createTrait();
        $mock->expects($this->once())
            ->method('loadCategoryItems')
            ->willReturn([]);

        $this->assertFalse($mock->hasCategoryItems());

        $mock = $this->createTrait();
        $mock->expects($this->once())
            ->method('loadCategoryItems')
            ->willReturn([ 'item' ]);

        $this->assertTrue($mock->hasCategoryItems());
    }
}
