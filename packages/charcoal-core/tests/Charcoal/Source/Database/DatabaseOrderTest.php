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
     *
     * @return DatabaseOrder
     */
    final protected function createExpression()
    {
        return new DatabaseOrder();
    }

    /**
     * Test default table name for default data values.
     *
     * @see \Charcoal\Tests\Source\Database\DatabaseFilterTest::testDefaultValues()
     *
     * @return void
     */
    public function testDefaultValues()
    {
        $obj = $this->createExpression();

        $data = $obj->defaultData();
        $this->assertArrayHasKey('table', $data);
        $this->assertEquals(DatabaseSource::DEFAULT_TABLE_ALIAS, $data['table']);
        $this->assertEquals(DatabaseSource::DEFAULT_TABLE_ALIAS, $obj->table());
    }

    /**
     * Test influence of "active" property on SQL compilation.
     *
     * @return void
     */
    public function testInactiveExpression()
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
     *
     * @return void
     */
    public function testBlankSql()
    {
        $obj = $this->createExpression();

        $obj->setMode(null);
        $this->assertEquals('', $obj->sql());
    }

    /**
     * Test SQL with custom mode and placeholders.
     *
     * @return void
     */
    public function testSqlCustomMode()
    {
        $obj = $this->createExpression();

        $obj->setMode('custom')->setCondition('qux ASC');
        $this->assertEquals('qux ASC', $obj->sql());
    }

    /**
     * Test that "custom" and "values" mode have precedence over other features
     * when the mode is undefined.
     *
     * @return void
     */
    public function testSqlModeResolutionAndPrecedence()
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
     *
     * @return void
     */
    public function testSqlRandomMode()
    {
        $obj = $this->createExpression();

        $obj->setMode('rand');
        $this->assertEquals('RAND()', $obj->sql());
    }

    /**
     * Test SQL with direction mode.
     *
     * @dataProvider provideSqlDirectionMode
     *
     * @param  mixed $mode     The directional mode to set.
     * @param  mixed $expected The expected SQL direction.
     * @return void
     */
    public function testSqlDirectionMode($mode, $expected)
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
     * @return  array
     */
    public function provideSqlDirectionMode()
    {
        return [
            [ 'asc',  'ASC'  ],
            [ 'desc', 'DESC' ],
        ];
    }

    /**
     * Test direction mode without property.
     *
     * @return void
     */
    public function testSqlDirectionModeWithoutProperty()
    {
        $obj = $this->createExpression();

        $obj->setMode('asc');
        $this->assertEquals('', $obj->sql());
    }

    /**
     * Test SQL with values mode.
     *
     * @return void
     */
    public function testSqlValuesMode()
    {
        $obj = $this->createExpression();
        $obj->setMode('values')
            ->setProperty('test')
            ->setValues([ 1, false, 'foo' ]);

        $this->assertEquals('FIELD(objTable.`test`, 1,0,"foo")', $obj->sql());
    }

    /**
     * Test values mode without property.
     *
     * @return void
     */
    public function testSqlValuesModeWithoutProperty()
    {
        $obj = $this->createExpression();

        $this->expectException(UnexpectedValueException::class);

        $obj->setMode('values')->setValues('1,2,3');
        $obj->sql();
    }

    /**
     * Test values mode without values.
     *
     * @return void
     */
    public function testSqlValuesModeWithoutValues()
    {
        $obj = $this->createExpression();

        $this->expectException(UnexpectedValueException::class);

        $obj->setMode('values')->setProperty('test');
        $obj->sql();
    }

    /**
     * Test invalid custom SQL.
     *
     * @return void
     */
    public function testSqlCustomModeWithoutQuery()
    {
        $obj = $this->createExpression();

        $this->expectException(UnexpectedValueException::class);

        $method = $this->getMethod($obj, 'byCondition');
        $method->invoke($obj);
    }

    /**
     * Test invalid property SQL.
     *
     * @return void
     */
    public function testSqlWithoutModeWithoutProperty()
    {
        $obj = $this->createExpression();

        $this->expectException(UnexpectedValueException::class);

        $method = $this->getMethod($obj, 'byProperty');
        $method->invoke($obj);
    }

    /**
     * Test helper methods.
     *
     * @return void
     */
    public function testPrepareValues()
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
