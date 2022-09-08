<?php

namespace Charcoal\Tests\Source;

use stdClass;
use DateTime;
use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\AbstractExpression;
use Charcoal\Source\ExpressionInterface;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\CoreContainerIntegrationTrait;
use Charcoal\Tests\Source\ExpressionTestTrait;

/**
 * Test {@see AbstractExpression}.
 */
class AbstractExpressionTest extends AbstractTestCase
{
    use CoreContainerIntegrationTrait;

    /**
     * Create expression for testing.
     *
     * @return AbstractExpression
     */
    final protected function createExpression()
    {
        return $this->getMockForAbstractClass(AbstractExpression::class);
    }

    /**
     * Test the "name" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     *
     * @return void
     */
    public function testName()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertNull($obj->name());

        /** 2. Mutated Value */
        $that = $obj->setName('foo');
        $this->assertEquals('foo', $obj->name());

        /** 3. Chainable */
        $this->assertSame($obj, $that);
    }

    /**
     * Test "name" property with invalid value.
     *
     * @return void
     */
    public function testNameWithInvalidValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->createExpression()->setName(0);
    }

    /**
     * Test the "active" property.
     *
     * Assertions:
     * 1. Default state
     * 2. Mutated state
     * 3. Chainable method
     * 4. Cast value to boolean
     *
     * @return void
     */
    public function testActive()
    {
        $obj = $this->createExpression();

        /** 1. Default Value */
        $this->assertTrue($obj->active());

        /** 2. Mutated Value */
        $that = $obj->setActive(false);
        $this->assertFalse($obj->active());

        /** 3. Chainable */
        $this->assertSame($obj, $that);

        /** 4. Cast value to boolean */
        $obj->setActive(1);
        $this->assertTrue($obj->active());

        $obj->setActive(0);
        $this->assertFalse($obj->active());
    }

    /**
     * Test value parsing.
     *
     * @dataProvider provideParsableValues
     *
     * @param  mixed $value    The value to test.
     * @param  mixed $expected The expected result.
     * @return void
     */
    public function testParseValue($value, $expected)
    {
        $obj = $this->createExpression();

        $this->assertEquals($expected, $obj::parseValue($value));
    }

    /**
     * Provide data for value parsing.
     *
     * @used-by self::testParseValue()
     * @return  array
     */
    public function provideParsableValues()
    {
        $container = $this->getContainer();

        $prop = $container['property/factory']->create('date-time');
        $prop->setVal('13 July 2004');
        $time = new DateTime('8 June 1995');

        return [
            'Null Type'              => [ null, null ],
            'Integer Type'           => [ 42, 42 ],
            'Integer String'         => [ '3', '3' ],
            'Boolean Type'           => [ true, true ],
            'Boolean String (TRUE)'  => [ 'true', true ],
            'Boolean String (FALSE)' => [ 'false', false ],
            'Date/Time Object'       => [ $time, '1995-06-08 00:00:00' ],
            'Date/Time Property'     => [ $prop, '2004-07-13 00:00:00' ],
        ];
    }

    /**
     * Test value quoting.
     *
     * @dataProvider provideQuotableValues
     *
     * @param mixed $value    The value to test.
     * @param  mixed $expected The expected result.
     * @return void
     */
    public function testQuoteValue($value, $expected)
    {
        $obj = $this->createExpression();

        $this->assertEquals($expected, $obj::quoteValue($value));
    }

    /**
     * Provide data for value quoting.
     *
     * @used-by self::testQuoteValue()
     * @return  array
     */
    public function provideQuotableValues()
    {
        $obj = new stdClass();

        return [
            'Null Type'       => [ null, null ],
            'Array Type'      => [ [ 42 ], [ 42 ] ],
            'Integer Type'    => [ 42, 42 ],
            'Integer String'  => [ '3', '3' ],
            'Quotable String' => [ 'Foo "Qux" Baz', '"Foo &quot;Qux&quot; Baz"' ],
            'Boolean Type'    => [ true, 1 ],
            'Boolean String'  => [ 'false', 0 ],
            'Object Type'     => [ $obj, $obj ],
        ];
    }

    /**
     * Test field quoting.
     *
     * @dataProvider provideQuotableIdentifiers
     *
     * @param  mixed $fieldName The field name.
     * @param  mixed $tableName The table name.
     * @param  mixed $expected  The expected identifier.
     * @return void
     */
    public function testQuoteIdentifier($fieldName, $tableName, $expected)
    {
        $obj = $this->createExpression();

        $this->assertEquals($expected, $obj::quoteIdentifier($fieldName, $tableName, $expected));
    }

    /**
     * Provide data for field quoting.
     *
     * @used-by self::testQuoteIdentifier()
     * @return  array
     */
    public function provideQuotableIdentifiers()
    {
        return [
            [ null,   null,   ''          ],
            [ '',     null,   ''          ],
            [ '*',    null,   '*'         ],
            [ 'col',  null,   '`col`'     ],
            [ '*',    'tbl',  'tbl.*'     ],
            [ 'col',  'tbl',  'tbl.`col`' ],
        ];
    }

    /**
     * Test field quoting with invalid field name.
     *
     * @return void
     */
    public function testQuoteIdentifierWithInvalidFieldName()
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = $this->createExpression();
        $obj::quoteIdentifier([]);
    }

    /**
     * Test field quoting with blank table name.
     *
     * @return void
     */
    public function testQuoteIdentifierWithBlankTableName()
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = $this->createExpression();
        $obj::quoteIdentifier('foo', '');
    }

    /**
     * Test field quoting with invalid table name.
     *
     * @return void
     */
    public function testQuoteIdentifierWithInvalidTableName()
    {
        $this->expectException(InvalidArgumentException::class);
        $obj = $this->createExpression();
        $obj::quoteIdentifier('foo', []);
    }

    /**
     * Test value differentiation.
     *
     * @dataProvider provideDiffValues
     *
     * @param  mixed $a        The custom value.
     * @param  mixed $b        The default value.
     * @param  mixed $expected The expected result.
     * @return void
     */
    public function testDiffValues($a, $b, $expected)
    {
        $obj = $this->createExpression();

        $this->assertEquals($expected, $obj::diffValues($a, $b));
    }

    /**
     * Provide data for value differentiation.
     *
     * @used-by self::testDiffValues()
     * @return  array
     */
    public function provideDiffValues()
    {
        return [
            'Same Type'      => [ 5, 5, 0 ],
            'Different Type' => [ 5, '5', 1 ],
        ];
    }

    /**
     * Test callable detection.
     *
     * @dataProvider provideCallableValues
     *
     * @param  mixed $value    The value to test.
     * @param  mixed $expected The expected result.
     * @return void
     */
    public function testIsCallable($value, $expected)
    {
        $obj = $this->createExpression();

        $this->assertEquals($expected, $obj::isCallable($value));
    }

    /**
     * Provide data for callable detection.
     *
     * @used-by self::testIsCallable()
     * @return  array
     */
    public function provideCallableValues()
    {
        return [
            'Null Type'   => [ null, false ],
            'String Type' => [ 'strval', false ],
            'Closure'     => [ function () {
            }, true ],
        ];
    }
}
