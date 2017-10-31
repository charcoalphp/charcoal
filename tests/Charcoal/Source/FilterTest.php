<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From PHPUnit
use PHPUnit_Framework_Error;

// From 'charcoal-core'
use Charcoal\Source\Filter;
use Charcoal\Source\FilterInterface;
use Charcoal\Tests\ContainerIntegrationTrait;
use Charcoal\Tests\Source\ExpressionTestFieldTrait;
use Charcoal\Tests\Source\ExpressionTestTrait;

/**
 * Test {@see Filter} and {@see FilterInterface}.
 */
class FilterTest extends \PHPUnit_Framework_TestCase
{
    use ContainerIntegrationTrait;
    use ExpressionTestFieldTrait;
    use ExpressionTestTrait;

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
     * Test new instance.
     *
     * Assertions:
     * 1. Implements {@see FilterInterface}
     */
    public function testFilterConstruct()
    {
        $obj = $this->createExpression();

        /** 1. Implementation */
        $this->assertInstanceOf(FilterInterface::class, $obj);
    }

    /**
     * Provide data for value parsing.
     *
     * @used-by ExpressionTestTrait::testDefaultValues()
     * @return  array
     */
    final public function provideDefaultValues()
    {
        return [
            'property'  => [ 'property',   null ],
            'table'     => [ 'table',      null ],
            'value'     => [ 'value',      null ],
            'function'  => [ 'func',       null ],
            'operator'  => [ 'operator',   '=' ],
            'operand'   => [ 'operand',    'AND' ],
            'condition' => [ 'condition',  null ],
            'active'    => [ 'active',     true ],
            'name'      => [ 'name',       null ],
        ];
    }

    /**
     * Test the "value" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     *
     * Note: {@see Filter::value()} uses {@see \Charcoal\Source\AbstractExpression::parseValue()}.
     * Tests for `parseValue()` are performed in {@see ExpressionTestTrait::testParseValue()}.
     */
    public function testValue()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->value());

        /** 2. Mutated Value */
        $that = $obj->setValue('foobar');
        $this->assertInternalType('string', $obj->value());
        $this->assertEquals('foobar', $obj->value());

        /** 3. Chainable */
        $this->assertSame($obj, $that);
    }

    /**
     * Test deprecated "val" property.
     */
    public function testDeprecatedValExpression()
    {
        $obj = $this->createExpression();

        @$obj->setData([ 'val' => 'qux' ]);
        $this->assertEquals('qux', $obj->value());
    }

    /**
     * Test "val" property deprecation notice.
     */
    public function testDeprecatedValError()
    {
        $this->setExpectedException(PHPUnit_Framework_Error::class);
        $this->createExpression()->setData([ 'val' => 'qux' ]);

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
     * 3. Mutation via aliases
     */
    public function testData()
    {
        /** 1. Mutate all options */
        $mutation = [
            'value'     => '%foobar',
            'func'      => 'REVERSE',
            'operator'  => 'LIKE',
            'operand'   => 'OR',
            'property'  => 'col',
            'table'     => 'tbl',
            'condition' => '1 = 1',
            'active'    => false,
            'name'      => 'foo',
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);
        $this->assertStructHasBasicData($obj, $mutation);
        $this->assertStructHasFieldData($obj, $mutation);

        $data = $obj->data();

        $this->assertArrayHasKey('value', $data);
        $this->assertEquals('%foobar', $data['value']);
        $this->assertEquals('%foobar', $obj->value());

        $this->assertArrayHasKey('func', $data);
        $this->assertEquals('REVERSE', $data['func']);
        $this->assertEquals('REVERSE', $obj->func());

        $this->assertArrayHasKey('operator', $data);
        $this->assertEquals('LIKE', $data['operator']);
        $this->assertEquals('LIKE', $obj->operator());

        $this->assertArrayHasKey('operand', $data);
        $this->assertEquals('OR', $data['operand']);
        $this->assertEquals('OR', $obj->operand());

        /** 2. Partially mutated state */
        $mutation = [
            'operator' => 'IS NULL'
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);

        $defs = $obj->defaultData();
        $this->assertStructHasBasicData($obj, $defs);

        $this->assertEquals($defs['value'], $obj->value());
        $this->assertEquals($defs['func'], $obj->func());
        $this->assertEquals($defs['operand'], $obj->operand());
        $this->assertEquals($defs['condition'], $obj->condition());

        $data = $obj->data();
        $this->assertNotEquals($defs['operator'], $data['operator']);
        $this->assertEquals('IS NULL', $data['operator']);

        /** 3. Mutation via aliases */
        $mutation = [
            'function' => 'REVERSE'
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);

        $data = $obj->data();
        $this->assertEquals('REVERSE', $data['func']);
    }

    /**
     * Test deprecated "string" property.
     *
     * @see OrderTest::testDeprecatedStringExpression()
     */
    public function testDeprecatedStringExpression()
    {
        $obj = $this->createExpression();

        @$obj->setData([ 'string' => '1 = 1' ]);
        $this->assertEquals('1 = 1', $obj->condition());
    }

    /**
     * Test "string" property deprecation notice.
     *
     * @see OrderTest::testDeprecatedStringError()
     */
    public function testDeprecatedStringError()
    {
        $this->setExpectedException(PHPUnit_Framework_Error::class);
        $this->createExpression()->setData([ 'string' => '1 = 1' ]);

    }
}
