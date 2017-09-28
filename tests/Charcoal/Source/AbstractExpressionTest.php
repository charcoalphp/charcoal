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
            'active' => [ 'active', true ],
            'string' => [ 'string', null ],
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
     * Test the "string" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Trimmed value
     * 5. Accepts NULL
     * 6. Swaps blank string for NULL
     */
    public function testString()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->string());

        /** 2. Mutated Value */
        $that = $obj->setString('1 = 1');
        $this->assertInternalType('string', $obj->string());
        $this->assertEquals('1 = 1', $obj->string());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Trimmed value */
        $obj->setString('   1 = 1  ');
        $this->assertEquals('1 = 1', $obj->string());

        /** 5. Accepts NULL */
        $obj->setString(null);
        $this->assertNull($obj->string());

        /** 6. Swaps blank string for NULL */
        $obj->setString('  ');
        $this->assertNull($obj->string());
    }

    /**
     * Test the conditional check of "string".
     */
    public function testHasString()
    {
        $obj = $this->createExpression();

        $this->assertFalse($obj->hasString());

        $obj->setString('  ');
        $this->assertFalse($obj->hasString());

        $that = $obj->setString('1 = 1');
        $this->assertTrue($obj->hasString());
    }

    /**
     * Test "string" property with invalid value.
     */
    public function testStringWithInvalidValue()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $this->createExpression()->setString([]);
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
            'active' => false,
            'string' => '1 = 1',
        ];
        $obj->setData($mutation);
        $this->assertStructHasBasicData($obj, $mutation);

        /** 2. Partially mutated state */
        $obj = $this->createExpression();
        $obj->setData([ 'string' => '2 = 2' ]);

        $data = $obj->data();
        $this->assertArrayNotHasKey('active', $data);
        $this->assertArrayHasKey('string', $data);
        $this->assertEquals('2 = 2', $data['string']);

        $obj = $this->createExpression();
        $obj->setData([ 'active' => false ]);

        $data = $obj->data();
        $this->assertArrayNotHasKey('string', $data);
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
            'active' => false,
            'string' => '1 = 1',
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
        $this->assertNull($that->string());

        /** 2. Serialization from mutated state */
        $mutation = [
            'active' => false,
            'string' => '1 = 1',
        ];
        $obj->setData($mutation);
        $that = unserialize(serialize($obj));
        $this->assertInstanceOf(AbstractExpression::class, $that);
        $this->assertEquals($obj, $that);
        $this->assertFalse($that->active());
        $this->assertEquals('1 = 1', $that->string());
    }
}
