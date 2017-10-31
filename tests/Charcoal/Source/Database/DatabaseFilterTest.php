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
use Charcoal\Tests\Source\DatabaseExpressionTestTrait;

/**
 *
 */
class DatabaseFilterTest extends \PHPUnit_Framework_TestCase
{
    use ContainerIntegrationTrait;
    use DatabaseExpressionTestTrait;

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




    // -------------------------------------------------------------------------




    /**
     * Test basic SQL operators.
     *
     * @dataProvider provideBasicOperators
     *
     * @param string $operator A SQL operator.
     */
    public function testSqlBasicOperators($operator)
    {
        $obj = $this->createExpression();
        $obj->setProperty('xyzzy')->setOperator($operator)->setValue('bar');

        $this->assertEquals('(objTable.`xyzzy` '.$operator.' \'bar\')', $obj->sql());
    }

    /**
     * Test NULL-style SQL operators.
     *
     * @dataProvider provideNullOperators
     *
     * @param string $operator A SQL operator.
     */
    public function testSqlNullOperators($operator)
    {
        $obj = $this->createExpression();
        $obj->setProperty('xyzzy')->setOperator($operator)->setValue('bar');

        $this->assertEquals('(objTable.`xyzzy` '.$operator.')', $obj->sql());
    }

    /**
     * Test advanced SQL operators.
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
        $obj->setProperty('xyzzy')->setOperator($operator)->setValue($value);

        $this->assertEquals(
            sprintf($expected, 'objTable.`xyzzy`', implode($delimiter, $value)),
            $obj->sql()
        );
    }

    /**
     * Test SQL function.
     */
    public function testSqlFunction()
    {
        $obj = $this->createExpression();
        $obj->setProperty('xyzzy')->setOperator('=')->setValue('bar')->setFunc('abs');

        $this->assertEquals('(ABS(objTable.`xyzzy`) = \'bar\')', $obj->sql());
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
        $obj->setProperty($prop)->setOperator('=')->setValue('bar');

        $this->assertEquals(
            '((objTable.`xyzzy_en` = \'bar\') OR (objTable.`xyzzy_fr` = \'bar\') OR '.
             '(objTable.`xyzzy_de` = \'bar\') OR (objTable.`xyzzy_es` = \'bar\'))',
            $obj->sql()
        );
    }

    /**
     * Provide data for simple operators.
     *
     * @used-by self::testSqlBasicOperators()
     * @return  array
     */
    public function provideBasicOperators()
    {
        return [
            [ '=' ], [ '!=' ],
            [ '>' ], [ '>=' ], [ '<' ], [ '<=' ],
            [ 'IS' ], [ 'IS NOT' ],
            [ 'LIKE' ], [ 'NOT LIKE' ]
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
     * @used-by self::testSqlNullOperators()
     * @return  array
     */
    public function provideNullOperators()
    {
        return [
            [ 'IS NULL' ],
            [ 'IS NOT NULL' ]
        ];
    }
}
