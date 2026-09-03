<?php

namespace Charcoal\Tests\Source;

use Charcoal\Source\Database\DatabaseFilter;
use Charcoal\Source\ExpressionTreeSanitizer;
use Charcoal\Source\Filter;
use Charcoal\Source\Order;

use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Mock\FilterCollectionClass;
use Charcoal\Tests\Mock\OrderCollectionClass;

/**
 * Tests for LS05: sanitize untrusted filter/order trees.
 */
class ExpressionTreeSanitizerTest extends AbstractTestCase
{
    /**
     * Predicate structures are preserved; raw condition is stripped.
     *
     * @return void
     */
    public function testSanitizeFiltersStripsConditionKeepsPredicate()
    {
        $payload = "' OR '1'='1";
        $input = [
            [
                'property' => 'name',
                'operator' => '=',
                'value'    => $payload,
            ],
            [
                'condition' => '1=1 OR 1=1',
            ],
            'raw SQL string',
            [
                'string' => 'deprecated raw',
            ],
            [
                'conjunction' => 'OR',
                'filters'     => [
                    [
                        'property' => 'title',
                        'value'    => 'ok',
                    ],
                    [
                        'condition' => 'evil',
                    ],
                ],
            ],
        ];

        $out = ExpressionTreeSanitizer::sanitizeFilters($input);

        $this->assertCount(2, $out);
        $this->assertSame('name', $out[0]['property']);
        $this->assertSame($payload, $out[0]['value']);
        $this->assertArrayNotHasKey('condition', $out[0]);

        $this->assertSame('OR', $out[1]['conjunction']);
        $this->assertCount(1, $out[1]['filters']);
        $this->assertSame('title', $out[1]['filters'][0]['property']);
    }

    /**
     * Custom order conditions are stripped from untrusted lists.
     *
     * @return void
     */
    public function testSanitizeOrdersStripsCustomCondition()
    {
        $input = [
            [
                'property'  => 'id',
                'direction' => 'asc',
            ],
            [
                'condition' => 'RAND()',
                'mode'      => 'custom',
            ],
            'RAND()',
            [
                'property' => 'name',
                'mode'     => 'desc',
            ],
        ];

        $out = ExpressionTreeSanitizer::sanitizeOrders($input);

        $this->assertCount(2, $out);
        $this->assertSame('id', $out[0]['property']);
        $this->assertSame('name', $out[1]['property']);
        $this->assertSame('desc', $out[1]['mode']);
    }

    /**
     * Untrusted setFilters does not apply stored raw conditions.
     *
     * @return void
     */
    public function testUntrustedSetFiltersDropsCondition()
    {
        $collector = new FilterCollectionClass();
        $collector->setFilters([
            [
                'property' => 'status',
                'value'    => 'active',
            ],
            [
                'condition' => '1=1',
            ],
        ], false);

        $filters = $collector->filters();
        $this->assertCount(1, $filters);
        $this->assertInstanceOf(Filter::class, $filters[0]);
        $this->assertFalse($filters[0]->hasCondition());
        $this->assertSame('status', $filters[0]->property());
    }

    /**
     * Trusted setFilters still accepts code-defined conditions.
     *
     * @return void
     */
    public function testTrustedSetFiltersKeepsCondition()
    {
        $collector = new FilterCollectionClass();
        $collector->setFilters([
            [
                'condition' => '(expiry_date > NOW() OR expiry_date IS NULL)',
            ],
        ], true);

        $filters = $collector->filters();
        $this->assertCount(1, $filters);
        $this->assertTrue($filters[0]->hasCondition());
        $this->assertSame(
            '(expiry_date > NOW() OR expiry_date IS NULL)',
            $filters[0]->condition()
        );
    }

    /**
     * Untrusted setOrders drops custom condition orders.
     *
     * @return void
     */
    public function testUntrustedSetOrdersDropsCondition()
    {
        $collector = new OrderCollectionClass();
        $collector->setOrders([
            [
                'property'  => 'id',
                'direction' => 'desc',
            ],
            [
                'condition' => 'FIELD(id, 1, 2)',
                'mode'      => 'custom',
            ],
        ], false);

        $orders = $collector->orders();
        $this->assertCount(1, $orders);
        $this->assertInstanceOf(Order::class, $orders[0]);
        $this->assertFalse($orders[0]->hasCondition());
        $this->assertSame('id', $orders[0]->property());
    }

    /**
     * End-to-end: sanitized tree + DatabaseFilter does not emit raw condition SQL.
     *
     * @return void
     */
    public function testSanitizedTreeCannotInjectViaDatabaseFilter()
    {
        $tree = ExpressionTreeSanitizer::sanitizeFilters([
            [
                'conjunction' => 'AND',
                'filters'     => [
                    [
                        'property' => 'name',
                        'operator' => '=',
                        'value'    => 'safe',
                    ],
                    [
                        'condition' => "1=1) OR (1=1",
                    ],
                ],
            ],
        ]);

        $filter = new DatabaseFilter();
        $filter->setData([
            'filters' => $tree,
        ]);

        $sql = $filter->sql();
        $this->assertStringNotContainsString('1=1) OR (1=1', $sql);
        $this->assertMatchesRegularExpression('/`objTable`\.`name` = :filter_\d+/', $sql);
    }
}
