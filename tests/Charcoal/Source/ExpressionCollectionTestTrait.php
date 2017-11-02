<?php

namespace Charcoal\Tests\Source;

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

// From 'charcoal-core'
use Charcoal\Source\ExpressionInterface;
use Charcoal\Source\FilterCollectionInterface;
use Charcoal\Source\OrderCollectionInterface;

/**
 * Shared tests and utilities
 * for {@see FilterCollectionInterface}
 * and {@see OrderCollectionInterface}.
 */
trait ExpressionCollectionTestTrait
{
    /**
     * A throwaway array used for testing initial values.
     *
     * @var array
     */
    protected $dummyItems = [ 'foo', 'bar', 'qux' ];

    /**
     * Create expression for testing.
     *
     * @return object
     */
    abstract protected function createCollector();

    /**
     * Create expression for testing.
     *
     * @param  array $data Optional expression data.
     * @return ExpressionInterface
     */
    abstract protected function createExpression(array $data = null);

    /**
     * Test expression creation from collector.
     *
     * Assertions:
     * 1. Instance of {@see ExpressionInterface}
     * 2. Instance of relevant collection
     */
    abstract public function testCreateExpression();

    /**
     * Test collection retrieval.
     *
     * Assertions:
     * 1. Empty; Default state
     * 2. Populated; Mutated state
     */
    abstract public function testGetExpressions();

    /**
     * Test collection emptiness.
     *
     * Assertions:
     * 1. Empty; Default state
     * 2. Populated; Mutated state
     */
    abstract public function testHasExpressions();

    /**
     * Test the mass assignment of expressions.
     *
     * Assertions:
     * 1. Replaces expressions with a new collection
     * 2. Chainable method
     */
    abstract public function testSetExpressions();

    /**
     * Test the mass addition of expressions.
     *
     * Assertions:
     * 1. Appends an array of items to the internal collection
     * 2. Chainable method
     */
    abstract public function testAddExpressions();

    /**
     * Test the addition of one expression.
     *
     * Assertions:
     * 1. Appends one item to the internal collection
     * 2. Chainable method
     */
    abstract public function testAddExpression();

    /**
     * Assert that the given haystack is as expected.
     *
     * @param  array $expected The expected haystack.
     * @param  array $haystack The actual haystack.
     * @return void
     */
    public function assertArrayEquals(array $expected, array $haystack)
    {
        $this->assertCount(count($expected), $haystack);
        $this->assertEquals($expected, $haystack);
    }

    /**
     * Assert that the given haystack contains the expected.
     *
     * @param  array $expected The expected haystack.
     * @param  array $haystack The actual haystack.
     * @return void
     */
    public function assertArrayContains(array $expected, array $haystack)
    {
        foreach ($expected as $item) {
            $this->assertContains($item, $haystack);
        }
    }

    /**
     * Retrieve access to a non-public method.
     *
     * @param  object $object The object to access.
     * @param  string $name   The method name to access.
     * @return ReflectionMethod
     */
    final public static function getMethod($object, $name)
    {
        $reflect = new ReflectionClass($object);
        $method  = $reflect->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

    /**
     * Invoke the requested method.
     *
     * @param  object $object The object to access.
     * @param  string $name   The name of the method to invoke.
     * @return mixed Returns the method result.
     */
    final public static function callMethod($object, $name)
    {
        return static::getMethod($object, $name)->invoke($object);
    }

    /**
     * Invoke the requested method.
     *
     * @param  object $object  The object to access.
     * @param  string $name    The name of the method to invoke.
     * @param  mixed  ...$args The parameters to be passed to the function.
     * @return mixed Returns the method result.
     */
    final public static function callMethodWith($object, $name, ...$args)
    {
        return static::getMethod($object, $name)->invoke($object, ...$args);
    }

    /**
     * Retrieve access to a non-public property.
     *
     * @param  object $object The object to access.
     * @param  string $name   The name of the property to access.
     * @return ReflectionProperty
     */
    final public static function getProperty($object, $name)
    {
        $reflect  = new ReflectionClass($object);
        $property = $reflect->getProperty($name);
        $property->setAccessible(true);
        return $property;
    }

    /**
     * Populate the given expression with dummy items for testing.
     *
     * @param  object $object   The collector object.
     * @param  string $property The property of $object to set.
     * @param  mixed  $value    A value to assign on a property.
     * @return void
     */
    final public static function setPropertyValue($object, $property, $value)
    {
        static::getProperty($object, $property)->setValue($object, $value);
    }
}
