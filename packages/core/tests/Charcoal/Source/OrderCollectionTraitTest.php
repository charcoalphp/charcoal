<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\ExpressionInterface;
use Charcoal\Source\Order;
use Charcoal\Source\OrderInterface;
use Charcoal\Source\OrderCollectionTrait;
use Charcoal\Source\OrderCollectionInterface;

use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\AssertionsTrait;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Mock\OrderCollectionClass;
use Charcoal\Tests\Mock\OrderTree;
use Charcoal\Tests\Source\ExpressionCollectionTestTrait;

/**
 * Test {@see OrderCollectionTrait} and {@see OrderCollectionInterface}.
 */
class OrderCollectionTraitTest extends AbstractTestCase
{
    use AssertionsTrait;
    use ExpressionCollectionTestTrait;
    use ReflectionsTrait;

    /**
     * Create mock object for testing.
     *
     * @return OrderCollectionClass
     */
    final public function createCollector()
    {
        return new OrderCollectionClass();
    }

    /**
     * Create expression for testing.
     *
     * @param  array $data Optional expression data.
     * @return Order
     */
    final protected function createExpression(array $data = null)
    {
        $expr = new Order();
        if ($data !== null) {
            $expr->setData($data);
        }
        return $expr;
    }

    /**
     * Test expression creation from collector.
     *
     * Assertions:
     * 1. Instance of {@see ExpressionInterface}
     * 2. Instance of {@see OrderInterface}
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::createOrder
     *
     * @return void
     */
    public function testCreateExpression()
    {
        $obj = $this->createCollector();

        $result = $this->callMethod($obj, 'createOrder');
        $this->assertInstanceOf(OrderInterface::class, $result);
        $this->assertInstanceOf(ExpressionInterface::class, $result);
    }

    /**
     * Test collection retrieval.
     *
     * Assertions:
     * 1. Empty; Default state
     * 2. Populated; Mutated state
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::orders
     *
     * @return void
     */
    public function testGetExpressions()
    {
        $obj = $this->createCollector();

        /** 1. Default state */
        $ret = $obj->orders();
        $this->assertIsArray($ret);
        $this->assertEmpty($ret);

        /** 2. Mutated state */
        $this->setPropertyValue($obj, 'orders', $this->dummyItems);
        $this->assertArrayEquals($this->dummyItems, $obj->orders());
    }

    /**
     * Test collection emptiness.
     *
     * Assertions:
     * 1. Empty; Default state
     * 2. Populated; Mutated state
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::hasOrders
     *
     * @return void
     */
    public function testHasExpressions()
    {
        $obj = $this->createCollector();

        /** 1. Default state */
        $this->assertFalse($obj->hasOrders());

        /** 2. Mutated state */
        $this->setPropertyValue($obj, 'orders', $this->dummyItems);
        $this->assertTrue($obj->hasOrders());
    }

    /**
     * Test the mass assignment of expressions.
     *
     * Assertions:
     * 1. Replaces expressions with a new collection
     * 2. Chainable method
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::setOrders
     *
     * @return void
     */
    public function testSetExpressions()
    {
        $obj  = $this->createCollector();
        $exp1 = $this->createExpression();
        $exp2 = $this->createExpression();

        /** 1. Replaces expressions with a new collection */
        $this->setPropertyValue($obj, 'orders', $this->dummyItems);
        $this->assertArrayEquals($this->dummyItems, $obj->orders());

        $that = $obj->setOrders([ $exp1, $exp2 ]);
        $ret  = $obj->orders();
        $this->assertCount(2, $ret);
        $this->assertContains($exp1, $ret);
        $this->assertContains($exp2, $ret);

        /** 2. Chainable */
        $this->assertSame($obj, $that);
    }

    /**
     * Test the mass addition of expressions.
     *
     * Assertions:
     * 1. Appends an array of items to the internal collection
     * 2. Chainable method
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::addOrders
     *
     * @return void
     */
    public function testAddExpressions()
    {
        $obj  = $this->createCollector();
        $exp1 = $this->createExpression();
        $exp2 = $this->createExpression();

        /** 1. Appends items to the internal collection */
        $this->setPropertyValue($obj, 'orders', $this->dummyItems);
        $this->assertArrayEquals($this->dummyItems, $obj->orders());

        $that = $obj->addOrders([ $exp1, $exp2 ]);
        $ret  = $obj->orders();
        $this->assertCount(5, $ret);
        $this->assertContains($exp1, $ret);
        $this->assertContains($exp2, $ret);

        /** 2. Chainable */
        $this->assertSame($obj, $that);
    }

    /**
     * Test the mass addition of expressions with names.
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::addOrders
     *
     * @return void
     */
    public function testAddExpressionsMap()
    {
        $obj = $this->createCollector();
        $map = [
            'foo' => $this->createExpression(),
            'bar' => $this->createExpression(),
            'qux' => $this->createExpression(),
        ];

        $obj->addOrders($map);
        $ret = $obj->orders();

        $this->assertCount(count($map), $ret);
        $this->assertNotEquals($map, $ret);
        $this->assertArrayContains($map, $ret);

        foreach ($ret as $exp) {
            $this->assertArrayHasKey($exp->name(), $map);
        }
    }

    /**
     * Test the addition of one expression.
     *
     * Assertions:
     * 1. Appends one item to the internal collection
     * 2. Chainable method
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::addOrder
     *
     * @return void
     */
    public function testAddExpression()
    {
        $obj  = $this->createCollector();
        $expr = $this->createExpression();

        /** 1. Appends one item to the internal collection */
        $this->setPropertyValue($obj, 'orders', $this->dummyItems);
        $this->assertArrayEquals($this->dummyItems, $obj->orders());

        $that = $obj->addOrder($expr);
        $ret  = $obj->orders();
        $this->assertCount(4, $ret);
        $this->assertContains($expr, $ret);

        /** 2. Chainable */
        $this->assertSame($obj, $that);
    }

    /**
     * Test the parsing of an expression.
     *
     * Assertions:
     * 1. If a string is provided,
     *    an Expression object with a condition is returned
     * 2. If an array is provided,
     *    an Expression object with given data is returned
     * 3. If a closure is provided,
     *    an Expression object is created with the collector's context.
     * 4. If an instance of {@see OrderInterface} is provided,
     *    the Expression object is used as is.
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::processOrder
     *
     * @return void
     */
    public function testProcessExpression()
    {
        $obj = $this->createCollector();

        /** 1. Condition */
        $condition = '`foo` ASC';
        $result    = $this->callMethodWith($obj, 'processOrder', $condition);
        $this->assertInstanceOf(OrderInterface::class, $result);
        $this->assertEquals($condition, $result->condition());

        /** 2. Structure */
        $struct = [
            'name'     => 'foo',
            'property' => 'qux',
        ];
        $result = $this->callMethodWith($obj, 'processOrder', $struct);
        $this->assertInstanceOf(OrderInterface::class, $result);
        $this->assertArrayContains($struct, $result->data());

        /** 3. Closure */
        $lambda = function (OrderInterface $expr, OrderCollectionInterface $tested) use ($struct) {
            return $expr->setData($struct);
        };
        $result = $this->callMethodWith($obj, 'processOrder', $lambda);
        $this->assertInstanceOf(OrderInterface::class, $result);
        $this->assertArrayContains($struct, $result->data());

        /** 4. Expression */
        $expr   = $this->createExpression();
        $result = $this->callMethodWith($obj, 'processOrder', $expr);
        $this->assertSame($expr, $result);
    }

    /**
     * Test the failure when parsing an invalid expression.
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::processOrder
     *
     * @return void
     */
    public function testProcessExpressionWithInvalidValue()
    {
        $obj = $this->createCollector();

        $this->expectException(InvalidArgumentException::class);
        $this->callMethodWith($obj, 'processOrder', null);
    }

    /**
     * Test traversal of internal collection.
     *
     * Assertions:
     * 1. Replaces expressions with a new collection
     * 2. Chainable method
     *
     * @covers \Charcoal\Source\OrderCollectionTrait::traverseOrders
     *
     * @return void
     */
    public function testTraverseExpressions()
    {
        $obj  = $this->createCollector();
        $exp1 = new OrderTree();
        $exp2 = new OrderTree();
        $exp3 = new OrderTree();

        $exp2->addOrder($exp3);
        $exp1->addOrder($exp2);

        /** 1. Traverse internal collection */
        $obj->addOrders([ $exp1 ]);

        $i = 0;
        $that = $obj->traverseOrders(function (OrderInterface $exp) use (&$i) {
            $i++;
            $exp->setProperty('foo');
        });

        $this->assertEquals(3, $i);

        foreach ([ $exp1, $exp2, $exp3 ] as $order) {
            $this->assertEquals('foo', $order->property());
        }

        /** 2. Chainable */
        $this->assertSame($obj, $that);
    }
}
