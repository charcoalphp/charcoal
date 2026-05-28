<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\ExpressionInterface;
use Charcoal\Source\Filter;
use Charcoal\Source\FilterInterface;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\CoreContainerIntegrationTrait;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Source\ExpressionTestFieldTrait;
use Charcoal\Tests\Source\ExpressionTestTrait;

/**
 * Test {@see Filter} and {@see FilterInterface}.
 */
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\Filter::class, '__clone')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\Filter::class, 'count')]
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\Filter::class, 'createFilter')]
class FilterTest extends AbstractTestCase
{
    use CoreContainerIntegrationTrait;
    use ExpressionTestFieldTrait;
    use ExpressionTestTrait;
    use ReflectionsTrait;

    /**
     * Create expression for testing.
     *
     * @return Order
     */
    final protected function createExpression(): \Charcoal\Source\Filter
    {
        return new Filter();
    }

    /**
     * Test new instance.
     *
     * Assertions:
     * 1. Implements {@see FilterInterface}
     */
    public function testFilterConstruct(): void
    {
        $obj = $this->createExpression();

        /** 1. Implementation */
        $this->assertInstanceOf(FilterInterface::class, $obj);
    }

    /**
     * Test deep cloning of expression trees.
     *
     */
    public function testDeepCloning(): void
    {
        $obj = $this->createExpression();
        $obj->addFilters([
            [
                'condition' => 'title LIKE "Hello %"'
            ],
            [
                'property' => 'trashed',
                'operator' => 'IS NULL'
            ],
            [
                'property' => 'author_id',
                'value'    => 1
            ]
        ]);

        $cln = clone $obj;
        $this->assertEquals($cln, $obj);
        $this->assertNotSame($cln, $obj);

        $originals = $obj->filters();
        foreach ($cln->filters() as $i => $dupe) {
            $this->assertNotSame($originals[$i], $dupe);
        }
    }

    /**
     * Provide data for value parsing.
     *
     * @used-by ExpressionTestTrait::testDefaultValues()
     */
    final public static function provideDefaultValues(): array
    {
        return [
            'property'    => [ 'property',     null ],
            'table'       => [ 'table',        null ],
            'value'       => [ 'value',        null ],
            'function'    => [ 'func',         null ],
            'operator'    => [ 'operator',     '=' ],
            'conjunction' => [ 'conjunction',  'AND' ],
            'filters'     => [ 'filters',      [] ],
            'condition'   => [ 'condition',    null ],
            'active'      => [ 'active',       true ],
            'name'        => [ 'name',         null ],
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
    public function testValue(): void
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->value());

        /** 2. Mutated Value */
        $that = $obj->setValue('foobar');
        $this->assertIsString($obj->value());
        $this->assertEquals('foobar', $obj->value());

        /** 3. Chainable */
        $this->assertSame($obj, $that);
    }

    /**
     * Test deprecated "val" property.
     */
    public function testDeprecatedValExpression(): void
    {
        $obj = $this->createExpression();

        @$obj->setData([ 'val' => 'qux' ]);
        $this->assertEquals('qux', $obj->value());
    }

    /**
     * Test "val" property deprecation notice.
     */
    public function testDeprecatedValError(): void
    {
        $this->expectUserDeprecationMessage('Filter expression option "val" is deprecated in favour of "value": qux');
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
    public function testOperator(): void
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertEquals('=', $obj->operator());

        /** 2. Mutated Value */
        $that = $obj->setOperator('LIKE');
        $this->assertIsString($obj->operator());
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
    public function testOperatorWithUnsupportedOperator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createExpression()->setOperator('foo');
    }

    /**
     * Test "operator" property with invalid value.
     */
    public function testOperatorWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
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
    public function testFunc(): void
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->func());

        /** 2. Mutated Value */
        $that = $obj->setFunc('LENGTH');
        $this->assertIsString($obj->func());
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
    public function testFuncWithUnsupportedFunction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createExpression()->setFunc('xyzzy');
    }

    /**
     * Test "func" property with invalid value.
     */
    public function testFuncWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createExpression()->setFunc(33);
    }

    /**
     * Test the "conjunction" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Accepts mixed case
     */
    public function testConjunction(): void
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertEquals('AND', $obj->conjunction());

        /** 2. Mutated Value */
        $that = $obj->setConjunction('||');
        $this->assertIsString($obj->conjunction());
        $this->assertEquals('||', $obj->conjunction());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Accepts mixed case */
        $obj->setConjunction('xor');
        $this->assertEquals('XOR', $obj->conjunction());
    }

    /**
     * Test "conjunction" property with unsupported conjunction.
     */
    public function testConjunctionWithUnsupportedConjunction(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createExpression()->setConjunction('qux');
    }

    /**
     * Test "conjunction" property with invalid value.
     */
    public function testConjunctionWithInvalidValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createExpression()->setConjunction(11);
    }

    /**
     * Test deprecated "operand" property.
     */
    public function testDeprecatedOperandExpression(): void
    {
        $obj = $this->createExpression();

        @$obj->setData([ 'operand' => 'XOR' ]);
        $this->assertEquals('XOR', $obj->conjunction());
    }

    /**
     * Test "operand" property deprecation notice.
     */
    public function testDeprecatedOperandError(): void
    {
        $this->expectUserDeprecationMessage('Query expression option "operand" is deprecated in favour of "conjunction": XOR');
        $this->createExpression()->setData([ 'operand' => 'XOR' ]);
    }

    /**
     * Test implementation of {@see Countable}.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     *
     */
    public function testCount(): void
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertEquals(0, $obj->count());

        /** 2. Mutated Value */
        $obj->addFilter('1 = 1');
        $this->assertEquals(1, $obj->count());
    }

    /**
     * Test the creation of a query filter expression.
     *
     * Assertions:
     * 1. Instance of {@see ExpressionInterface}
     * 2. Instance of {@see Filter}
     *
     * @see    \Charcoal\Tests\Source\AbstractSourceTest::testCreateFilter
     */
    public function testCreateFilter(): void
    {
        $obj = $this->createExpression();

        $result = $this->callMethodWith($obj, 'createFilter', [ 'name' => 'foo' ]);
        $this->assertInstanceOf(Filter::class, $result);
        $this->assertInstanceOf(ExpressionInterface::class, $result);
        $this->assertEquals('foo', $result->name());
    }

    /**
     * Test data structure with mutated state.
     *
     * Assertions:
     * 1. Mutate all options
     * 2. Partially mutated state
     * 3. Mutation via aliases
     */
    public function testData(): void
    {
        /** 1. Mutate all options */
        $exp1 = $this->createExpression();

        $mutation = [
            'value'       => '%foobar',
            'func'        => 'REVERSE',
            'operator'    => 'LIKE',
            'property'    => 'col',
            'table'       => 'tbl',
            'conjunction' => 'OR',
            'filters'     => [ 'foo' => $exp1 ],
            'condition'   => '1 = 1',
            'active'      => false,
            'name'        => 'foo',
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

        $this->assertArrayHasKey('conjunction', $data);
        $this->assertEquals('OR', $data['conjunction']);
        $this->assertEquals('OR', $obj->conjunction());

        $this->assertArrayHasKey('filters', $data);
        $this->assertContains($exp1, $data['filters']);
        $this->assertContains($exp1, $obj->filters());

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
        $this->assertEquals($defs['conjunction'], $obj->conjunction());
        $this->assertEquals($defs['condition'], $obj->condition());

        $data = $obj->data();
        $this->assertNotEquals($defs['operator'], $data['operator']);
        $this->assertEquals('IS NULL', $data['operator']);

        /** 3. Mutation via aliases */
        $exp2 = $this->createExpression();

        $mutation = [
            'function'   => 'REVERSE',
            'conditions' => [ 'baz' => $exp2 ]
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);

        $data = $obj->data();
        $this->assertEquals('REVERSE', $data['func']);
        $this->assertContains($exp2, $data['filters']);
    }

    /**
     * Test deprecated "string" property.
     *
     * @see OrderTest::testDeprecatedStringExpression()
     */
    public function testDeprecatedStringExpression(): void
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
    public function testDeprecatedStringError(): void
    {
        $this->expectUserDeprecationMessage('Filter expression option "string" is deprecated in favour of "condition": 1 = 1');
        $this->createExpression()->setData([ 'string' => '1 = 1' ]);
    }
}
