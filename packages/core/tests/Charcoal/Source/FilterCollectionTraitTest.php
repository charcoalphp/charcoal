<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\ExpressionInterface;
use Charcoal\Source\Filter;
use Charcoal\Source\FilterInterface;
use Charcoal\Source\FilterCollectionTrait;
use Charcoal\Source\FilterCollectionInterface;

use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\AssertionsTrait;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Mock\FilterCollectionClass;
use Charcoal\Tests\Source\ExpressionCollectionTestTrait;

/**
 * Test {@see FilterCollectionTrait} and {@see FilterCollectionInterface}.
 */
class FilterCollectionTraitTest extends AbstractTestCase
{
    use AssertionsTrait;
    use ExpressionCollectionTestTrait;
    use ReflectionsTrait;

    /**
     * Create mock object for testing.
     *
     * @return FilterCollectionClass
     */
    final public function createCollector()
    {
        return new FilterCollectionClass();
    }

    /**
     * Create expression for testing.
     *
     * @param  array $data Optional expression data.
     * @return Filter
     */
    final protected function createExpression(array $data = null)
    {
        $expr = new Filter();
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
     * 2. Instance of {@see FilterInterface}
     *
     * @covers \Charcoal\Source\FilterCollectionTrait::createFilter
     *
     * @return void
     */
    public function testCreateExpression()
    {
        $obj = $this->createCollector();

        $result = $this->callMethod($obj, 'createFilter');
        $this->assertInstanceOf(FilterInterface::class, $result);
        $this->assertInstanceOf(ExpressionInterface::class, $result);
    }

    /**
     * Test collection retrieval.
     *
     * Assertions:
     * 1. Empty; Default state
     * 2. Populated; Mutated state
     *
     * @covers \Charcoal\Source\FilterCollectionTrait::filters
     *
     * @return void
     */
    public function testGetExpressions()
    {
        $obj = $this->createCollector();

        /** 1. Default state */
        $ret = $obj->filters();
        $this->assertIsArray($ret);
        $this->assertEmpty($ret);

        /** 2. Mutated state */
        $this->setPropertyValue($obj, 'filters', $this->dummyItems);
        $this->assertArrayEquals($this->dummyItems, $obj->filters());
    }

    /**
     * Test collection emptiness.
     *
     * Assertions:
     * 1. Empty; Default state
     * 2. Populated; Mutated state
     *
     * @covers \Charcoal\Source\FilterCollectionTrait::hasFilters
     *
     * @return void
     */
    public function testHasExpressions()
    {
        $obj = $this->createCollector();

        /** 1. Default state */
        $this->assertFalse($obj->hasFilters());

        /** 2. Mutated state */
        $this->setPropertyValue($obj, 'filters', $this->dummyItems);
        $this->assertTrue($obj->hasFilters());
    }

    /**
     * Test the mass assignment of expressions.
     *
     * Assertions:
     * 1. Replaces expressions with a new collection
     * 2. Chainable method
     *
     * @covers \Charcoal\Source\FilterCollectionTrait::setFilters
     *
     * @return void
     */
    public function testSetExpressions()
    {
        $obj  = $this->createCollector();
        $exp1 = $this->createExpression();
        $exp2 = $this->createExpression();

        /** 1. Replaces expressions with a new collection */
        $this->setPropertyValue($obj, 'filters', $this->dummyItems);
        $this->assertArrayEquals($this->dummyItems, $obj->filters());

        $that = $obj->setFilters([ $exp1, $exp2 ]);
        $ret  = $obj->filters();
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
     * @covers \Charcoal\Source\FilterCollectionTrait::addFilters
     *
     * @return void
     */
    public function testAddExpressions()
    {
        $obj  = $this->createCollector();
        $exp1 = $this->createExpression();
        $exp2 = $this->createExpression();

        /** 1. Appends items to the internal collection */
        $this->setPropertyValue($obj, 'filters', $this->dummyItems);
        $this->assertArrayEquals($this->dummyItems, $obj->filters());

        $that = $obj->addFilters([ $exp1, $exp2 ]);
        $ret  = $obj->filters();
        $this->assertCount(5, $ret);
        $this->assertContains($exp1, $ret);
        $this->assertContains($exp2, $ret);

        /** 2. Chainable */
        $this->assertSame($obj, $that);
    }

    /**
     * Test the mass addition of expressions with names.
     *
     * @covers \Charcoal\Source\FilterCollectionTrait::addFilters
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

        $obj->addFilters($map);
        $ret = $obj->filters();

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
     * @covers \Charcoal\Source\FilterCollectionTrait::addFilter
     *
     * @return void
     */
    public function testAddExpression()
    {
        $obj  = $this->createCollector();
        $expr = $this->createExpression();

        /** 1. Appends one item to the internal collection */
        $this->setPropertyValue($obj, 'filters', $this->dummyItems);
        $this->assertArrayEquals($this->dummyItems, $obj->filters());

        $that = $obj->addFilter($expr);
        $ret  = $obj->filters();
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
     * 4. If an instance of {@see FilterInterface} is provided,
     *    the Expression object is used as is.
     *
     * @covers \Charcoal\Source\FilterCollectionTrait::processFilter
     *
     * @return void
     */
    public function testProcessExpression()
    {
        $obj = $this->createCollector();

        /** 1. Condition */
        $condition  = '`foo` ASC';
        $result = $this->callMethodWith($obj, 'processFilter', $condition);
        $this->assertInstanceOf(FilterInterface::class, $result);
        $this->assertEquals($condition, $result->condition());

        /** 2. Structure */
        $struct = [
            'name'     => 'foo',
            'property' => 'qux',
        ];
        $result = $this->callMethodWith($obj, 'processFilter', $struct);
        $this->assertInstanceOf(FilterInterface::class, $result);
        $this->assertArrayContains($struct, $result->data());

        /** 3. Closure */
        $lambda = function (FilterInterface $expr, FilterCollectionInterface $tested) use ($struct) {
            return $expr->setData($struct);
        };
        $result = $this->callMethodWith($obj, 'processFilter', $lambda);
        $this->assertInstanceOf(FilterInterface::class, $result);
        $this->assertArrayContains($struct, $result->data());

        /** 4. Expression */
        $expr   = $this->createExpression();
        $result = $this->callMethodWith($obj, 'processFilter', $expr);
        $this->assertSame($expr, $result);
    }

    /**
     * Test the failure when parsing an invalid expression.
     *
     * @covers \Charcoal\Source\FilterCollectionTrait::processFilter
     *
     * @return void
     */
    public function testProcessExpressionWithInvalidValue()
    {
        $obj = $this->createCollector();

        $this->expectException(InvalidArgumentException::class);
        $this->callMethodWith($obj, 'processFilter', null);
    }

    /**
     * Test traversal of internal collection.
     *
     * Assertions:
     * 1. Applies callback to internal collection
     * 2. Chainable method
     *
     * @covers \Charcoal\Source\FilterCollectionTrait::traverseFilters
     *
     * @return void
     */
    public function testTraverseExpressions()
    {
        $obj  = $this->createCollector();
        $exp1 = $this->createExpression();
        $exp2 = $this->createExpression();
        $exp3 = $this->createExpression();
        $exp4 = $this->createExpression();

        $exp2->addFilter($exp3);
        $exp1->addFilter($exp2);

        /** 1. Traverse internal collection */
        $obj->addFilters([ $exp1, $exp4 ]);
        $that = $obj->traverseFilters(function (FilterInterface $exp) {
            $exp->setProperty('foo');
        });

        foreach ($obj->filters() as $filter) {
            $this->assertEquals('foo', $filter->property());
        }

        /** 2. Chainable */
        $this->assertSame($obj, $that);
    }
}
