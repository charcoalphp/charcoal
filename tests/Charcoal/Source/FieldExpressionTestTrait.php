<?php

namespace Charcoal\Tests\Source;

// From 'charcoal-core'
use Charcoal\Source\ExpressionInterface;
use Charcoal\Source\FieldInterface;
use Charcoal\Source\FieldTrait;

/**
 *
 */
trait FieldExpressionTestTrait
{
    /**
     * Assert the given expression has data from {@see FieldInterface}.
     *
     * @param FieldInterface $obj      The expression to test.
     * @param array|null     $expected The expected data subset.
     */
    public function assertStructHasFieldData(FieldInterface $obj, array $expected = null)
    {
        if (empty($expected)) {
            $expected = [
                'property'   => 'col',
                'table_name' => 'tbl',
            ];
            $obj->setData($mutation);
        }

        $data = $obj->data();

        $this->assertArrayHasKey('property', $data);
        $this->assertEquals($expected['property'], $data['property']);
        $this->assertEquals($expected['property'], $obj->property());

        $this->assertArrayHasKey('table_name', $data);
        $this->assertEquals($expected['table_name'], $data['table_name']);
        $this->assertEquals($expected['table_name'], $obj->tableName());
    }
}
