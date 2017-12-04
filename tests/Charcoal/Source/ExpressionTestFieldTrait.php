<?php

namespace Charcoal\Tests\Source;

// From PHPUnit
use PHPUnit_Framework_Error;

// From 'charcoal-core'
use Charcoal\Source\ExpressionInterface;
use Charcoal\Source\ExpressionFieldInterface;
use Charcoal\Source\ExpressionFieldTrait;

/**
 * Shared tests for implementations of {@see ExpressionFieldTrait}
 * and {@see ExpressionFieldInterface}.
 */
trait ExpressionTestFieldTrait
{
    /**
     * Test deprecated "table_name" property.
     */
    public function testDeprecatedTableNameExpression()
    {
        $obj = $this->createExpression();

        @$obj->setData([ 'table_name' => 'foobar' ]);
        $this->assertEquals('foobar', $obj->table());
    }

    /**
     * Test "table_name" property deprecation notice.
     */
    public function testDeprecatedTableNameError()
    {
        $this->setExpectedException(PHPUnit_Framework_Error::class);
        $this->createExpression()->setData([ 'table_name' => 'foobar' ]);
    }

    /**
     * Assert the given expression has data from {@see ExpressionFieldInterface}.
     *
     * @param ExpressionFieldInterface $obj      The expression to test.
     * @param array|null               $expected The expected data subset.
     */
    public function assertStructHasFieldData(ExpressionFieldInterface $obj, array $expected = null)
    {
        if (empty($expected)) {
            $expected = [
                'property' => 'col',
                'table'    => 'tbl',
            ];
            $obj->setData($mutation);
        }

        $data = $obj->data();

        $this->assertArrayHasKey('property', $data);
        $this->assertEquals($expected['property'], $data['property']);
        $this->assertEquals($expected['property'], $obj->property());

        $this->assertArrayHasKey('table', $data);
        $this->assertEquals($expected['table'], $data['table']);
        $this->assertEquals($expected['table'], $obj->table());
    }
}
