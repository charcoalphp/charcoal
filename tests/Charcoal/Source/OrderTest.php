<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\Order;
use Charcoal\Tests\ContainerIntegrationTrait;
use Charcoal\Tests\Source\FieldExpressionTestTrait;
use Charcoal\Tests\Source\QueryExpressionTestTrait;

/**
 *
 */
class OrderTest extends \PHPUnit_Framework_TestCase
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
        return new Order();
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
            'mode'      => [ 'mode',       null ],
            'values'    => [ 'values',     null ],
            'active'    => [ 'active',     true ],
            'condition' => [ 'condition',  null ],
        ];
    }

    /**
     * Test the "mode" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Accepts mixed case
     * 5. Accepts NULL
     */
    public function testMode()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->mode());

        /** 2. Mutated Value */
        $that = $obj->setMode('asc');
        $this->assertInternalType('string', $obj->mode());
        $this->assertEquals('asc', $obj->mode());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Accepts mixed case */
        $obj->setMode('DESC');
        $this->assertEquals('desc', $obj->mode());

        /** 5. Accepts NULL */
        $obj->setMode(null);
        $this->assertNull($obj->mode());
    }

    /**
     * Test "mode" property with unsupported mode.
     */
    public function testModeWithUnsupportedMode()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setMode('foobar');
    }

    /**
     * Test "mode" property with invalid value.
     */
    public function testModeWithInvalidValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setMode([]);
    }

    /**
     * Test the "values" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Accepts NULL
     */
    public function testValues()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->values());

        /** 2. Mutated Value */
        $mutated = [ 'foo', 'baz', 'qux' ];

        $that = $obj->setValues([ 'foo', 'baz', 'qux' ]);
        $this->assertInternalType('array', $obj->values());
        $this->assertEquals($mutated, $obj->values());

        $obj->setValues('foo,baz,qux');
        $this->assertEquals($mutated, $obj->values());

        $obj->setValues('foo ,  baz, qux   ');
        $this->assertEquals($mutated, $obj->values());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Accepts NULL */
        $obj->setValues(null);
        $this->assertNull($obj->values());
    }

    /**
     * Test "mode" property with blank string.
     */
    public function testValuesWithBlankValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setValues('');
    }

    /**
     * Test "mode" property with blank string.
     */
    public function testValuesWithEmptyArray()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setValues([]);
    }

    /**
     * Test "mode" property with invalid value.
     */
    public function testValuesWithInvalidValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setValues(42);
    }

    /**
     * Test data structure with mutated state.
     *
     * Assertions:
     * 1. Mutate all options
     * 2. Partially mutated state
     * 3. Auto-set mode from "condition"
     */
    public function testData()
    {
        $obj = $this->createExpression();

        /** 1. Mutate all options */
        $values   = [ 'foo', 'baz', 'qux' ];
        $mutation = [
            'mode'      => 'rand',
            'values'    => $values,
            'property'  => 'col',
            'table'     => 'tbl',
            'active'    => false,
            'condition' => '1 = 1',
        ];

        $obj->setData($mutation);
        $data = $obj->data();

        $this->assertArrayHasKey('mode', $data);
        $this->assertEquals('rand', $data['mode']);
        $this->assertEquals('rand', $obj->mode());

        $this->assertArrayHasKey('values', $data);
        $this->assertEquals($values, $data['values']);
        $this->assertEquals($values, $obj->values());

        $this->assertStructHasBasicData($obj, $mutation);
        $this->assertStructHasFieldData($obj, $mutation);

        /** 2. Partially mutated state */
        $mutation = [
            'mode' => 'desc'
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);

        $this->assertNull($obj->values());
        $this->assertTrue($obj->active());
        $this->assertNull($obj->condition());

        $data = $obj->data();
        $this->assertArrayNotHasKey('values', $data);
        $this->assertArrayNotHasKey('active', $data);
        $this->assertArrayNotHasKey('condition', $data);

        $this->assertArrayHasKey('mode', $data);
        $this->assertEquals('desc', $data['mode']);

        /** 3. Auto-set mode from "condition" */
        $mutation = [
            'condition' => '2 = 2'
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);

        $this->assertEquals('2 = 2', $obj->condition());

        $data = $obj->data();
        $this->assertArrayHasKey('condition', $data);
        $this->assertEquals('2 = 2', $data['condition']);

        $this->assertArrayHasKey('mode', $data);
        $this->assertEquals('custom', $data['mode']);
    }
}
