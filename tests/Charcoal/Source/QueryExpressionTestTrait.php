<?php

namespace Charcoal\Tests\Source;

use DateTime;
use InvalidArgumentException;

// From 'charcoal-core'
use Charcoal\Source\AbstractExpression;
use Charcoal\Source\ExpressionInterface;

/**
 *
 */
trait QueryExpressionTestTrait
{
    /**
     * @return \Pimple\Container
     */
    abstract protected function getContainer();

    /**
     * Create expression for testing.
     *
     * @return ExpressionInterface
     */
    abstract protected function createExpression();

    /**
     * Provide data for value parsing.
     *
     * @example [ [ 'active', true ] ]
     * @used-by self::testDefaultValues()
     * @return  array
     */
    abstract public function provideDefaultValues();

    /**
     * Test new instance.
     *
     * Assertions:
     * 1. Implements {@see ExpressionInterface}
     */
    public function testContruct()
    {
        $obj = $this->createExpression();

        /** 1. Implementation */
        $this->assertInstanceOf(ExpressionInterface::class, $obj);
    }

    /**
     * Test value parsing.
     *
     * @dataProvider provideParsableValues
     *
     * @param mixed $val      The value to test.
     * @param mixed $expected The expected result.
     */
    public function testParseValue($val, $expected)
    {
        $obj = $this->createExpression();

        $this->assertEquals($expected, $obj::parseValue($val));
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
        $prop  = $container['property/factory']->create('date-time');
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
     * Test field quoting.
     *
     * @dataProvider provideQuotableIdentifiers
     *
     * @param mixed $fieldName The field name.
     * @param mixed $tableName The table name.
     * @param mixed $expected  The expected identifier.
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
     */
    public function testQuoteIdentifierWithInvalidFieldName()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $obj = $this->createExpression();
        $obj::quoteIdentifier([]);
    }

    /**
     * Test field quoting with blank table name.
     */
    public function testQuoteIdentifierWithBlankTableName()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $obj = $this->createExpression();
        $obj::quoteIdentifier('foo', '');
    }

    /**
     * Test field quoting with invalid table name.
     */
    public function testQuoteIdentifierWithInvalidTableName()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        $obj = $this->createExpression();
        $obj::quoteIdentifier('foo', []);
    }

    /**
     * Test method signature for default data values.
     *
     * Assertions:
     * 1. Returns an array
     */
    public function testDefaultValuesMethod()
    {
        $obj = $this->createExpression();

        /** 1. Returns an array */
        $this->assertInternalType('array', $obj->defaultData());
    }

    /**
     * Test default data values.
     *
     * @dataProvider provideDefaultValues
     *
     * @param mixed $key      The data key test.
     * @param mixed $expected The expected data value.
     */
    public function testDefaultValues($key, $expected)
    {
        $obj  = $this->createExpression();
        $data = $obj->defaultData();

        $this->assertArrayHasKey($key, $data);
        $this->assertEquals($expected, $data[$key]);
    }

    /**
     * Test method signature for data stucture.
     *
     * Assertions:
     * 1. Returns an array
     * 2. Chainable method
     */
    public function testDataMethod()
    {
        $obj = $this->createExpression();

        /** 1. Returns an array */
        $this->assertInternalType('array', $obj->data());

        /** 2. Chainable method */
        $that = $obj->setData([]);
        $this->assertSame($obj, $that);
    }

    /**
     * Test data structure with default state.
     */
    public function testDefaultData()
    {
        $obj = $this->createExpression();
        $this->assertEquals([], $obj->data());
    }

    /**
     * Assert the given expression has data from {@see AbstractExpression}.
     *
     * @param ExpressionInterface $obj      The expression to test.
     * @param array|null          $expected The expected data subset.
     */
    public function assertStructHasBasicData(ExpressionInterface $obj, array $expected = null)
    {
        if (empty($expected)) {
            $expected = [
                'active' => false,
                'string' => '1 = 1',
            ];
            $obj->setData($mutation);
        }

        $data = $obj->data();

        $this->assertArrayHasKey('active', $data);
        $this->assertEquals($expected['active'], $data['active']);
        $this->assertEquals($expected['active'], $obj->active());

        $this->assertArrayHasKey('string', $data);
        $this->assertEquals($expected['string'], $data['string']);
        $this->assertEquals($expected['string'], $obj->string());
    }
}
