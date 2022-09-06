<?php

namespace Charcoal\Tests\Source;

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
     *
     * @return void
     */
    public function testDeprecatedTableNameExpression()
    {
        $obj = $this->createExpression();

        @$obj->setData([ 'table_name' => 'foobar' ]);
        $this->assertEquals('foobar', $obj->table());
    }

    /**
     * Test "table_name" property deprecation notice.
     *
     * @used-by self::testDeprecatedTableNameErrorInPhp7()
     *
     * @return void
     */
    public function delegatedTestDeprecatedTableNameError()
    {
        $this->createExpression()->setData([ 'table_name' => 'foobar' ]);
    }

    /**
     * @requires PHP >= 7.0
     * @return   void
     */
    public function testDeprecatedTableNameErrorInPhp7()
    {
        $this->expectDeprecation();
        $this->delegatedTestDeprecatedTableNameError();
    }

    /**
     * Assert the given expression has data from {@see ExpressionFieldInterface}.
     *
     * @param ExpressionFieldInterface $obj      The expression to test.
     * @param array|null               $expected The expected data subset.
     * @return void
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
