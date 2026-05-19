<?php

namespace Charcoal\Tests\Source\Database;

use DateTime;
use UnexpectedValueException;

// From 'charcoal-property'
use Charcoal\Property\GenericProperty;
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
        $this->assertEquals('objTable.`foo` = \'Charcoal\'', $obj->sql());

        $obj->setActive(false);
        $this->assertEquals('', $obj->sql());
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
    public function providedNestedExpressions(): array
    {
        return [
            'One Level'  => $this->nestedExpressionsDataset1(),
            'Two Levels' => $this->nestedExpressionsDataset2(),
        ];
    }

    /**
     * Dataset #1 for testing nested expressions.
     *
     * @used-by self::providedNestedExpressions()
     */
    protected function nestedExpressionsDataset1(): array
    {
        $time = new DateTime('3 days ago');

        $conditions = [
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
        ];

        $expected  = '(';
        $expected .= 'title LIKE "Hello %" AND ';
        $expected .= 'objTable.`posted` >= \''.$time->format('Y-m-d H:i:s').'\' AND ';
        $expected .= 'objTable.`author_id` = \'1\'';
        $expected .= ')';

        return [ $conditions, $expected ];
    }

    /**
     * Dataset #2 for testing nested expressions.
     *
     * @used-by self::providedNestedExpressions()
     */
    protected function nestedExpressionsDataset2(): array
    {
        $time = date('Y-m-d');

        $conditions = [
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
        ];

        $expected  = '(';
        $expected .= 'objTable.`author_id` != \'1\' AND ';
        $expected .= '(objTable.`published` = \'1\' OR objTable.`posted` < \''.$time.'\') AND NOT ';
        $expected .= '(objTable.`title` = \'Hello World\' AND objTable.`modified` IS NULL)';
        $expected .= ')';

        return [ $conditions, $expected ];
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
    }

    /**
     * Test the negation of the "condition" property with the "operator" property.
     */
    public function testCustomSqlNegation(): void
    {
        $obj = $this->createExpression();

        $obj->setOperator('NOT')->setCondition('objTable.foo = objTable.baz');
        $this->assertEquals('NOT (objTable.foo = objTable.baz)', $obj->sql());
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

        $this->assertEquals('objTable.`xyzzy` '.$operator.' \'Charcoal\'', $obj->sql());
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

        $this->assertEquals('objTable.`xyzzy` '.$operator, $obj->sql());
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

        $this->assertEquals($operator.' objTable.`xyzzy`', $obj->sql());
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

        $this->assertEquals(
            sprintf($expected, 'objTable.`xyzzy`', implode($delimiter, $value)),
            $obj->sql()
        );
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
        $obj = $this->createExpression();

        $obj->setData([
            'property' => 'xyzzy',
            'operator' => $operator,
        ]);

        $this->expectException(UnexpectedValueException::class);
        $obj->sql();
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

        $this->assertEquals('REVERSE(objTable.`xyzzy`) = \'Charcoal\'', $obj->sql());
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

        $expected  = '(';
        $expected .= 'objTable.`xyzzy_en` = \'Charcoal\' OR ';
        $expected .= 'objTable.`xyzzy_fr` = \'Charcoal\' OR ';
        $expected .= 'objTable.`xyzzy_de` = \'Charcoal\' OR ';
        $expected .= 'objTable.`xyzzy_es` = \'Charcoal\'';
        $expected .= ')';
        $this->assertEquals($expected, $obj->sql());
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
            'FIND_IN_SET' => [ 'FIND_IN_SET', ',',     'FIND_IN_SET(\'%2$s\', %1$s)' ],
            'IN'          => [ 'IN',          '\',\'', '%1$s IN (\'%2$s\')' ],
            'NOT IN'      => [ 'NOT IN',      '\',\'', '%1$s NOT IN (\'%2$s\')' ]
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
