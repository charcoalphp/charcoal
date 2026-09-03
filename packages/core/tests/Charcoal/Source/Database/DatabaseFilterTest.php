<?php

namespace Charcoal\Tests\Source\Database;

use DateTime;
use UnexpectedValueException;

// From 'charcoal-property'
use Charcoal\Property\PropertyInterface;

// From 'charcoal-core'
use Charcoal\Source\DatabaseSource;
use Charcoal\Source\Database\DatabaseFilter;

use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\CoreContainerIntegrationTrait;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Source\DatabaseExpressionTestTrait;

/**
 * Test {@see DatabaseFilter}.
 */
#[\PHPUnit\Framework\Attributes\CoversMethod(\Charcoal\Source\Database\DatabaseFilter::class, 'isNegating')]
class DatabaseFilterTest extends AbstractTestCase
{
    use CoreContainerIntegrationTrait;
    use DatabaseExpressionTestTrait;
    use ReflectionsTrait;

    /**
     * Create expression for testing.
     */
    final protected function createExpression(): \Charcoal\Source\Database\DatabaseFilter
    {
        return new DatabaseFilter();
    }

    /**
     * Create mock property for testing.
     *
     * @return PropertyInterface
     */
    final public function createProperty()
    {
        $container = $this->getContainer();

        $prop = $container['property/factory']->create('generic');
        $prop->setIdent('xyzzy');

        return $prop;
    }

    /**
     * Assert SQL uses a named placeholder and binds the expected value.
     *
     * @param  DatabaseFilter $obj      The compiled filter.
     * @param  string         $sqlPattern SQL with `%s` where the `:name` placeholder goes.
     * @param  mixed          $bound    Expected bound value (single bind).
     * @return void
     */
    protected function assertSqlBound(DatabaseFilter $obj, $sqlPattern, $bound)
    {
        $sql   = $obj->sql();
        $binds = $obj->binds();

        $this->assertCount(1, $binds);
        $name = array_key_first($binds);
        $this->assertMatchesRegularExpression('/^filter_\d+$/', $name);
        $this->assertSame($bound, $binds[$name]);
        $this->assertSame(sprintf($sqlPattern, ':' . $name), $sql);
    }

    /**
     * Test default table name for default data values.
     *
     * @see \Charcoal\Tests\Source\Database\DatabaseOrderTest::testDefaultValues()
     */
    public function testDefaultValues(): void
    {
        $obj = $this->createExpression();

        $data = $obj->defaultData();
        $this->assertArrayHasKey('table', $data);
        $this->assertEquals(DatabaseSource::DEFAULT_TABLE_ALIAS, $data['table']);
        $this->assertEquals(DatabaseSource::DEFAULT_TABLE_ALIAS, $obj->table());
    }

    /**
     * Test influence of "active" property on SQL compilation.
     */
    public function testInactiveExpression(): void
    {
        $obj = $this->createExpression();
        $obj->setProperty('foo')->setValue('Charcoal');

        $obj->setActive(true);
        $this->assertSqlBound($obj, '`objTable`.`foo` = %s', 'Charcoal');

        $obj->setActive(false);
        $this->assertEquals('', $obj->sql());
        $this->assertSame([], $obj->binds());
    }

    /**
     * Test SQL without conditions.
     *
     * Assertions:
     * 1. Default state
     * 2. Negatable Operators
     * 3. Ignored Operators
     *
     */
    public function testNegation(): void
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertFalse($obj->isNegating());

        /** 2. Negatable Operators */
        $obj->setOperator('!');
        $this->assertTrue($obj->isNegating());

        $obj->setOperator('NOT');
        $this->assertTrue($obj->isNegating());

        /** 3. Ignored Operators */
        $obj->setOperator('IS NOT');
        $this->assertFalse($obj->isNegating());
    }

    /**
     * Test SQL without conditions.
     */
    public function testBlankSql(): void
    {
        $obj = $this->createExpression();

        $this->assertEquals('', $obj->sql());
        $this->assertSame([], $obj->binds());
    }

    /**
     * Test invalid SQL predicate.
     */
    public function testSqlWithoutPredicate(): void
    {
        $obj = $this->createExpression();

        $this->expectException(UnexpectedValueException::class);

        $method = $this->getMethod($obj, 'byPredicate');
        $method->invoke($obj);
    }

    /**
     * Test nested filters.
     *
     *
     * @param  array  $conditions The expressions to define.
     * @param  string $expected   The expected compiled SQL string.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('providedNestedExpressions')]
    public function testNestedSql(array $conditions, $expected): void
    {
        $obj = $this->createExpression();
        $obj->addFilters($conditions);
        $this->assertEquals($expected, $obj->sql());
    }

    /**
     * Provide data for value parsing.
     *
     * @example [ [ <filters>, <SQL> ] ]
     * @used-by self::testNestedSql()
     */
    public static function providedNestedExpressions(): array
    {
        return [
            'One Level'  => self::nestedExpressionsDataset1(),
            'Two Levels' => self::nestedExpressionsDataset2(),
        ];
    }

    /**
     * Dataset #1 for testing nested expressions.
     *
     * @used-by self::providedNestedExpressions()
     */
    protected static function nestedExpressionsDataset1(): array
    {
        $time = new DateTime('3 days ago');
        $timeStr = $time->format('Y-m-d H:i:s');

        $obj = $this->createExpression();
        $obj->addFilters([
            [
                'condition' => 'title LIKE "Hello %"'
            ],
            [
                'property' => 'posted',
                'operator' => '>=',
                'value'    => $time
            ],
            [
                'property' => 'author_id',
                'value'    => 1
            ]
        ]);

        $sql   = $obj->sql();
        $binds = $obj->binds();

        $this->assertCount(2, $binds);
        $names = array_keys($binds);
        $this->assertSame($timeStr, $binds[$names[0]]);
        $this->assertSame(1, $binds[$names[1]]);

        $expected  = '(';
        $expected .= 'title LIKE "Hello %" AND ';
        $expected .= '`objTable`.`posted` >= :' . $names[0] . ' AND ';
        $expected .= '`objTable`.`author_id` = :' . $names[1];
        $expected .= ')';
        $this->assertSame($expected, $sql);
    }

    /**
     * Test nested filters with two levels.
     *
     * @used-by self::providedNestedExpressions()
     */
    protected static function nestedExpressionsDataset2(): array
    {
        $time = date('Y-m-d');

        $obj = $this->createExpression();
        $obj->addFilters([
            [
                'property' => 'author_id',
                'operator' => '!=',
                'value'    => 1
            ],
            [
                'conjunction' => 'OR',
                'filters'     => [
                    [
                        'property' => 'published',
                        'value'    => true
                    ],
                    [
                        'property' => 'posted',
                        'operator' => '<',
                        'value'    => $time
                    ]
                ]
            ],
            [
                'operator' => 'NOT',
                'filters'  => [
                    [
                        'property' => 'title',
                        'value'    => 'Hello World'
                    ],
                    [
                        'property' => 'modified',
                        'operator' => 'IS NULL'
                    ]
                ]
            ]
        ]);

        $sql   = $obj->sql();
        $binds = $obj->binds();

        $this->assertCount(4, $binds);
        $names = array_keys($binds);
        $this->assertSame(1, $binds[$names[0]]);
        $this->assertSame(true, $binds[$names[1]]);
        $this->assertSame($time, $binds[$names[2]]);
        $this->assertSame('Hello World', $binds[$names[3]]);

        $expected  = '(';
        $expected .= '`objTable`.`author_id` != :' . $names[0] . ' AND ';
        $expected .= '(`objTable`.`published` = :' . $names[1] . ' OR `objTable`.`posted` < :' . $names[2] . ') AND NOT ';
        $expected .= '(`objTable`.`title` = :' . $names[3] . ' AND `objTable`.`modified` IS NULL)';
        $expected .= ')';
        $this->assertSame($expected, $sql);
    }

    /**
     * Test nested filters has precedence over other features.
     */
    public function testNestedSqlPrecedence(): void
    {
        $obj = $this->createExpression();

        // Should be ignored
        $obj->setProperty('foo')->setOperator('=')->setValue('bar');

        // Should take precedence
        $obj->setCondition('1 = 1');
        $this->assertEquals('1 = 1', $obj->sql());
        $this->assertSame([], $obj->binds());
    }

    /**
     * Test invalid SQL nested filters.
     */
    public function testSqlWithoutNestedExpressions(): void
    {
        $obj = $this->createExpression();

        $this->expectException(UnexpectedValueException::class);

        $method = $this->getMethod($obj, 'byFilters');
        $method->invoke($obj);
    }

    /**
     * Test "condition" property with and without placeholders.
     */
    public function testCustomSql(): void
    {
        $obj = $this->createExpression();

        $obj->setCondition('objTable.foo = objTable.baz');
        $this->assertEquals('objTable.foo = objTable.baz', $obj->sql());
        $this->assertSame([], $obj->binds());
    }

    /**
     * Test the negation of the "condition" property with the "operator" property.
     */
    public function testCustomSqlNegation(): void
    {
        $obj = $this->createExpression();

        $obj->setOperator('NOT')->setCondition('objTable.foo = objTable.baz');
        $this->assertEquals('NOT (objTable.foo = objTable.baz)', $obj->sql());
        $this->assertSame([], $obj->binds());
    }

    /**
     * Test "condition" property has precedence over other features.
     */
    public function testCustomSqlPrecedence(): void
    {
        $obj = $this->createExpression();

        // Should be ignored
        $obj->setProperty('foo')->setOperator('=')->setValue('bar');

        // Should take precedence
        $obj->setCondition('1 = 1');
        $this->assertEquals('1 = 1', $obj->sql());
        $this->assertSame([], $obj->binds());
    }

    /**
     * Test invalid custom SQL.
     */
    public function testCustomSqlWithoutQuery(): void
    {
        $obj = $this->createExpression();

        $this->expectException(UnexpectedValueException::class);

        $method = $this->getMethod($obj, 'byCondition');
        $method->invoke($obj);
    }

    /**
     * Test condition compilation.
     */
    public function testCompileConditions(): void
    {
        $obj = $this->createExpression();

        $method = $this->getMethod($obj, 'compileConditions');
        $result = $method->invoke($obj, []);
        $this->assertEquals('()', $result);
    }

    /**
     * Test basic SQL operator without a value.
     */
    public function testSqlOperatorWithoutValue(): void
    {
        $obj = $this->createExpression();

        $obj->setData([
            'property' => 'xyzzy',
            'operator' => '=',
        ]);

        $this->expectException(UnexpectedValueException::class);
        $obj->sql();
    }

    /**
     * Test comparison SQL operators.
     *
     *
     * @param  string $operator A SQL operator.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideComparisonOperators')]
    public function testSqlComparisonOperators(string $operator): void
    {
        $obj = $this->createExpression();
        $obj->setData([
            'property' => 'xyzzy',
            'operator' => $operator,
            'value'    => 'Charcoal',
        ]);

        $this->assertSqlBound($obj, '`objTable`.`xyzzy` ' . $operator . ' %s', 'Charcoal');
    }

    /**
     * Test condition-style SQL operators ("value" is ignored).
     *
     *
     * @param  string $operator A SQL operator.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideConditionalOperators')]
    public function testSqlConditionalOperators(string $operator): void
    {
        $obj = $this->createExpression();
        $obj->setData([
            'property' => 'xyzzy',
            'operator' => $operator,
            'value'    => 'Charcoal',
        ]);

        $this->assertEquals('`objTable`.`xyzzy` '.$operator, $obj->sql());
        $this->assertSame([], $obj->binds());
    }

    /**
     * Test NOT-style SQL operators ("value" is ignored).
     *
     *
     * @param  string $operator A SQL operator.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideNegationOperators')]
    public function testSqlNegationOperators(string $operator): void
    {
        $obj = $this->createExpression();
        $obj->setData([
            'property' => 'xyzzy',
            'operator' => $operator,
            'value'    => 'Charcoal',
        ]);

        $this->assertEquals($operator.' `objTable`.`xyzzy`', $obj->sql());
        $this->assertSame([], $obj->binds());
    }

    /**
     * Test list-based SQL operators.
     *
     *
     * @param  string $operator  A SQL operator.
     * @param  string $delimiter The set's delimiter.
     * @param  string $expected  The expected result.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideSetOperators')]
    public function testSqlSetOperators(string $operator, string $delimiter, string $expected): void
    {
        $obj = $this->createExpression();

        $value = [ 'foo', 'bar', 'qux' ];
        $obj->setData([
            'property' => 'xyzzy',
            'operator' => $operator,
            'value'    => $value,
        ]);

        $asserter($this, $obj, $value);
    }

    /**
     * Test list-based SQL operator without a value.
     *
     *
     * @param  string $operator  A SQL operator.
     * @param  string $delimiter The set's delimiter.
     * @param  string $expected  Unused; The expected result.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideSetOperators')]
    public function testSqlSetOperatorsWithoutValue(string $operator, string $delimiter, string $expected): void
    {
        unset($asserter);

        $obj = $this->createExpression();

        $obj->setData([
            'property' => 'xyzzy',
            'operator' => $operator,
        ]);

        $this->expectException(UnexpectedValueException::class);
        $obj->sql();
    }

    /**
     * Empty IN list becomes a safe false predicate.
     *
     * @return void
     */
    public function testSqlEmptyIn()
    {
        $obj = $this->createExpression();
        $obj->setData([
            'property' => 'xyzzy',
            'operator' => 'IN',
            'value'    => [],
        ]);

        $this->assertSame('0=1', $obj->sql());
        $this->assertSame([], $obj->binds());
    }

    /**
     * Empty NOT IN list becomes a safe true predicate.
     *
     * @return void
     */
    public function testSqlEmptyNotIn()
    {
        $obj = $this->createExpression();
        $obj->setData([
            'property' => 'xyzzy',
            'operator' => 'NOT IN',
            'value'    => [],
        ]);

        $this->assertSame('1=1', $obj->sql());
        $this->assertSame([], $obj->binds());
    }

    /**
     * Test SQL function.
     */
    public function testSqlFunction(): void
    {
        $obj = $this->createExpression();
        $obj->setData([
            'property' => 'xyzzy',
            'operator' => '=',
            'value'    => 'Charcoal',
            'function' => 'reverse',
        ]);

        $this->assertSqlBound($obj, 'REVERSE(`objTable`.`xyzzy`) = %s', 'Charcoal');
    }

    /**
     * Test SQL condition with multiple field names.
     */
    public function testSqlFields(): void
    {
        $container = $this->getContainer();

        $this->getContainerProvider()->registerMultilingualTranslator($container);

        $prop = $this->createProperty();
        $prop->setL10n(true);

        $obj = $this->createExpression();
        $obj->setProperty($prop)->setOperator('=')->setValue('Charcoal');

        $sql   = $obj->sql();
        $binds = $obj->binds();

        $this->assertCount(4, $binds);
        foreach ($binds as $bound) {
            $this->assertSame('Charcoal', $bound);
        }
        $names = array_keys($binds);

        $expected  = '(';
        $expected .= '`objTable`.`xyzzy_en` = :' . $names[0] . ' OR ';
        $expected .= '`objTable`.`xyzzy_fr` = :' . $names[1] . ' OR ';
        $expected .= '`objTable`.`xyzzy_de` = :' . $names[2] . ' OR ';
        $expected .= '`objTable`.`xyzzy_es` = :' . $names[3];
        $expected .= ')';
        $this->assertSame($expected, $sql);
    }

    /**
     * Provide data for simple operators.
     *
     * @used-by self::testSqlComparisonOperators()
     */
    public static function provideComparisonOperators(): array
    {
        return [
            [ '=' ], [ '!=' ],
            [ '>' ], [ '>=' ], [ '<' ], [ '<=' ],
            [ 'IS' ], [ 'IS NOT' ],
            [ 'LIKE' ], [ 'NOT LIKE' ],
        ];
    }

    /**
     * Provide data for sets-style operators.
     *
     * @used-by self::testSqlSetOperators()
     */
    public static function provideSetOperators(): array
    {
        return [
            'FIND_IN_SET' => [
                'FIND_IN_SET',
                function (self $test, DatabaseFilter $obj, array $value) {
                    $sql   = $obj->sql();
                    $binds = $obj->binds();
                    $test->assertCount(1, $binds);
                    $name = array_key_first($binds);
                    $test->assertSame(implode(',', $value), $binds[$name]);
                    $test->assertSame(
                        'FIND_IN_SET(:' . $name . ', `objTable`.`xyzzy`)',
                        $sql
                    );
                },
            ],
            'IN' => [
                'IN',
                function (self $test, DatabaseFilter $obj, array $value) {
                    $sql   = $obj->sql();
                    $binds = $obj->binds();
                    $test->assertCount(3, $binds);
                    $names = array_keys($binds);
                    $test->assertSame($value, array_values($binds));
                    $test->assertSame(
                        '`objTable`.`xyzzy` IN (:' . implode(', :', $names) . ')',
                        $sql
                    );
                },
            ],
            'NOT IN' => [
                'NOT IN',
                function (self $test, DatabaseFilter $obj, array $value) {
                    $sql   = $obj->sql();
                    $binds = $obj->binds();
                    $test->assertCount(3, $binds);
                    $names = array_keys($binds);
                    $test->assertSame($value, array_values($binds));
                    $test->assertSame(
                        '`objTable`.`xyzzy` NOT IN (:' . implode(', :', $names) . ')',
                        $sql
                    );
                },
            ],
        ];
    }

    /**
     * Provide data for condition-style operators.
     *
     * @used-by self::testSqlConditionalOperators()
     */
    public static function provideConditionalOperators(): array
    {
        return [
            [ 'IS NULL' ], [ 'IS NOT NULL' ],
            [ 'IS TRUE' ], [ 'IS NOT TRUE' ],
            [ 'IS FALSE' ], [ 'IS NOT FALSE' ],
            [ 'IS UNKNOWN' ], [ 'IS NOT UNKNOWN' ],
        ];
    }

    /**
     * Provide data for logical NOT operators.
     *
     * @used-by self::testSqlNegationOperators()
     */
    public static function provideNegationOperators(): array
    {
        return [
            [ '!' ],
            [ 'NOT' ],
        ];
    }
}
