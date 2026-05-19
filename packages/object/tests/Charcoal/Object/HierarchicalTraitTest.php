<?php

namespace Charcoal\Tests\Object;

// From 'charcoal-object'
use Charcoal\Object\HierarchicalTrait;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Object\ContainerProvider;
use Charcoal\Tests\Object\Mocks\HierarchicalClass as HierarchicalObject;

/**
 *
 */
class HierarchicalTraitTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var Hierarchical
     */
    private \Charcoal\Tests\Object\Mocks\HierarchicalClass $obj;

    /**
     * Set up the test.
     */
    public function setUp(): void
    {
        $this->obj = new HierarchicalObject();
    }

    public function testSetMaster(): void
    {
        $obj = $this->obj;
        // $master = $this->createMock(get_class($obj));
        $master = '86619ad9';
        $ret = $obj->setMaster($master);
        $this->assertEquals($ret, $obj);
        $this->assertSame($master, $obj->getMaster());
    }

    public function testHasMaster(): void
    {
        $obj = $this->obj;
        $this->assertFalse($obj->hasMaster());

        $master = $this->createStub($obj::class);
        $obj->setMaster($master);
        $this->assertTrue($obj->hasMaster());
    }

    public function testIsTopLevel(): void
    {
        $obj = $this->obj;
        $this->assertTrue($obj->isTopLevel());

        $master = $this->createStub($obj::class);
        $obj->setMaster($master);
        $this->assertFalse($obj->isTopLevel());
    }

    public function testIsLastLevel(): void
    {
        $obj = $this->obj;
        $this->assertTrue($obj->isLastLevel());

        $children = array_fill(0, 4, $this->createMock($obj::class));
        $obj->setChildren($children);
        $this->assertFalse($obj->isLastLevel());
    }

    public function testHierarchyLevel(): void
    {
        $obj = $this->obj;

        $this->assertEquals(1, $obj->hierarchyLevel());

        $master = clone $obj;
        $master2 = clone $obj;
        $children = array_fill(0, 4, $this->createMock($obj::class));

        $obj->setMaster($master);
        $obj->setChildren($children);

        $this->assertEquals(2, $obj->hierarchyLevel());

        $obj->getMasterObject()->setMaster($master2);

        $this->assertEquals(2, $obj->hierarchyLevel());
        $obj->resetHierarchy();
        $this->assertEquals(3, $obj->hierarchyLevel());
    }

    public function testToplevelMaster(): void
    {
        $obj = $this->obj;

        $this->assertSame(null, $obj->toplevelMaster());

        $master1 = $this->createMock($obj::class);
        $master2 = $this->createMock($obj::class);

        $obj->setMaster($master1->id());
        // No longer easily testable because of modelLoader.
        // $this->assertSame($master1, $obj->toplevelMaster());

        $master1->setMaster($master2->id());
        $obj->setMaster($master1->id());
        // No longer easily testable because of modelLoader.
        // $this->assertSame($master2, $obj->toplevelMaster());
    }

    public function testHierarchy(): void
    {
        $obj = $this->createPartialMock($this->obj::class, ['getMasterObject']);
        $this->assertEquals([], $obj->hierarchy());

        $master1 = $this->createPartialMock($this->obj::class, ['getMasterObject']);
        $master2 = $this->createTestProxy($this->obj::class);

        $obj->setMaster($master1->getId());
        $obj->method('getMasterObject')->willReturn($master1);
        // No longer easily testable because of modelLoader.
        $this->assertSame([$master1], $obj->hierarchy());

        $master1->setMaster($master2->getId());
        $master1->method('getMasterObject')->willReturn($master2);
        // Force refresh teh hierarchy
        $obj->setMaster($master1->getId());
        $this->assertSame([$master1, $master2], $obj->hierarchy());
    }

    public function testInvertedHierarchy(): void
    {
        $obj = $this->obj;

        $this->assertEquals([], $obj->invertedHierarchy());

        $master1 = clone $obj;
        $master2 = clone $obj;

        $obj->setMaster($master1);
        $this->assertSame([$master1], $obj->invertedHierarchy());

        $master1->setMaster($master2);
        $obj->resetHierarchy();
        $this->assertSame([$master2, $master1], $obj->invertedHierarchy());
    }

    public function testIsMasterOf(): void
    {
        $obj = $this->obj;
        $master = $this->createTestProxy($obj::class);

        $this->assertFalse($master->isMasterOf($obj));
        $obj->setMaster($master->getId());
        $this->assertTrue($master->isMasterOf($obj));
        $this->assertFalse($obj->isMasterOf($master));
    }

    public function testHasChildren(): void
    {
        $obj = $this->obj;
        $this->assertFalse($obj->hasChildren());

        $children = array_fill(0, 4, $this->createMock($obj::class));
        $obj->setChildren($children);
        $this->assertTrue($obj->hasChildren());
    }

    public function testNumChildren(): void
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numChildren());


        $children = array_fill(0, 4, $this->createMock($obj::class));
        $obj->setChildren($children);
        $this->assertEquals(4, $obj->numChildren());

        $child5 = $this->createStub($obj::class);
        $obj->addChild($child5);
        $this->assertEquals(5, $obj->numChildren());
    }

    public function testIsChildOf(): void
    {
        $obj = $this->obj;
        $master = $this->createTestProxy($obj::class);

        $this->assertFalse($obj->isChildOf($master));
        $obj->setMaster($master->getId());
        $this->assertTrue($obj->isChildOf($master));
    }

    public function testRecurisveIsChildOf(): void
    {
        $obj = $this->obj;
        $master = $this->createTestProxy($obj::class);

        $this->assertFalse($obj->isChildOf($master));
        $obj->setMaster($master->getId());
        $this->assertTrue($obj->isChildOf($master));
    }
}
