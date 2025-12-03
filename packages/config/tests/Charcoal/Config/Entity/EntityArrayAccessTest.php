<?php

namespace Charcoal\Tests\Config\Entity;

use ArrayAccess;

// From 'charcoal-config'
use Charcoal\Tests\Config\Entity\AbstractEntityTestCase;
use Charcoal\Tests\Config\Mixin\ArrayAccessTestTrait;
use Charcoal\Tests\Config\Mock\MacroEntity;
use Charcoal\Config\AbstractEntity;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AbstractEntity::class)]
class EntityArrayAccessTest extends AbstractEntityTestCase
{
    use ArrayAccessTestTrait;

    /**
     * @var MacroEntity
     */
    public $obj;

    /**
     * Create a concrete MacroEntity instance.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->obj = $this->createEntity([
            'name' => 'Charcoal',
            'foo'  => 10,
            'erd'  => true,
        ]);
    }

    /**
     * Asserts that the object implements ArrayAccess.
     *
     * @coversNothing
     * @return MacroEntity
     */
    public function testArrayAccess()
    {
        $this->assertInstanceOf(ArrayAccess::class, $this->obj);
        return $this->obj;
    }



    // Test ArrayAccess on non-private properties
    // =========================================================================

    /**
     * @covers AbstractEntity::offsetExists()
     * @return void
     */
    public function testOffsetExists()
    {
        $obj = $this->obj;

        // MacroEntity::$name
        $this->assertObjectHasProperty('name', $obj);
        $this->assertTrue(isset($obj['name']));

        // MacroEntity::foo()
        $this->assertTrue(isset($obj['foo']));

        // MacroEntity::getErd()
        $this->assertTrue(isset($obj['erd']));
    }

    /**
     * @covers AbstractEntity::offsetGet()
     * @return void
     */
    public function testOffsetGet()
    {
        $obj = $this->obj;

        // MacroEntity::$name
        $this->assertEquals('Charcoal', $obj['name']);

        // MacroEntity::foo()
        $this->assertEquals('foo is 20', $obj['foo']);

        // MacroEntity::getErd()
        $this->assertEquals(true, $obj['erd']);
    }

    /**
     * @covers AbstractEntity::offsetSet()
     * @return void
     */
    public function testOffsetSet()
    {
        $obj = $this->obj;

        $obj['baz'] = 'waldo';
        $this->assertObjectHasProperty('baz', $obj);
        $this->assertEquals('waldo', $obj['baz']);
    }

    /**
     * @covers AbstractEntity::offsetUnset()
     * @return void
     */
    public function testOffsetUnset()
    {
        $obj = $this->obj;

        unset($obj['name']);
        $this->assertObjectHasProperty('name', $obj);
        $this->assertNull($obj['name']);
    }



    // Test ArrayAccess on encapsulated properties
    // =========================================================================

    /**
     * @covers \Charcoal\Tests\Config\Mock\MacroEntity::foo()
     * @covers AbstractEntity::offsetExists()
     * @return void
     */
    public function testOffsetExistsOnEncapsulatedMethod()
    {
        $obj = $this->obj;

        $this->assertObjectHasProperty('foo', $obj);
        $this->assertTrue(isset($obj['foo']));
    }

    /**
     * @covers \Charcoal\Tests\Config\Mock\MacroEntity::foo()
     * @covers AbstractEntity::offsetGet()
     * @return void
     */
    public function testOffsetGetOnEncapsulatedMethod()
    {
        $obj = $this->obj;

        $this->assertEquals('foo is 20', $obj['foo']);
    }

    /**
     * @covers \Charcoal\Tests\Config\Mock\MacroEntity::setFoo()
     * @covers AbstractEntity::offsetSet()
     * @return void
     */
    public function testOffsetSetOnEncapsulatedMethod()
    {
        $obj = $this->obj;

        $obj['foo'] = 32;
        $this->assertEquals('foo is 42', $obj['foo']);
    }

    /**
     * @covers \Charcoal\Tests\Config\Mock\MacroEntity::setFoo()
     * @covers AbstractEntity::offsetUnset()
     * @return void
     */
    public function testOffsetUnsetOnEncapsulatedMethod()
    {
        $obj = $this->obj;

        unset($obj['foo']);
        $this->assertObjectHasProperty('foo', $obj);
        $this->assertEquals('foo is 10', $obj['foo']);
    }



    // Test ArrayAccess via aliases
    // =========================================================================

    /**
     * @covers AbstractEntity::has()
     * @return void
     */
    public function testHas()
    {
        $obj = $this->obj;

        $this->assertObjectHasProperty('name', $obj);
        $this->assertTrue($obj->has('name'));

        unset($obj['name']);
        $this->assertFalse($obj->has('name'));
    }

    /**
     * @covers AbstractEntity::get()
     * @return void
     */
    public function testGet()
    {
        $obj = $this->obj;

        $this->assertEquals('Charcoal', $obj->get('name'));
    }

    /**
     * @covers AbstractEntity::set()
     * @return void
     */
    public function testSet()
    {
        $obj = $this->obj;

        $that = $obj->set('baz', 'waldo');
        $this->assertEquals($obj, $that);
        $this->assertObjectHasProperty('baz', $obj);
        $this->assertEquals('waldo', $obj->get('baz'));
    }
}
