<?php

namespace Charcoal\Tests\Source\Database;

use Charcoal\Source\Database\DatabaseFilter;

use Charcoal\Tests\AbstractTestCase;

/**
 * Regression tests: filter values must not become SQL structure (LS01).
 */
class DatabaseFilterInjectionTest extends AbstractTestCase
{
    /**
     * Classic SQLi payloads used as filter values.
     *
     * @return array<string,array{0:string}>
     */
    public function provideInjectionPayloads()
    {
        return [
            'or_true'           => [ "' OR '1'='1" ],
            'or_true_comment'   => [ "' OR '1'='1' --" ],
            'drop_table'        => [ '1; DROP TABLE users--' ],
            'union_select'      => [ "1' UNION SELECT NULL--" ],
            'like_breakout'     => [ "%' OR '%'='" ],
            'admin_comment'     => [ "admin'--" ],
            'backslash_escape'  => [ "\\'; DROP TABLE users; --" ],
            'nested_quotes'     => [ "'\" OR \"\"=\"" ],
        ];
    }

    /**
     * @dataProvider provideInjectionPayloads
     *
     * @param  string $payload Attacker-controlled filter value.
     * @return void
     */
    public function testEqualityPayloadIsBoundNotInterpolated($payload)
    {
        $this->assertPayloadBound('=', $payload);
    }

    /**
     * @dataProvider provideInjectionPayloads
     *
     * @param  string $payload Attacker-controlled filter value.
     * @return void
     */
    public function testLikePayloadIsBoundNotInterpolated($payload)
    {
        $this->assertPayloadBound('LIKE', $payload);
    }

    /**
     * @dataProvider provideInjectionPayloads
     *
     * @param  string $payload Attacker-controlled filter value.
     * @return void
     */
    public function testInPayloadIsBoundNotInterpolated($payload)
    {
        $this->assertPayloadBound('IN', $payload);
    }

    /**
     * @dataProvider provideInjectionPayloads
     *
     * @param  string $payload Attacker-controlled filter value.
     * @return void
     */
    public function testNotInPayloadIsBoundNotInterpolated($payload)
    {
        $this->assertPayloadBound('NOT IN', $payload);
    }

    /**
     * @dataProvider provideInjectionPayloads
     *
     * @param  string $payload Attacker-controlled filter value.
     * @return void
     */
    public function testFindInSetPayloadIsBoundNotInterpolated($payload)
    {
        $this->assertPayloadBound('FIND_IN_SET', $payload);
    }

    /**
     * Array IN with an injection element only appears in binds.
     *
     * @return void
     */
    public function testInArrayWithInjectionElement()
    {
        $payload = "' OR '1'='1";
        $obj = new DatabaseFilter();
        $obj->setData([
            'property' => 'name',
            'operator' => 'IN',
            'value'    => [ 'safe', $payload, 'also-safe' ],
        ]);

        $sql   = $obj->sql();
        $binds = $obj->binds();

        $this->assertCount(3, $binds);
        $this->assertContains($payload, $binds);
        $this->assertStringNotContainsString($payload, $sql);
        $this->assertDoesNotMatchRegularExpression("/'\\s*OR\\s*'/", $sql);
        $this->assertMatchesRegularExpression(
            '/objTable.`name` IN \(:filter_\d+, :filter_\d+, :filter_\d+\)/',
            $sql
        );
    }

    /**
     * Relation-style aliased LIKE (LS02) must bind the search value, not interpolate it.
     *
     * @return void
     */
    public function testAliasedTableLikeBindsPayload()
    {
        $payload = "%' OR '1'='1%";
        $obj = new DatabaseFilter();
        $obj->setData([
            'table'    => 'relTable1',
            'property' => 'name_en',
            'operator' => 'LIKE',
            'value'    => $payload,
        ]);

        $sql   = $obj->sql();
        $binds = $obj->binds();

        $this->assertCount(1, $binds);
        $this->assertSame($payload, array_values($binds)[0]);
        $this->assertStringNotContainsString($payload, $sql);
        $this->assertMatchesRegularExpression(
            '/relTable1.`name_en` LIKE :filter_\d+/',
            $sql
        );
    }

    /**
     * Trusted code-defined conditions remain inlined (no binds) — intentional escape hatch.
     *
     * @return void
     */
    public function testTrustedConditionRemainsRaw()
    {
        $obj = new DatabaseFilter();
        $obj->setCondition('(expiry_date > NOW() OR expiry_date IS NULL)');

        $this->assertSame('(expiry_date > NOW() OR expiry_date IS NULL)', $obj->sql());
        $this->assertSame([], $obj->binds());
    }

    /**
     * Compile a predicate and assert the payload is parameterized.
     *
     * @param  string $operator Filter operator.
     * @param  string $payload  Injection payload.
     * @return void
     */
    protected function assertPayloadBound($operator, $payload)
    {
        $obj = new DatabaseFilter();
        $obj->setData([
            'property' => 'name',
            'operator' => $operator,
            'value'    => $payload,
        ]);

        $sql   = $obj->sql();
        $binds = $obj->binds();

        $this->assertNotEmpty($binds);
        $this->assertContains($payload, array_values($binds));
        $this->assertStringNotContainsString($payload, $sql);

        // Must not look like a quoted literal embedding the payload.
        $this->assertStringNotContainsString("'" . $payload . "'", $sql);

        // Breakout markers must not appear as SQL structure outside binds.
        $this->assertDoesNotMatchRegularExpression("/'\\s*OR\\s*'/", $sql);
        $this->assertStringNotContainsString('UNION SELECT', strtoupper($sql));
        $this->assertStringNotContainsString('DROP TABLE', strtoupper($sql));

        $this->assertMatchesRegularExpression('/:filter_\d+/', $sql);
    }
}
