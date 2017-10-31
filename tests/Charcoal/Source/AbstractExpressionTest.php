<?php

namespace Charcoal\Tests\Source;

use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\AbstractExpression;
use Charcoal\Source\ExpressionInterface;
use Charcoal\Tests\ContainerIntegrationTrait;
use Charcoal\Tests\Source\QueryExpressionTestTrait;

/**
 *
 */
class AbstractExpressionTest extends \PHPUnit_Framework_TestCase
{
    use ContainerIntegrationTrait;
    use QueryExpressionTestTrait;

    /**
     * Create expression for testing.
     *
     * @return AbstractExpression
     */
    final protected function createExpression()
    {
        return $this->getMockForAbstractClass(AbstractExpression::class);
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
            'active'    => [ 'active',    true ],
            'condition' => [ 'condition', null ],
        ];
    }

    /**
     * Test the "name" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     */
    public function testName()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->name());

        /** 2. Mutated Value */
        $that = $obj->setName('foo');
        $this->assertEquals('foo', $obj->name());

        /** 3. Chainable */
        $this->assertSame($obj, $that);
    }

    /**
     * Test "name" property with invalid value.
     */
    public function testNameWithInvalidValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setName(0);
    }

    /**
     * Test the "active" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Cast value to boolean
     */
    public function testActive()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertTrue($obj->active());

        /** 2. Mutated Value */
        $that = $obj->setActive(false);
        $this->assertFalse($obj->active());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Cast value to boolean */
        $obj->setActive(1);
        $this->assertTrue($obj->active());

        $obj->setActive(0);
        $this->assertFalse($obj->active());
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
     */
    public function testConditionExpression()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->condition());

        /** 2. Mutated Value */
        $that = $obj->setCondition('1 = 1');
        $this->assertInternalType('string', $obj->condition());
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
     * Test deprecated "string" property.
     */
    public function testDeprecatedStringExpression()
    {
        $obj = $this->createExpression();

        @$obj->setData([ 'string' => '1 = 1' ]);
        $this->assertEquals('1 = 1', $obj->condition());
    }

    /**
     * Test "string" property deprecation notice.
     */
    public function testDeprecatedStringError()
    {
        $this->setExpectedException(\PHPUnit_Framework_Error::class);
        $this->createExpression()->setData([ 'string' => '1 = 1' ]);

    }

    /**
     * Test "condition" property with invalid value.
     */
    public function testConditionExpressionWithInvalidValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setCondition([]);
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
            'active'    => false,
            'condition' => '1 = 1',
        ];
        $obj->setData($mutation);
        $this->assertStructHasBasicData($obj, $mutation);

        /** 2. Partially mutated state */
        $obj = $this->createExpression();
        $obj->setData([ 'condition' => '2 = 2' ]);

        $data = $obj->data();
        $this->assertArrayNotHasKey('active', $data);
        $this->assertArrayHasKey('condition', $data);
        $this->assertEquals('2 = 2', $data['condition']);

        $obj = $this->createExpression();
        $obj->setData([ 'active' => false ]);

        $data = $obj->data();
        $this->assertArrayNotHasKey('condition', $data);
        $this->assertArrayHasKey('active', $data);
        $this->assertFalse($data['active']);
    }

    /**
     * Test JSON serialization.
     *
     * Assertions:
     * 1. Serialization from default state
     * 2. Serialization from mutated state
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
            'active'    => false,
            'condition' => '1 = 1',
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
     */
    public function testSerializable()
    {
        $obj = $this->createExpression();

        /** 1. Serialization from default state */
        $that = unserialize(serialize($obj));
        $this->assertInstanceOf(AbstractExpression::class, $that);
        $this->assertEquals($obj, $that);
        $this->assertTrue($that->active());
        $this->assertNull($that->condition());

        /** 2. Serialization from mutated state */
        $mutation = [
            'active'    => false,
            'condition' => '1 = 1',
        ];
        $obj->setData($mutation);
        $that = unserialize(serialize($obj));
        $this->assertInstanceOf(AbstractExpression::class, $that);
        $this->assertEquals($obj, $that);
        $this->assertFalse($that->active());
        $this->assertEquals('1 = 1', $that->condition());
    }
}
