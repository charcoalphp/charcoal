<?php

namespace Charcoal\Tests\Source\Database;

use stdClass;
use UnexpectedValueException;

// From 'charcoal-core'
use Charcoal\Source\DatabaseSource;
use Charcoal\Source\Database\DatabaseOrder;

use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Source\DatabaseExpressionTestTrait;

/**
 * Test {@see DatabaseOrder}.
 */
class DatabaseOrderTest extends AbstractTestCase
{
    use DatabaseExpressionTestTrait;
    use ReflectionsTrait;

    /**
     * Create expression for testing.
     */
    final protected function createExpression(): \Charcoal\Source\Database\DatabaseOrder
    {
        return new DatabaseOrder();
    }

    /**
     * Test default table name for default data values.
     *
     * @see \Charcoal\Tests\Source\Database\DatabaseFilterTest::testDefaultValues()
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
        $obj->setMode('asc')->setProperty('foo');

        $obj->setActive(true);
        $this->assertEquals('objTable.`foo` ASC', $obj->sql());

        $obj->setActive(false);
        $this->assertEquals('', $obj->sql());
    }

    /**
     * Test SQL without a mode.
     */
    public function testBlankSql(): void
    {
        $obj = $this->createExpression();

        $obj->setMode(null);
        $this->assertEquals('', $obj->sql());
    }

    /**
     * Test SQL with custom mode and placeholders.
     */
    public function testSqlCustomMode(): void
    {
        $obj = $this->createExpression();

        $obj->setMode('custom')->setCondition('qux ASC');
        $this->assertEquals('qux ASC', $obj->sql());
    }

    /**
     * Test that "custom" and "values" mode have precedence over other features
     * when the mode is undefined.
     */
    public function testSqlModeResolutionAndPrecedence(): void
    {
        $obj = $this->createExpression();

        $obj->setMode(null)->setProperty('country');

        /** Resolves to "values" mode when values are defined. */
        $obj->setValues([ 'FR', 'UK', 'CA' ]);
        $this->assertEquals('FIELD(objTable.`country`, "FR","UK","CA")', $obj->sql());

        /** Resolves to "custom" mode, and takes precedence, when a custom expression is defined. */
        $obj->setCondition('foo DESC');
        $this->assertEquals('foo DESC', $obj->sql());
    }

    /**
     * Test SQL with random mode.
     */
    public function testSqlRandomMode(): void
    {
        $obj = $this->createExpression();

        $obj->setMode('rand');
        $this->assertEquals('RAND()', $obj->sql());
    }

    /**
     * Test SQL with direction mode.
     *
     *
     * @param  mixed $mode     The directional mode to set.
     * @param  mixed $expected The expected SQL direction.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideSqlDirectionMode')]
    public function testSqlDirectionMode(string $mode, string $expected): void
    {
        $obj = $this->createExpression();

        $obj->setMode($mode)->setProperty('test');
        $this->assertEquals(
            sprintf('objTable.`test` %s', $expected),
            $obj->sql()
        );
    }

    /**
     * Provide data for selecting directional ordering.
     *
     * @used-by self::testSqlDirectionMode()
     */
    public static function provideSqlDirectionMode(): array
    {
        return [
            [ 'asc',  'ASC'  ],
            [ 'desc', 'DESC' ],
        ];
    }

    /**
     * Test direction mode without property.
     */
    public function testSqlDirectionModeWithoutProperty(): void
    {
        $obj = $this->createExpression();

        $obj->setMode('asc');
        $this->assertEquals('', $obj->sql());
    }

    /**
     * Test SQL with values mode.
     */
    public function testSqlValuesMode(): void
    {
        $obj = $this->createExpression();
        $obj->setMode('values')
            ->setProperty('test')
            ->setValues([ 1, false, 'foo' ]);

        $this->assertEquals('FIELD(objTable.`test`, 1,0,"foo")', $obj->sql());
    }

    /**
     * Test values mode without property.
     */
    public function testSqlValuesModeWithoutProperty(): void
    {
        $obj = $this->createExpression();

        $this->expectException(UnexpectedValueException::class);

        $obj->setMode('values')->setValues('1,2,3');
        $obj->sql();
    }

    /**
     * Test values mode without values.
     */
    public function testSqlValuesModeWithoutValues(): void
    {
        $obj = $this->createExpression();

        $this->expectException(UnexpectedValueException::class);

        $obj->setMode('values')->setProperty('test');
        $obj->sql();
    }

    /**
     * Test invalid custom SQL.
     */
    public function testSqlCustomModeWithoutQuery(): void
    {
        $obj = $this->createExpression();

        $this->expectException(UnexpectedValueException::class);

        $method = $this->getMethod($obj, 'byCondition');
        $method->invoke($obj);
    }

    /**
     * Test invalid property SQL.
     */
    public function testSqlWithoutModeWithoutProperty(): void
    {
        $obj = $this->createExpression();

        $this->expectException(UnexpectedValueException::class);

        $method = $this->getMethod($obj, 'byProperty');
        $method->invoke($obj);
    }

    /**
     * Test helper methods.
     */
    public function testPrepareValues(): void
    {
        $obj = $this->createExpression();

        $arr = $obj->prepareValues([]);
        $this->assertEquals([], $arr);

        $arr = $obj->prepareValues(42);
        $this->assertEquals([ 42 ], $arr);

        $arr = $obj->prepareValues([
            1, '19', 'false', 'Foo "Qux" Baz', [ 42 ], new stdClass()
        ]);
        $this->assertEquals([ 1, '19', false, '"Foo &quot;Qux&quot; Baz"' ], $arr);
    }
}
