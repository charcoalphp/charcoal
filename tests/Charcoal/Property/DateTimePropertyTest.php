<?php

namespace Charcoal\Tests\Property;

use \DateTime;

use \Charcoal\Property\DateTimeProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class DateTimePropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Assert that the `type` method:
     * - returns "date-time"
     */
    public function testType()
    {
        $obj = new DateTimeProperty();
        $this->assertEquals('date-time', $obj->type());
    }

    /**
     * Assert that the `setData` method:
     * - is chainable
     * - sets the data
     */
    public function testSetData()
    {
        $obj = new DateTimeProperty();

        $ret = $obj->setData([
            'min'=>'2015-01-01 00:00:00',
            'max'=>'2025-01-01 00:00:00',
            'format'=>'Y.m.d'
        ]);

        $this->assertSame($ret, $obj);

        $this->assertEquals(new DateTime('2015-01-01 00:00:00'), $obj->min());
        $this->assertEquals(new DateTime('2025-01-01 00:00:00'), $obj->max());
        $this->assertEquals('Y.m.d', $obj->format());
    }

    /**
     * Assert that calling `setVal` with a null parameters:
     * - Is chainable
     * - Set the value to null if "allowNull" is true
     * - Throw an exception if "allowNull" is false
     */
    public function testSetValWithNullValue()
    {
        $obj = new DateTimeProperty();
        $obj->setAllowNull(true);

        $ret = $obj->setVal(null);
        $this->assertSame($ret, $obj);
        $this->assertEquals(null, $obj->val());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setAllowNull(false);
        $obj->setVal(null);
    }

    /**
     * Assert that the `setVal` method:
     * - Is chainable
     * - Sets the value when the parameter is a string or a DateTime object
     * - Throws an exception otherwise
     */
    public function testSetVal()
    {
        $obj = new DateTimeProperty();
        $ret = $obj->setVal('2000-01-01 00:00:00');
        $this->assertSame($ret, $obj);
        $this->assertEquals(new DateTime('2000-01-01 00:00:00'), $obj->val());

        $dt = new DateTime('October 1st, 1984');
        $ret = $obj->setVal($dt);
        $this->assertSame($ret, $obj);
        $this->assertEquals($dt, $obj->val());
    }

    public function testStorageVal()
    {
        $obj = new DateTimeProperty();

        $obj->setVal('October 1st, 1984');
        $this->assertEquals('1984-10-01 00:00:00', $obj->storageVal());

        $obj->setVal(null);

        $obj->setAllowNull(true);
        $this->assertEquals(null, $obj->storageVal());

        $obj->setAllowNull(false);
        $this->setExpectedException('\Exception');
        $obj->storageVal();
    }

    public function testDisplayVal()
    {
        $obj = new DateTimeProperty();
        $this->assertEquals('', $obj->displayVal());

        $obj->setVal('October 1st, 2015 15:00:00');
        $this->assertEquals('2015-10-01 15:00:00', $obj->displayVal());

        $obj->setFormat('Y/m/d');
        $this->assertEquals('2015/09/01', $obj->displayVal('September 1st, 2015'));
    }

    /**
     * Assert that the `setMultiple()` method:
     * - set the multiple to false, if false or falsish value
     * - throws exception otherwise (truthish or invalid value)
     * - is chainable
     */
    public function testSetMultiple()
    {
        $obj = new DateTimeProperty();
        $ret = $obj->setMultiple(0);
        $this->assertSame($ret, $obj);
        $this->assertSame(false, $ret->multiple());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMultiple(1);
    }

    public function testMultiple()
    {
        $obj = new DateTimeProperty();
        $this->assertSame(false, $obj->multiple());
    }

    /**
     * Assert that the `min` method:
     * - is chainable
     * - sets the min value from a string or DateTime object
     * - throws exception when the argument is invalid
     * -
     */
    public function testSetMin()
    {
        $obj = new DateTimeProperty();

        // Setting by string
        $ret = $obj->setMin('2020-01-01 01:02:03');
        $this->assertSame($ret, $obj);
        $this->assertEquals(new DateTime('2020-01-01 01:02:03'), $obj->min());

        // Setting by DateTime
        $dt = new DateTime('today');
        $ret = $obj->setMin($dt);
        $this->assertSame($ret, $obj);
        $this->assertEquals($dt, $obj->min());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMin('foo');

        // Ensure setting a null value works
        $obj->setMin(null);
        $this->assertEquals(null, $obj->min());
    }

    /**
     * Assert that the `max` method:
     * - is chainable
     * - sets the max value
     * - throws exception when the argument is invalid
     */
    public function testSetMax()
    {
        $obj = new DateTimeProperty();

        $ret = $obj->setMax('2020-01-01 01:02:03');
        $this->assertSame($ret, $obj);
        $this->assertEquals(new DateTime('2020-01-01 01:02:03'), $obj->max());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMax('foo');

        // Ensure setting a null value works
        $obj->setMax(null);
        $this->assertEquals(null, $obj->max());
    }

    /**
     * Assert that the `format()` method
     * - is chainable
     * and that the `setFormat()`:
     * - is chainable
     * - sets the format
     * - throws an exception if not a string or null
     */
    public function testSetFormat()
    {
        $obj = new DateTimeProperty();
        $this->assertEquals('Y-m-d H:i:s', $obj->format());

        $ret = $obj->setFormat('Y/m/d');
        $this->assertSame($ret, $obj);
        $this->assertEquals('Y/m/d', $obj->format());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setFormat(null);
    }

    public function testSave()
    {
        $obj = new DateTimeProperty();
        $this->assertEquals(null, $obj->save());

        $obj->setVal('2015-01-01');
        $this->assertEquals(new DateTime('2015-01-01'), $obj->save());
    }

    /**
     * Assert that the `validateMin` method:
     * - Returns true if no "min" is set
     * - Returns true when the value is equal or bigger
     * - Returns false when the value is smaller
     */
    public function testValidateMin()
    {
        $obj = new DateTimeProperty();
        $this->assertTrue($obj->validateMin());

        $obj->setMin('2015-01-01');

        // Equal
        $obj->setVal('2015-01-01');
        $this->assertTrue($obj->validateMin());

        // Bigger
        $obj->setVal('2016-01-01');
        $this->assertTrue($obj->validateMin());

        // Smaller
        $obj->setVal('2014-01-01');
        $this->assertNotTrue($obj->validateMin());
    }

    /**
     * Assert that the `validateMax` method:
     * - Returns true if no "max" is set
     * - Returns true when the value is equal or smaller
     * - Returns false when the value is bigger
     */
    public function testValidateMax()
    {
        $obj = new DateTimeProperty();
        $this->assertTrue($obj->validateMax());

        $obj->setMax('2015-01-01');

        // Equal
        $obj->setVal('2015-01-01');
        $this->assertTrue($obj->validateMax());

        // Smaller
        $obj->setVal('2014-01-01');
        $this->assertTrue($obj->validateMax());

        // Bigger
        $obj->setVal('2016-01-01');
        $this->assertNotTrue($obj->validateMax());
    }

    public function testSqlExtra()
    {
        $obj = new DateTimeProperty();
        $this->assertSame('', $obj->sqlExtra());
    }

    public function testSqlType()
    {
        $obj = new DateTimeProperty();
        $this->assertEquals('DATETIME', $obj->sqlType());
    }

    public function testSqlPdoType()
    {
        $obj = new DateTimeProperty();
        $this->assertEquals(\PDO::PARAM_STR, $obj->sqlPdoType());
    }
}
