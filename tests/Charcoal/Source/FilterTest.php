<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\Filter;
use Charcoal\Tests\ContainerIntegrationTrait;
use Charcoal\Tests\Source\FieldExpressionTestTrait;
use Charcoal\Tests\Source\QueryExpressionTestTrait;

/**
 *
 */
class FilterTest extends \PHPUnit_Framework_TestCase
{
    use ContainerIntegrationTrait;
    use FieldExpressionTestTrait;
    use QueryExpressionTestTrait;

    /**
     * Create expression for testing.
     *
     * @return Order
     */
    final protected function createExpression()
    {
        return new Filter();
    }

    /**
     * Provide data for value parsing.
     *
     * @used-by QueryExpressionTestTrait::testDefaultValues()
     * @return  array
     */
    final public function provideDefaultValues()
    {
        return [
            'property'  => [ 'property',   null ],
            'table'     => [ 'table',      null ],
            'value'     => [ 'val',        null ],
            'function'  => [ 'func',       null ],
            'operator'  => [ 'operator',   '=' ],
            'operand'   => [ 'operand',    'AND' ],
            'active'    => [ 'active',     true ],
            'condition' => [ 'condition',  null ],
        ];
    }

    /**
     * Test the "val" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     *
     * Note: {@see Filter::val()} uses {@see \Charcoal\Source\AbstractExpression::parseVal()}.
     * Tests for `parseVal()` are performed in {@see QueryExpressionTestTrait::testParseValue()}
     */
    public function testVal()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->val());

        /** 2. Mutated Value */
        $that = $obj->setVal('foobar');
        $this->assertInternalType('string', $obj->val());
        $this->assertEquals('foobar', $obj->val());

        /** 3. Chainable */
        $this->assertSame($obj, $that);
    }

    /**
     * Test the "operator" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Accepts mixed case
     */
    public function testOperator()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertEquals('=', $obj->operator());

        /** 2. Mutated Value */
        $that = $obj->setOperator('LIKE');
        $this->assertInternalType('string', $obj->operator());
        $this->assertEquals('LIKE', $obj->operator());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Accepts mixed case */
        $obj->setOperator('is null');
        $this->assertEquals('IS NULL', $obj->operator());
    }

    /**
     * Test "operator" property with unsupported operator.
     */
    public function testOperatorWithUnsupportedOperator()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setOperator('foo');
    }

    /**
     * Test "operator" property with invalid value.
     */
    public function testOperatorWithInvalidValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setOperator(42);
    }

    /**
     * Test the "func" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Accepts mixed case
     * 5. Accepts NULL
     */
    public function testFunc()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->func());

        /** 2. Mutated Value */
        $that = $obj->setFunc('LENGTH');
        $this->assertInternalType('string', $obj->func());
        $this->assertEquals('LENGTH', $obj->func());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Accepts mixed case */
        $obj->setFunc('weekDay');
        $this->assertEquals('WEEKDAY', $obj->func());

        /** 5. Accepts NULL */
        $obj->setFunc(null);
        $this->assertNull($obj->func());
    }

    /**
     * Test "func" property with unsupported func.
     */
    public function testFuncWithUnsupportedFunction()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setFunc('xyzzy');
    }

    /**
     * Test "func" property with invalid value.
     */
    public function testFuncWithInvalidValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setFunc(33);
    }

    /**
     * Test the "operand" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Accepts mixed case
     */
    public function testOperand()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertEquals('AND', $obj->operand());

        /** 2. Mutated Value */
        $that = $obj->setOperand('||');
        $this->assertInternalType('string', $obj->operand());
        $this->assertEquals('||', $obj->operand());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Accepts mixed case */
        $obj->setOperand('xor');
        $this->assertEquals('XOR', $obj->operand());
    }

    /**
     * Test "operand" property with unsupported operand.
     */
    public function testOperandWithUnsupportedOperand()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setOperand('qux');
    }

    /**
     * Test "operand" property with invalid value.
     */
    public function testOperandWithInvalidValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setOperand(11);
    }

    /**
     * Test data structure with mutated state.
     *
     * Assertions:
     * 1. Mutate all options
     * 2. Partially mutated state
     */
    public function testData()
    {
        $obj = $this->createExpression();

        /** 1. Mutate all options */
        $mutation = [
            'val'       => '%foobar',
            'func'      => 'REVERSE',
            'operator'  => 'LIKE',
            'operand'   => 'OR',
            'property'  => 'col',
            'table'     => 'tbl',
            'active'    => false,
            'condition' => '1 = 1',
        ];

        $obj->setData($mutation);
        $data = $obj->data();

        $this->assertArrayHasKey('val', $data);
        $this->assertEquals('%foobar', $data['val']);
        $this->assertEquals('%foobar', $obj->val());

        $this->assertArrayHasKey('func', $data);
        $this->assertEquals('REVERSE', $data['func']);
        $this->assertEquals('REVERSE', $obj->func());

        $this->assertArrayHasKey('operator', $data);
        $this->assertEquals('LIKE', $data['operator']);
        $this->assertEquals('LIKE', $obj->operator());

        $this->assertArrayHasKey('operand', $data);
        $this->assertEquals('OR', $data['operand']);
        $this->assertEquals('OR', $obj->operand());

        $this->assertStructHasBasicData($obj, $mutation);
        $this->assertStructHasFieldData($obj, $mutation);

        /** 2. Partially mutated state */
        $mutation = [
            'operator' => 'IS NULL'
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);

        $this->assertNull($obj->val());
        $this->assertNull($obj->func());
        $this->assertEquals('AND', $obj->operand());
        $this->assertTrue($obj->active());
        $this->assertNull($obj->condition());

        $data = $obj->data();
        $this->assertArrayNotHasKey('val', $data);
        $this->assertArrayNotHasKey('func', $data);
        $this->assertArrayNotHasKey('operand', $data);
        $this->assertArrayNotHasKey('active', $data);
        $this->assertArrayNotHasKey('condition', $data);

        $this->assertArrayHasKey('operator', $data);
        $this->assertEquals('IS NULL', $data['operator']);
    }
}
