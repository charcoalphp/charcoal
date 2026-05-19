<?php

namespace Charcoal\Tests\Config\Entity;

use ArrayAccess;

// From 'charcoal-config'
use Charcoal\Tests\Config\Entity\AbstractEntityTestCase;
use Charcoal\Tests\Config\Mixin\ArrayAccessTestTrait;
use Charcoal\Tests\Config\Mock\MacroEntity;
use Charcoal\Config\AbstractEntity;

/**
 * Test ArrayAccess implementation in AbstractEntity
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Charcoal\Config\AbstractEntity::class)]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractEntity::class, 'offsetExists()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractEntity::class, 'offsetGet()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractEntity::class, 'offsetSet()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractEntity::class, 'offsetUnset()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Tests\Config\Mock\MacroEntity::class, 'foo()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Tests\Config\Mock\MacroEntity::class, 'setFoo()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractEntity::class, 'has()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractEntity::class, 'get()')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Config\AbstractEntity::class, 'set()')]
class EntityArrayAccessTest extends AbstractEntityTestCase
{
    use ArrayAccessTestTrait;

    /**
     * @var MacroEntity
     */
    public $obj;

    /**
     * Create a concrete MacroEntity instance.
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
     * @return MacroEntity
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testArrayAccess()
    {
        $this->assertInstanceOf(ArrayAccess::class, $this->obj);
        return $this->obj;
    }



    // Test ArrayAccess on non-private properties
    // =========================================================================
    public function testOffsetExists(): void
    {
        $obj = $this->obj;

        // MacroEntity::$name
        $this->assertTrue(property_exists($obj, 'name'));
        $this->assertTrue(isset($obj['name']));

        // MacroEntity::foo()
        $this->assertTrue(isset($obj['foo']));

        // MacroEntity::getErd()
        $this->assertTrue(isset($obj['erd']));
    }

    public function testOffsetGet(): void
    {
        $obj = $this->obj;

        // MacroEntity::$name
        $this->assertEquals('Charcoal', $obj['name']);

        // MacroEntity::foo()
        $this->assertEquals('foo is 20', $obj['foo']);

        // MacroEntity::getErd()
        $this->assertEquals(true, $obj['erd']);
    }

    public function testOffsetSet(): void
    {
        $obj = $this->obj;

        $obj['baz'] = 'waldo';
        $this->assertTrue(property_exists($obj, 'baz'));
        $this->assertEquals('waldo', $obj['baz']);
    }

    public function testOffsetUnset(): void
    {
        $obj = $this->obj;

        unset($obj['name']);
        $this->assertTrue(property_exists($obj, 'name'));
        $this->assertNull($obj['name']);
    }



    // Test ArrayAccess on encapsulated properties
    // =========================================================================
    public function testOffsetExistsOnEncapsulatedMethod(): void
    {
        $obj = $this->obj;

        $this->assertTrue(property_exists($obj, 'foo'));
        $this->assertTrue(isset($obj['foo']));
    }

    public function testOffsetGetOnEncapsulatedMethod(): void
    {
        $obj = $this->obj;

        $this->assertEquals('foo is 20', $obj['foo']);
    }

    public function testOffsetSetOnEncapsulatedMethod(): void
    {
        $obj = $this->obj;

        $obj['foo'] = 32;
        $this->assertEquals('foo is 42', $obj['foo']);
    }

    public function testOffsetUnsetOnEncapsulatedMethod(): void
    {
        $obj = $this->obj;

        unset($obj['foo']);
        $this->assertTrue(property_exists($obj, 'foo'));
        $this->assertEquals('foo is 10', $obj['foo']);
    }



    // Test ArrayAccess via aliases
    // =========================================================================
    public function testHas(): void
    {
        $obj = $this->obj;

        $this->assertTrue(property_exists($obj, 'name'));
        $this->assertTrue($obj->has('name'));

        unset($obj['name']);
        $this->assertFalse($obj->has('name'));
    }

    public function testGet(): void
    {
        $obj = $this->obj;

        $this->assertEquals('Charcoal', $obj->get('name'));
    }

    public function testSet(): void
    {
        $obj = $this->obj;

        $that = $obj->set('baz', 'waldo');
        $this->assertEquals($obj, $that);
        $this->assertTrue(property_exists($obj, 'baz'));
        $this->assertEquals('waldo', $obj->get('baz'));
    }
}
