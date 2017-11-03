<?php

namespace Charcoal\Tests\Source\Database;

use UnexpectedValueException;

// From 'charcoal-property'
use Charcoal\Property\GenericProperty;
use Charcoal\Property\PropertyInterface;

// From 'charcoal-core'
use Charcoal\Source\DatabaseSource;
use Charcoal\Source\Database\DatabaseFilter;

use Charcoal\Tests\ContainerIntegrationTrait;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Source\DatabaseExpressionTestTrait;

/**
 * Test {@see DatabaseFilter}.
 */
class DatabaseFilterTest extends \PHPUnit_Framework_TestCase
{
    use ContainerIntegrationTrait;
    use DatabaseExpressionTestTrait;
    use ReflectionsTrait;

    /**
     * Create expression for testing.
     *
     * @return DatabaseFilter
     */
    final protected function createExpression()
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
     */
    public function testInactiveExpression()
    {
        $obj = $this->createExpression();
        $obj->setProperty('foo')->setValue('Charcoal');

        $obj->setActive(true);
        $this->assertEquals('(objTable.`foo` = \'Charcoal\')', $obj->sql());

        $obj->setActive(false);
        $this->assertEquals('', $obj->sql());
    }

    /**
     * Test SQL without conditions.
     */
    public function testBlankSql()
    {
        $obj = $this->createExpression();

        $this->assertEquals('', $obj->sql());
    }

    /**
     * Test invalid SQL predicate.
     */
    public function testSqlWithoutPredicate()
    {
        $obj = $this->createExpression();

        $this->setExpectedException(UnexpectedValueException::class);

        $method = $this->getMethod($obj, 'byPredicate');
        $method->invoke($obj);
    }

    /**
     * Test "condition" property with and without placeholders.
     */
    public function testCustomSql()
    {
        $obj = $this->createExpression();

        $obj->setCondition('objTable.foo = objTable.baz');
        $this->assertEquals('objTable.foo = objTable.baz', $obj->sql());
    }

    /**
     * Test "condition" property has precedence over other features.
     */
    public function testCustomSqlPrecedence()
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
    public function testCustomSqlWithoutQuery()
    {
        $obj = $this->createExpression();

        $this->setExpectedException(UnexpectedValueException::class);

        $method = $this->getMethod($obj, 'byCondition');
        $method->invoke($obj);
    }

    /**
     * Test condition compilation.
     */
    public function testCompileConditions()
    {
        $obj = $this->createExpression();

        $method = $this->getMethod($obj, 'compileConditions');
        $result = $method->invoke($obj, []);
        $this->assertEquals('()', $result);
    }

    /**
     * Test basic SQL operator without a value.
     */
    public function testSqlOperatorWithoutValue()
    {
        $obj = $this->createExpression();

        $obj->setData([
            'property' => 'xyzzy',
            'operator' => '=',
        ]);

        $this->setExpectedException(UnexpectedValueException::class);
        $obj->sql();
    }

    /**
     * Test comparison SQL operators.
     *
     * @dataProvider provideComparisonOperators
     *
     * @param string $operator A SQL operator.
     */
    public function testSqlComparisonOperators($operator)
    {
        $obj = $this->createExpression();
        $obj->setData([
            'property' => 'xyzzy',
            'operator' => $operator,
            'value'    => 'Charcoal',
        ]);

        $this->assertEquals('(objTable.`xyzzy` '.$operator.' \'Charcoal\')', $obj->sql());
    }

    /**
     * Test condition-style SQL operators ("value" is ignored).
     *
     * @dataProvider provideConditionalOperators
     *
     * @param string $operator A SQL operator.
     */
    public function testSqlConditionalOperators($operator)
    {
        $obj = $this->createExpression();
        $obj->setData([
            'property' => 'xyzzy',
            'operator' => $operator,
            'value'    => 'Charcoal',
        ]);

        $this->assertEquals('(objTable.`xyzzy` '.$operator.')', $obj->sql());
    }

    /**
     * Test list-based SQL operators.
     *
     * @dataProvider provideSetOperators
     *
     * @param string $operator  A SQL operator.
     * @param string $delimiter The set's delimiter.
     * @param string $expected  The expected result.
     */
    public function testSqlSetOperators($operator, $delimiter, $expected)
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
     * @dataProvider provideSetOperators
     *
     * @param string $operator  A SQL operator.
     * @param string $delimiter The set's delimiter.
     * @param string $expected  Unused; The expected result.
     */
    public function testSqlSetOperatorsWithoutValue($operator, $delimiter, $expected)
    {
        $obj = $this->createExpression();

        $obj->setData([
            'property' => 'xyzzy',
            'operator' => $operator,
        ]);

        $this->setExpectedException(UnexpectedValueException::class);
        $obj->sql();
    }

    /**
     * Test SQL function.
     */
    public function testSqlFunction()
    {
        $obj = $this->createExpression();
        $obj->setData([
            'property' => 'xyzzy',
            'operator' => '=',
            'value'    => 'Charcoal',
            'function' => 'reverse',
        ]);

        $this->assertEquals('(REVERSE(objTable.`xyzzy`) = \'Charcoal\')', $obj->sql());
    }

    /**
     * Test SQL condition with multiple field names.
     */
    public function testSqlFields()
    {
        $container = $this->getContainer();

        $this->getContainerProvider()->registerMultilingualTranslator($container);

        $prop = $this->createProperty();
        $prop->setL10n(true);

        $obj = $this->createExpression();
        $obj->setProperty($prop)->setOperator('=')->setValue('Charcoal');

        $expected  = '(';
        $expected .= '(objTable.`xyzzy_en` = \'Charcoal\') OR ';
        $expected .= '(objTable.`xyzzy_fr` = \'Charcoal\') OR ';
        $expected .= '(objTable.`xyzzy_de` = \'Charcoal\') OR ';
        $expected .= '(objTable.`xyzzy_es` = \'Charcoal\')';
        $expected .= ')';
        $this->assertEquals($expected, $obj->sql());
    }

    /**
     * Provide data for simple operators.
     *
     * @used-by self::testSqlComparisonOperators()
     * @return  array
     */
    public function provideComparisonOperators()
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
     * @return  array
     */
    public function provideSetOperators()
    {
        return [
            'FIND_IN_SET' => [ 'FIND_IN_SET', ',',     'FIND_IN_SET(\'%2$s\', %1$s)' ],
            'IN'          => [ 'IN',          '\',\'', '(%1$s IN (\'%2$s\'))' ],
            'NOT IN'      => [ 'NOT IN',      '\',\'', '(%1$s NOT IN (\'%2$s\'))' ]
        ];
    }

    /**
     * Provide data for NULL-style operators.
     *
     * @used-by self::testSqlConditionalOperators()
     * @return  array
     */
    public function provideConditionalOperators()
    {
        return [
            [ 'IS NULL' ], [ 'IS NOT NULL' ],
            [ 'IS TRUE' ], [ 'IS NOT TRUE' ],
            [ 'IS FALSE' ], [ 'IS NOT FALSE' ],
            [ 'IS UNKNOWN' ], [ 'IS NOT UNKNOWN' ],
        ];
    }
}
