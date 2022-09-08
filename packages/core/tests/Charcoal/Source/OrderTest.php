<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\Order;
use Charcoal\Source\OrderInterface;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\CoreContainerIntegrationTrait;
use Charcoal\Tests\Source\ExpressionTestFieldTrait;
use Charcoal\Tests\Source\ExpressionTestTrait;

/**
 * Test {@see Order} and {@see OrderInterface}.
 */
class OrderTest extends AbstractTestCase
{
    use CoreContainerIntegrationTrait;
    use ExpressionTestFieldTrait;
    use ExpressionTestTrait;

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
     * Test new instance.
     *
     * Assertions:
     * 1. Implements {@see OrderInterface}
     *
     * @return void
     */
    public function testOrderConstruct()
    {
        $obj = $this->createExpression();

        /** 1. Implementation */
        $this->assertInstanceOf(OrderInterface::class, $obj);
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
            'direction' => [ 'direction',  null ],
            'mode'      => [ 'mode',       null ],
            'values'    => [ 'values',     null ],
            'condition' => [ 'condition',  null ],
            'active'    => [ 'active',     true ],
            'name'      => [ 'name',       null ],
        ];
    }

    /**
     * Test the "direction" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Accepts NULL
     * 5. Unsupported direction sets DESC
     *
     * @return void
     */
    public function testDirection()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->direction());

        /** 2. Mutated Value */
        $that = $obj->setDirection('asc');
        $this->assertEquals('ASC', $obj->direction());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Accepts NULL */
        $obj->setDirection(null);
        $this->assertNull($obj->direction());

        /** 5. Unsupported Direction */
        $that = $obj->setDirection('foo');
        $this->assertEquals('DESC', $obj->direction());
    }

    /**
     * Test "direction" property with invalid value.
     *
     * @return void
     */
    public function testDirectionWithInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createExpression()->setDirection(0);
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
     *
     * @return void
     */
    public function testMode()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->mode());

        /** 2. Mutated Value */
        $that = $obj->setMode('asc');
        $this->assertIsString($obj->mode());
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
     * Test "direction" property when selecting a direction "mode".
     *
     * @return void
     */
    public function testDirectionMode()
    {
        $obj = $this->createExpression();

        $this->assertNull($obj->direction());

        $obj->setMode('asc');
        $this->assertEquals('ASC', $obj->direction());

        $obj->setMode('desc');
        $this->assertEquals('DESC', $obj->direction());

        $obj->setMode('values');
        $this->assertEquals('DESC', $obj->direction());
    }

    /**
     * Test "mode" property with unsupported mode.
     *
     * @return void
     */
    public function testModeWithUnsupportedMode()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createExpression()->setMode('foobar');
    }

    /**
     * Test "mode" property with invalid value.
     *
     * @return void
     */
    public function testModeWithInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);
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
     *
     * @return void
     */
    public function testValues()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->values());

        /** 2. Mutated Value */
        $mutated = [ 'foo', 'baz', 'qux' ];

        $that = $obj->setValues([ 'foo', 'baz', 'qux' ]);
        $this->assertIsArray($obj->values());
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
     *
     * @return void
     */
    public function testValuesWithBlankValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createExpression()->setValues('');
    }

    /**
     * Test "mode" property with blank string.
     *
     * @return void
     */
    public function testValuesWithEmptyArray()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createExpression()->setValues([]);
    }

    /**
     * Test "mode" property with invalid value.
     *
     * @return void
     */
    public function testValuesWithInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createExpression()->setValues(42);
    }

    /**
     * Test data structure with mutated state.
     *
     * Assertions:
     * 1. Mutate all options
     * 2. Partially mutated state
     * 3. Auto-set mode from "condition"
     *
     * @return void
     */
    public function testData()
    {
        /** 1. Mutate all options */
        $values   = [ 'foo', 'baz', 'qux' ];
        $mutation = [
            'direction' => 'asc',
            'mode'      => 'rand',
            'values'    => $values,
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

        $this->assertArrayHasKey('mode', $data);
        $this->assertEquals('rand', $data['mode']);
        $this->assertEquals('rand', $obj->mode());

        $this->assertArrayHasKey('values', $data);
        $this->assertEquals($values, $data['values']);
        $this->assertEquals($values, $obj->values());

        /** 2. Partially mutated state */
        $mutation = [
            'values' => $values
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);

        $defs = $obj->defaultData();
        $this->assertStructHasBasicData($obj, $defs);

        $this->assertEquals($defs['direction'], $obj->direction());
        $this->assertEquals($defs['condition'], $obj->condition());

        $data = $obj->data();
        $this->assertNotEquals($defs['values'], $data['values']);
        $this->assertEquals($values, $data['values']);
        $this->assertEquals('values', $data['mode']);

        /** 3. Auto-set mode from "condition" */
        $mutation = [
            'condition' => '2 = 2'
        ];

        $obj = $this->createExpression();
        $obj->setData($mutation);

        $this->assertEquals('2 = 2', $obj->condition());

        $data = $obj->data();
        $this->assertEquals('2 = 2', $data['condition']);
        $this->assertEquals('custom', $data['mode']);
    }

    /**
     * Test deprecated "string" property.
     *
     * @see FilterTest::testDeprecatedStringExpression()
     *
     * @return void
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
     * @see FilterTest::testDeprecatedStringError()
     *
     * @used-by self::testDeprecatedStringErrorInPhp7()
     *
     * @return void
     */
    public function delegatedTestDeprecatedStringError()
    {
        $this->createExpression()->setData([ 'string' => '1 = 1' ]);
    }

    /**
     *
     *
     * @requires PHP >= 7.0
     * @return   void
     */
    public function testDeprecatedStringErrorInPhp7()
    {
        $this->expectDeprecation();
        $this->delegatedTestDeprecatedStringError();
    }
}
