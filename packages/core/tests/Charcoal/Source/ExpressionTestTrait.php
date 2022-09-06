<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\AbstractExpression;
use Charcoal\Source\ExpressionInterface;

/**
 * Shared tests for implementations of {@see AbstractExpression}
 * and {@see ExpressionInterface}.
 */
trait ExpressionTestTrait
{
    /**
     * @return \Pimple\Container
     */
    abstract protected function getContainer();

    /**
     * Create expression for testing.
     *
     * @return ExpressionInterface
     */
    abstract protected function createExpression();

    /**
     * Provide data for value parsing.
     *
     * @example [ [ 'active', true ] ]
     * @used-by self::testDefaultValues()
     * @return  array
     */
    abstract public function provideDefaultValues();

    /**
     * Test new instance.
     *
     * Assertions:
     * 1. Implements {@see ExpressionInterface}
     *
     * @return void
     */
    final public function testConstruct()
    {
        $obj = $this->createExpression();

        /** 1. Implementation */
        $this->assertInstanceOf(ExpressionInterface::class, $obj);
    }

    /**
     * Test method signature for default data values.
     *
     * Assertions:
     * 1. Getter returns an array
     *
     * @return void
     */
    final public function testDefaultValuesMethod()
    {
        $obj = $this->createExpression();

        /** 1. Getter returns an array */
        $this->assertIsArray($obj->defaultData());
    }

    /**
     * Test default data values.
     *
     * @dataProvider provideDefaultValues
     *
     * @param mixed $key      The data key test.
     * @param mixed $expected The expected data value.
     * @return void
     */
    final public function testDefaultValues($key, $expected)
    {
        $obj  = $this->createExpression();
        $data = $obj->defaultData();

        $this->assertArrayHasKey($key, $data);
        $this->assertEquals($expected, $data[$key]);
    }

    /**
     * Test method signature for data stucture.
     *
     * Assertions:
     * 1. Getter returns an array
     * 2. Setter is chainable
     *
     * @return void
     */
    final public function testDataMethod()
    {
        $obj = $this->createExpression();

        /** 1. Getter returns an array */
        $this->assertIsArray($obj->data());

        /** 2. Setter is chainable */
        $that = $obj->setData([]);
        $this->assertSame($obj, $that);
    }

    /**
     * Test data structure with default state.
     *
     * @return void
     */
    final public function testDefaultData()
    {
        $obj = $this->createExpression();
        $this->assertEquals($obj->defaultData(), $obj->data());
    }

    /**
     * Assert the given expression has data from {@see AbstractExpression}.
     *
     * @param ExpressionInterface $obj      The expression to test.
     * @param array|null          $expected The expected data subset.
     * @return void
     */
    final public function assertStructHasBasicData(ExpressionInterface $obj, array $expected = null)
    {
        if (empty($expected)) {
            $expected = [
                'active' => false,
                'name'   => 'foo',
            ];
            $obj->setData($mutation);
        }

        $data = $obj->data();

        $this->assertArrayHasKey('active', $data);
        $this->assertEquals($expected['active'], $data['active']);
        $this->assertEquals($expected['active'], $obj->active());

        $this->assertArrayHasKey('name', $data);
        $this->assertEquals($expected['name'], $data['name']);
        $this->assertEquals($expected['name'], $obj->name());
    }

    /**
     * Test JSON serialization.
     *
     * Assertions:
     * 1. Serialization from default state
     * 2. Serialization from mutated state
     *
     * @return void
     */
    public function testJsonSerializable()
    {
        $obj = $this->createExpression();

        /** 1. Serialization from default state */
        $this->assertJsonStringEqualsJsonString(
            json_encode([]),
            json_encode($obj)
        );

        /** 2. Serialization from mutated state */
        $mutation = [
            'name' => 'foo',
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
     * @return void
     */
    public function testSerializable()
    {
        $obj = $this->createExpression();

        /** 1. Serialization from default state */
        $that = unserialize(serialize($obj));
        $this->assertInstanceOf(get_class($obj), $that);
        $this->assertEquals($obj, $that);
        $this->assertTrue($that->active());
        $this->assertNull($that->name());

        /** 2. Serialization from mutated state */
        $mutation = [
            'active' => false,
            'name'   => 'foo',
        ];
        $obj->setData($mutation);
        $that = unserialize(serialize($obj));
        $this->assertInstanceOf(get_class($obj), $that);
        $this->assertEquals($obj, $that);
        $this->assertFalse($that->active());
        $this->assertEquals('foo', $that->name());
    }
}
