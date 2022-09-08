<?php

namespace Charcoal\Tests\Config\Entity;

// From 'charcoal-config'
use Charcoal\Tests\AssertionsTrait;
use Charcoal\Tests\Config\Entity\AbstractEntityTestCase;
use Charcoal\Tests\Config\Mock\MacroEntity;
use Charcoal\Config\AbstractEntity;

/**
 * Test AbstractEntity
 *
 * @coversDefaultClass \Charcoal\Config\AbstractEntity
 */
class EntityTest extends AbstractEntityTestCase
{
    use AssertionsTrait;

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
        $this->obj = $this->createEntity();
    }

    /**
     * Asserts that the object tracks the affected keys.
     *
     * Assertions:
     * - Keys are empty by default
     * - Keys are added automatically when setting a value via {@see ArrayAccess::offsetSet()}
     * - Keys are removed automatically when unsetting a value via {@see ArrayAccess::offsetUnset()}
     *
     * @covers ::keys()
     * @return void
     */
    public function testKeys()
    {
        $obj = $this->obj;

        $this->assertIsArray($obj->keys());
        $this->assertEmpty($obj->keys());

        $obj->set('name', 'Charcoal');
        $obj->set('type', 'library');
        $this->assertCount(2, $obj->keys());
        $this->assertArrayContains([ 'name', 'type' ], $obj->keys());

        unset($obj['name']);
        $this->assertCount(1, $obj->keys());
        $this->assertContains('type', $obj->keys());
    }



    // Test Data Methods
    // =========================================================================

    /**
     * Retrieve data for {@see AbstractEntity::setData()}.
     *
     * @used-by self::testSetData()
     * @used-by self::testGetDataSubset()
     * @return  array
     */
    public function getSetData()
    {
        return [
            'name' => 'Charcoal',
            'type' => 'library',
            'data' => [
                'key1' => 'val1',
                'key2' => 'val2',
                'key3' => 'val3',
            ],
            'foo'  => 10,
            'baz'  => null,
        ];
    }

    /**
     * Test {@see AbstractEntity::setData()}.
     *
     * Assertions:
     * - When retrieving data, the entity will ignore the key "data"
     *   to prevent recursion calls; in addition to keys with a NULL value.
     * - When assigning data, the entity will ignore the key "data"
     *   to prevent recursion calls.
     * - The key-value pair "foo" will be passed to {@see MacroEntity::setFoo()}
     *
     * @covers ::setData()
     * @covers ::data()
     * @return void
     */
    public function testSetData()
    {
        $obj = $this->obj;

        $that = $obj->setData($this->getSetData());
        $this->assertSame($obj, $that);

        $this->assertArraySubsets(
            [
                'name' => 'Charcoal',
                'type' => 'library',
                'foo'  => 'foo is 20',
            ],
            $obj->data()
        );
        $this->assertArrayContains(
            [ 'name', 'type', 'foo', 'baz' ],
            $obj->keys()
        );
    }

    /**
     * Test {@see AbstractEntity::data()}.
     *
     * Assertions:
     * - The entity will ignore "data" to prevent recursion calls
     * - The entity will accept "name", "type", "foo", "baz"
     * - The entity will pass "foo" to {@see MacroEntity::setFoo()}
     *
     * @covers ::data()
     * @return void
     */
    public function testGetDataSubset()
    {
        $obj = $this->obj;

        $obj->setData($this->getSetData());

        $this->assertArraySubsets(
            [
                'type' => 'library',
                'foo'  => 'foo is 20',
            ],
            $obj->data([ 'type', 'data', 'foo', 'baz' ])
        );
    }

    /**
     * Test {@see AbstractEntity::setData()} via {@see \ArrayAccess::offsetSet()}.
     *
     * @covers ::offsetSet()
     * @covers ::offsetGet()
     * @covers ::setData()
     * @covers ::data()
     * @return void
     */
    public function testSetDataViaArrayAccess()
    {
        $obj = $this->obj;

        $obj['data'] = $this->getSetData();

        $this->assertArraySubsets(
            [
                'name' => 'Charcoal',
                'type' => 'library',
                'foo'  => 'foo is 20',
            ],
            $obj['data']
        );
    }



    // Test Internals
    // =========================================================================

    /**
     * Test camelization of entity keys.
     *
     * Assertions:
     * - Keys are interchangeable between "snake_case" and "camelCase"
     * - Keys are converted to "camelCase" for method calls or property assignments
     * - Keys are memorized as "camelCase"
     *
     * @covers ::camelize()
     * @return void
     */
    public function testCamelize()
    {
        $obj = $this->obj;

        $obj->set('foo_bar', 'waldo');
        $this->assertObjectHasAttribute('fooBar', $obj);
        $this->assertEquals('waldo', $obj['fooBar']);
        $this->assertEquals('waldo', $obj['foo___bar']);
        $this->assertArrayContains([ 'fooBar' ], $obj->keys());
    }

    /**
     * Test JSON serialization.
     *
     * Assertions:
     * 1. Serialization from default state
     * 2. Serialization from mutated state
     *
     * @covers ::jsonSerialize()
     * @return void
     */
    public function testJsonSerializable()
    {
        $obj = $this->obj;

        /** 1. Serialization from default state */
        $this->assertJsonStringEqualsJsonString(
            json_encode([]),
            json_encode($obj)
        );

        /** 2. Serialization from mutated state */
        $mutation = [
            'name' => 'Charcoal',
        ];
        $obj->setData($mutation);
        $this->assertJsonStringEqualsJsonString(
            json_encode($mutation),
            json_encode($obj)
        );
    }

    /**
     * Test data serialization.
     *
     * Assertions:
     * 1. Serialization from default state
     * 2. Serialization from mutated state
     *
     * @covers ::serialize()
     * @covers ::unserialize()
     * @return void
     */
    public function testSerializable()
    {
        $obj = $this->obj;

        /** 1. Serialization from default state */
        $that = unserialize(serialize($obj));
        $this->assertInstanceOf(get_class($obj), $that);
        $this->assertEquals($obj, $that);
        $this->assertEmpty($that->data());

        /** 2. Serialization from mutated state */
        $mutation = [
            'name' => 'Charcoal',
        ];
        $obj->setData($mutation);
        $that = unserialize(serialize($obj));
        $this->assertInstanceOf(get_class($obj), $that);
        $this->assertEquals($obj->data(), $that->data());
        $this->assertEquals('Charcoal', $that['name']);
    }
}
