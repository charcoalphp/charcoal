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
    private $obj;

    /**
     * Set up the test.
     *
     * @return void
     */
    public function setUp(): void
    {
        $this->obj = new HierarchicalObject();
    }

    /**
     * @return void
     */
    public function testSetMaster()
    {
        $obj = $this->obj;
        // $master = $this->createMock(get_class($obj));
        $master = '86619ad9';
        $ret = $obj->setMaster($master);
        $this->assertEquals($ret, $obj);
        $this->assertSame($master, $obj->getMaster());
    }

    /**
     * @return void
     */
    public function testHasMaster()
    {
        $obj = $this->obj;
        $this->assertFalse($obj->hasMaster());

        $master = $this->createMock(get_class($obj));
        $obj->setMaster($master);
        $this->assertTrue($obj->hasMaster());
    }

    /**
     * @return void
     */
    public function testIsTopLevel()
    {
        $obj = $this->obj;
        $this->assertTrue($obj->isTopLevel());

        $master = $this->createMock(get_class($obj));
        $obj->setMaster($master);
        $this->assertFalse($obj->isTopLevel());
    }

    /**
     * @return void
     */
    public function testIsLastLevel()
    {
        $obj = $this->obj;
        $this->assertTrue($obj->isLastLevel());

        $children = array_fill(0, 4, $this->createMock(get_class($obj)));
        $obj->setChildren($children);
        $this->assertFalse($obj->isLastLevel());
    }

    /**
     * @return void
     */
    public function testHierarchyLevel()
    {
        $obj = $this->obj;
        // No longer easily testable because of modelLoader.
        // $this->assertEquals(1, $obj->hierarchyLevel());

        $master = '86619ad9';
        $children = array_fill(0, 4, $this->createMock(get_class($obj)));
        $obj->setMaster($master);
        $obj->setChildren($children);
        // No longer easily testable because of modelLoader.
        // $this->assertEquals(2, $obj->hierarchyLevel());

        $master2 = '49757d4f';
        // $obj->getMasterObject()->setMaster($master2);

        //$this->assertEquals(3, $obj->hierarchyLevel());
    }

    /**
     * @return void
     */
    public function testToplevelMaster()
    {
        $obj = $this->obj;

        $this->assertSame(null, $obj->toplevelMaster());

        $master1 = $this->createMock(get_class($obj));
        $master2 = $this->createMock(get_class($obj));

        $obj->setMaster($master1->id());
        // No longer easily testable because of modelLoader.
        // $this->assertSame($master1, $obj->toplevelMaster());

        $master1->setMaster($master2->id());
        $obj->setMaster($master1->id());
        // No longer easily testable because of modelLoader.
        // $this->assertSame($master2, $obj->toplevelMaster());
    }

    /**
     * @return void
     */
    public function testHierarchy()
    {
        $obj = $this->createPartialMock(get_class($this->obj), ['getMasterObject']);
        $this->assertEquals([], $obj->hierarchy());

        $master1 = $this->createPartialMock(get_class($this->obj), ['getMasterObject']);
        $master2 = $this->createTestProxy(get_class($this->obj));

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

    /**
     * @return void
     */
    public function testInvertedHierarchy()
    {
        $obj = $this->obj;
        // No longer easily testable because of modelLoader.
        // $this->assertEquals([], $obj->invertedHierarchy());

        // $master1 = $this->createMock(get_class($obj));
        // $master2 = $this->createMock(get_class($obj));

        // $obj->setMaster($master1);
        // No longer easily testable because of modelLoader.
        // $this->assertSame([$master1], $obj->invertedHierarchy());

        // $master1->setMaster($master2);
        //$this->assertSame([$master2, $master1], $obj->invertedHierarchy());
    }

    /**
     * @return void
     */
    public function testIsMasterOf()
    {
        $obj = $this->obj;
        $master = $this->createTestProxy(get_class($obj));

        $this->assertFalse($master->isMasterOf($obj));
        $obj->setMaster($master->getId());
        $this->assertTrue($master->isMasterOf($obj));
        $this->assertFalse($obj->isMasterOf($master));
    }

    /**
     * @return void
     */
    public function testHasChildren()
    {
        $obj = $this->obj;
        $this->assertFalse($obj->hasChildren());

        $children = array_fill(0, 4, $this->createMock(get_class($obj)));
        $obj->setChildren($children);
        $this->assertTrue($obj->hasChildren());
    }

    /**
     * @return void
     */
    public function testNumChildren()
    {
        $obj = $this->obj;
        $this->assertEquals(0, $obj->numChildren());


        $children = array_fill(0, 4, $this->createMock(get_class($obj)));
        $obj->setChildren($children);
        $this->assertEquals(4, $obj->numChildren());

        $child5 = $this->createMock(get_class($obj));
        $obj->addChild($child5);
        $this->assertEquals(5, $obj->numChildren());
    }

    /**
     * @return void
     */
    public function testIsChildOf()
    {
        $obj = $this->obj;
        $master = $this->createTestProxy(get_class($obj));

        $this->assertFalse($obj->isChildOf($master));
        $obj->setMaster($master->getId());
        $this->assertTrue($obj->isChildOf($master));
    }

    /**
     * @return void
     */
    public function testRecurisveIsChildOf()
    {
        $obj = $this->obj;
        $master = $this->createTestProxy(get_class($obj));

        $this->assertFalse($obj->isChildOf($master));
        $obj->setMaster($master->getId());
        $this->assertTrue($obj->isChildOf($master));
    }
}
