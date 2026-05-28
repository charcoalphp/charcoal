<?php

namespace Charcoal\Tests\Source;

// From 'charcoal-core'
use Charcoal\Source\ExpressionInterface;
use Charcoal\Source\ExpressionFieldInterface;
use Charcoal\Source\ExpressionFieldTrait;
use Charcoal\Source\Filter;
use Charcoal\Source\Order;

/**
 * Shared tests for implementations of {@see ExpressionFieldTrait}
 * and {@see ExpressionFieldInterface}.
 */
trait ExpressionTestFieldTrait
{
    /**
     * Test deprecated "table_name" property.
     */
    public function testDeprecatedTableNameExpression(): void
    {
        $obj = $this->createExpression();

        @$obj->setData([ 'table_name' => 'foobar' ]);
        $this->assertEquals('foobar', $obj->table());
    }

    /**
     * Test "table_name" property deprecation notice.
     */
    public function testDeprecatedTableNameError(): void
    {
        $expression = $this->createExpression();
        $message = match (get_class($expression)) {
            Filter::class => 'Filter expression option "table_name" is deprecated in favour of "table": foobar',
            Order::class => 'Sort expression option "table_name" is deprecated in favour of "table": foobar',
            default => 'Expression option "table_name" is deprecated in favour of "table": foobar',
        };
        $this->expectUserDeprecationMessage($message);
        $expression->setData([ 'table_name' => 'foobar' ]);
    }

    /**
     * Assert the given expression has data from {@see ExpressionFieldInterface}.
     *
     * @param ExpressionFieldInterface $obj      The expression to test.
     * @param array|null               $expected The expected data subset.
     */
    public function assertStructHasFieldData(ExpressionFieldInterface $obj, ?array $expected = null): void
    {
        if ($expected === null || $expected === []) {
            $expected = [
                'property' => 'col',
                'table'    => 'tbl',
            ];
            $obj->setData($expected);
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
