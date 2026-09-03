<?php

namespace Charcoal\Tests\Source\Database;

use Charcoal\Source\Database\DatabaseOrder;

use Charcoal\Tests\AbstractTestCase;

/**
 * Regression tests: ORDER BY FIELD() values must not become SQL structure (LS10).
 */
class DatabaseOrderInjectionTest extends AbstractTestCase
{
    /**
     * Classic SQLi payloads used as FIELD() list members.
     *
     * @return array<string,array{0:string}>
     */
    public function provideInjectionPayloads()
    {
        return [
            'or_true'          => [ "' OR '1'='1" ],
            'or_true_comment'  => [ "' OR '1'='1' --" ],
            'drop_table'       => [ '1; DROP TABLE users--' ],
            'union_select'     => [ "1' UNION SELECT NULL--" ],
            'field_breakout'   => [ 'x), (SELECT password FROM users)--' ],
            'admin_comment'    => [ "admin'--" ],
            'backslash_escape' => [ "\\'; DROP TABLE users; --" ],
            'nested_quotes'    => [ "'\" OR \"\"=\"" ],
        ];
    }

    /**
     * @dataProvider provideInjectionPayloads
     *
     * @param  string $payload Attacker-controlled FIELD() value.
     * @return void
     */
    public function testValuesPayloadIsBoundNotInterpolated($payload)
    {
        $obj = new DatabaseOrder();
        $obj->setData([
            'property' => 'status',
            'mode'     => 'values',
            'values'   => [ $payload ],
        ]);

        $sql = $obj->sql();
        $binds = $obj->binds();

        $this->assertMatchesRegularExpression('/FIELD\(`objTable`\.`status`, :order_\d+\)/', $sql);
        $this->assertStringNotContainsString($payload, $sql);
        $this->assertCount(1, $binds);
        $this->assertSame($payload, array_values($binds)[0]);
    }

    /**
     * Multi-value FIELD() with an injection element only appears in binds.
     *
     * @return void
     */
    public function testValuesArrayWithInjectionElement()
    {
        $payload = "' OR '1'='1";
        $obj = new DatabaseOrder();
        $obj->setData([
            'property' => 'country',
            'mode'     => 'values',
            'values'   => [ 'FR', $payload, 'CA' ],
        ]);

        $sql = $obj->sql();
        $binds = $obj->binds();

        $this->assertMatchesRegularExpression(
            '/FIELD\(`objTable`\.`country`, :order_\d+, :order_\d+, :order_\d+\)/',
            $sql
        );
        $this->assertStringNotContainsString($payload, $sql);
        $this->assertSame([ 'FR', $payload, 'CA' ], array_values($binds));
    }
}
