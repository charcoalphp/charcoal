<?php

namespace Charcoal\Tests\Property;

use \DateTime;

use \PDO;

use \Psr\Log\NullLogger;

use \Charcoal\Property\DateTimeProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class DateTimePropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DateTimeProperty $obj
     */
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        $this->obj = new DateTimeProperty([
            'database'  => new PDO('sqlite::memory:'),
            'logger'    => new NullLogger(),
            'translator' => $GLOBALS['translator']
        ]);
    }

    /**
     * Assert that the `type` method:
     * - returns "date-time"
     */
    public function testType()
    {
        $this->assertEquals('date-time', $this->obj->type());
    }

    /**
     * Assert that the `setData` method:
     * - is chainable
     * - sets the data
     */
    public function testSetData()
    {
        $ret = $this->obj->setData([
            'min'=>'2015-01-01 00:00:00',
            'max'=>'2025-01-01 00:00:00',
            'format'=>'Y.m.d'
        ]);

        $this->assertSame($ret, $this->obj);

        $this->assertEquals(new DateTime('2015-01-01 00:00:00'), $this->obj->min());
        $this->assertEquals(new DateTime('2025-01-01 00:00:00'), $this->obj->max());
        $this->assertEquals('Y.m.d', $this->obj->format());
    }

    /**
     * Assert that calling `setVal` with a null parameters:
     * - Is chainable
     * - Set the value to null if "allowNull" is true
     * - Throw an exception if "allowNull" is false
     */
    public function testSetValWithNullValue()
    {
        $this->obj->setAllowNull(true);

        $ret = $this->obj->setVal(null);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(null, $this->obj->val());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setAllowNull(false);
        $this->obj->setVal(null);
    }

    /**
     * Assert that the `setVal` method:
     * - Is chainable
     * - Sets the value when the parameter is a string or a DateTime object
     * - Throws an exception otherwise
     */
    public function testSetVal()
    {
        $ret = $this->obj->setVal('2000-01-01 00:00:00');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(new DateTime('2000-01-01 00:00:00'), $this->obj->val());

        $dt = new DateTime('October 1st, 1984');
        $ret = $this->obj->setVal($dt);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($dt, $this->obj->val());
    }

    public function testStorageVal()
    {
        $this->assertEquals('1984-10-01 00:00:00', $this->obj->storageVal('October 1st, 1984'));
    }

    public function testDisplayVal()
    {
        $this->assertEquals('2015-10-01 15:00:00', $this->obj->displayVal('October 1st, 2015 15:00:00'));

        $this->obj->setFormat('Y/m/d');
        $this->assertEquals('2015/09/01', $this->obj->displayVal('September 1st, 2015'));
    }

    /**
     * Assert that the `setMultiple()` method:
     * - set the multiple to false, if false or falsish value
     * - throws exception otherwise (truthish or invalid value)
     * - is chainable
     */
    public function testSetMultiple()
    {
        $ret = $this->obj->setMultiple(0);
        $this->assertSame($ret, $this->obj);
        $this->assertSame(false, $ret->multiple());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setMultiple(1);
    }

    public function testMultiple()
    {
        $this->assertSame(false, $this->obj->multiple());
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
        // Setting by string
        $ret = $this->obj->setMin('2020-01-01 01:02:03');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(new DateTime('2020-01-01 01:02:03'), $this->obj->min());

        // Setting by DateTime
        $dt = new DateTime('today');
        $ret = $this->obj->setMin($dt);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($dt, $this->obj->min());

        $this->obj['min'] = 'today';
        $this->assertEquals($dt, $this->obj->min());

        $this->obj->set('min', 'today');
        $this->assertEquals($dt, $this->obj['min']);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setMin('foo');

        // Ensure setting a null value works
        $this->obj->setMin(null);
        $this->assertEquals(null, $this->obj->min());
    }

    /**
     * Assert that the `max` method:
     * - is chainable
     * - sets the max value
     * - throws exception when the argument is invalid
     */
    public function testSetMax()
    {
        $ret = $this->obj->setMax('2020-01-01 01:02:03');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(new DateTime('2020-01-01 01:02:03'), $this->obj->max());

        // Setting by DateTime
        $dt = new DateTime('today');
        $ret = $this->obj->setMax($dt);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($dt, $this->obj->max());

        $this->obj['max'] = 'today';
        $this->assertEquals($dt, $this->obj->max());

        $this->obj->set('max', 'today');
        $this->assertEquals($dt, $this->obj['max']);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setMax('foo');

        // Ensure setting a null value works
        $this->obj->setMax(null);
        $this->assertEquals(null, $this->obj->max());
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
        $this->assertEquals('Y-m-d H:i:s', $this->obj->format());

        $ret = $this->obj->setFormat('Y/m/d');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Y/m/d', $this->obj->format());

        $this->obj['format'] = 'd-m-Y';
        $this->assertEquals('d-m-Y', $this->obj->format());

        $this->obj->set('format', 'Y');
        $this->assertEquals('Y', $this->obj['format']);

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setFormat(null);
    }

    public function testSave()
    {
        $this->assertEquals(null, $this->obj->save(null));

        $this->assertEquals(new DateTime('2015-01-01'), $this->obj->save('2015-01-01'));
    }

    /**
     * Assert that the `validateMin` method:
     * - Returns true if no "min" is set
     * - Returns true when the value is equal or bigger
     * - Returns false when the value is smaller
     */
    public function testValidateMin()
    {
        $this->assertTrue($this->obj->validateMin());

        $this->obj->setMin('2015-01-01');

        // Equal
        $this->obj->setVal('2015-01-01');
        $this->assertTrue($this->obj->validateMin());

        // Bigger
        $this->obj->setVal('2016-01-01');
        $this->assertTrue($this->obj->validateMin());

        // Smaller
        $this->obj->setVal('2014-01-01');
        $this->assertNotTrue($this->obj->validateMin());
    }

    /**
     * Assert that the `validateMax` method:
     * - Returns true if no "max" is set
     * - Returns true when the value is equal or smaller
     * - Returns false when the value is bigger
     */
    public function testValidateMax()
    {
        $this->assertTrue($this->obj->validateMax());

        $this->obj->setMax('2015-01-01');

        // Equal
        $this->obj->setVal('2015-01-01');
        $this->assertTrue($this->obj->validateMax());

        // Smaller
        $this->obj->setVal('2014-01-01');
        $this->assertTrue($this->obj->validateMax());

        // Bigger
        $this->obj->setVal('2016-01-01');
        $this->assertNotTrue($this->obj->validateMax());
    }

    public function testSqlExtra()
    {
        $this->assertSame('', $this->obj->sqlExtra());
    }

    public function testSqlType()
    {
        $this->assertEquals('DATETIME', $this->obj->sqlType());
    }

    public function testSqlPdoType()
    {
        $this->assertEquals(\PDO::PARAM_STR, $this->obj->sqlPdoType());
    }
}
