<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\Expression;
use Charcoal\Source\ExpressionInterface;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\CoreContainerIntegrationTrait;
use Charcoal\Tests\Source\ExpressionTestTrait;

/**
 * Test {@see Expression} and {@see ExpressionInterface}.
 */
class ExpressionTest extends AbstractTestCase
{
    use CoreContainerIntegrationTrait;
    use ExpressionTestTrait;

    /**
     * Create expression for testing.
     *
     * @return Expression
     */
    final protected function createExpression()
    {
        return new Expression();
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
            'condition' => [ 'condition', null ],
            'active'    => [ 'active',    true ],
            'name'      => [ 'name',      null ],
        ];
    }

    /**
     * Test the "condition" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Trimmed value
     * 5. Accepts NULL
     * 6. Swaps blank string for NULL
     *
     * @return void
     */
    public function testConditionExpression()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->condition());

        /** 2. Mutated Value */
        $that = $obj->setCondition('1 = 1');
        $this->assertIsString($obj->condition());
        $this->assertEquals('1 = 1', $obj->condition());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Trimmed value */
        $obj->setCondition('   1 = 1  ');
        $this->assertEquals('1 = 1', $obj->condition());

        /** 5. Accepts NULL */
        $obj->setCondition(null);
        $this->assertNull($obj->condition());

        /** 6. Swaps blank string for NULL */
        $obj->setCondition('  ');
        $this->assertNull($obj->condition());
    }

    /**
     * Test the conditional check of "condition".
     *
     * @return void
     */
    public function testHasConditionExpression()
    {
        $obj = $this->createExpression();

        $this->assertFalse($obj->hasCondition());

        $obj->setCondition('  ');
        $this->assertFalse($obj->hasCondition());

        $that = $obj->setCondition('1 = 1');
        $this->assertTrue($obj->hasCondition());
    }

    /**
     * Test "condition" property with invalid value.
     *
     * @return void
     */
    public function testConditionExpressionWithInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createExpression()->setCondition([]);
    }

    /**
     * Test data structure with mutated state.
     *
     * Assertions:
     * 1. Mutate all options
     * 2. Partially mutated state
     *
     * @return void
     */
    public function testData()
    {
        /** 1. Mutate all options */
        $mutation = [
            'condition' => '1 = 1',
            'active'    => false,
            'name'      => 'foo',
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);
        $this->assertStructHasBasicData($obj, $mutation);

        $data = $obj->data();
        $this->assertArrayHasKey('condition', $data);
        $this->assertEquals('1 = 1', $data['condition']);
        $this->assertEquals($mutation['condition'], $obj->condition());

        /** 2. Partially mutated state */
        $mutation = [
            'condition' => '2 = 2'
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);

        $defs = $obj->defaultData();
        $this->assertStructHasBasicData($obj, $defs);

        $data = $obj->data();
        $this->assertNotEquals($defs['condition'], $data['condition']);
        $this->assertEquals('2 = 2', $data['condition']);
    }
}
