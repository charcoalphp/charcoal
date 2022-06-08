<?php

namespace Charcoal\Tests\Property;

use PDO;
use DateTime;
use Exception;
use InvalidArgumentException;

// From 'charcoal-property'
use Charcoal\Property\DateTimeProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 */
class DateTimePropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var DateTimeProperty
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new DateTimeProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    /**
     * Assert that the `type` method:
     * - returns "date-time"
     *
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('date-time', $this->obj->type());
    }

    /**
     * Assert that the `setData` method:
     * - is chainable
     * - sets the data
     *
     * @return void
     */
    public function testSetData()
    {
        $ret = $this->obj->setData([
            'min'    => '2015-01-01 00:00:00',
            'max'    => '2025-01-01 00:00:00',
            'format' => 'Y.m.d'
        ]);

        $this->assertSame($ret, $this->obj);

        $expected = new DateTime('2015-01-01 00:00:00');
        $this->assertEquals($expected, $this->obj['min']);

        $expected = new DateTime('2025-01-01 00:00:00');
        $this->assertEquals($expected, $this->obj['max']);
        $this->assertEquals('Y.m.d', $this->obj['format']);
    }

    /**
     * Assert that calling `setVal` with a null parameters:
     * - Is chainable
     * - Set the value to null if "allowNull" is true
     * - Throw an exception if "allowNull" is false
     *
     * @return void
     */
    public function testSetValWithNullValue()
    {
        $this->obj->setAllowNull(true);

        $ret = $this->obj->setVal(null);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(null, $this->obj->val());

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setAllowNull(false);
        $this->obj->setVal(null);
    }

    /**
     * Assert that the `setVal` method:
     * - Is chainable
     * - Sets the value when the parameter is a string or a DateTime object
     * - Throws an exception otherwise
     *
     * @return void
     */
    public function testSetVal()
    {
        $ret = $this->obj->setVal('2000-01-01 00:00:00');
        $this->assertSame($ret, $this->obj);

        $expected = new DateTime('2000-01-01 00:00:00');
        $this->assertEquals($expected, $this->obj->val());

        $dt = new DateTime('October 1st, 1984');
        $ret = $this->obj->setVal($dt);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($dt, $this->obj->val());
    }

    /**
     * @return void
     */
    public function testStorageVal()
    {
        $this->assertEquals('1984-10-01 00:00:00', $this->obj->storageVal('October 1st, 1984'));

//        $this->obj->setAllowNull(true);
//        $ret = $this->obj->storageVal(null);
//        $this-assertNull($ret);

        $this->obj->setAllowNull(false);
        $this->expectException(Exception::class);
        $this->obj->storageVal(null);
    }

    /**
     * @return void
     */
    public function testDisplayVal()
    {
        // Test default format
        $this->assertEquals('2015-10-01 15:00:00', $this->obj->displayVal('October 1st, 2015 15:00:00'));

        // Test with custom format and DateTime parameter
        $this->obj->setFormat('Y/m/d');
        $this->assertEquals('2015/09/01', $this->obj->displayVal(new DateTime('September 1st, 2015')));

        // Test with custom format passed as parameter
        $this->assertEquals('2017/12/12', $this->obj->displayVal('December 12, 2017', ['format'=>'Y/m/d']));

        // Test with null value
        $this->assertEquals('', $this->obj->displayVal(null));
    }

    public function testInputVal()
    {
        // Test default format
        $this->assertEquals('2015-10-01 15:00:00', $this->obj->inputVal('October 1st, 2015 15:00:00'));

        // Test with custom format and DateTime parameter
        $this->obj->setFormat('Y/m/d');
        $this->assertEquals('2015-09-01 00:00:00', $this->obj->inputVal(new DateTime('September 1st, 2015')));

        $this->assertEquals('', $this->obj->inputVal(null));
    }

    /**
     * Assert that the `setMultiple()` method:
     * - set the multiple to false, if false or falsish value
     * - throws exception otherwise (truthish or invalid value)
     * - is chainable
     *
     * @return void
     */
    public function testSetMultiple()
    {
        $ret = $this->obj->setMultiple(0);
        $this->assertSame($ret, $this->obj);
        $this->assertSame(false, $ret['multiple']);

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setMultiple(1);
    }

    /**
     * @return void
     */
    public function testMultiple()
    {
        $this->assertSame(false, $this->obj['multiple']);
    }

    /**
     * Assert that the `min` method:
     * - is chainable
     * - sets the min value from a string or DateTime object
     * - accepts null as parameter
     * - throws exception when the argument is invalid
     *
     * @return void
     */
    public function testSetMin()
    {
        // Setting by string
        $ret = $this->obj->setMin('2020-01-01 01:02:03');
        $this->assertSame($ret, $this->obj);

        $expected = new DateTime('2020-01-01 01:02:03');
        $this->assertEquals($expected, $this->obj['min']);

        // Setting by DateTime
        $dt = new DateTime('today');
        $ret = $this->obj->setMin($dt);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($dt, $this->obj['min']);

        $this->obj['min'] = 'today';
        $this->assertEquals($dt, $this->obj['min']);

        $this->obj->set('min', 'today');
        $this->assertEquals($dt, $this->obj['min']);

        $this->obj->setMin(null);
        $this->assertNull($this->obj['min']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setMin('foo');
    }

    public function testSetMinInvalidObjectThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setMin(new \StdClass());
    }

    /**
     * Assert that the `max` method:
     * - is chainable
     * - sets the max value
     * - accepts null as a parameter
     * - throws exception when the argument is invalid
     *
     * @return void
     */
    public function testSetMax()
    {
        $ret = $this->obj->setMax('2020-01-01 01:02:03');
        $this->assertSame($ret, $this->obj);

        $expected = new DateTime('2020-01-01 01:02:03');
        $this->assertEquals($expected, $this->obj['max']);

        // Setting by DateTime
        $dt = new DateTime('today');
        $ret = $this->obj->setMax($dt);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($dt, $this->obj['max']);

        $this->obj['max'] = 'today';
        $this->assertEquals($dt, $this->obj['max']);

        $this->obj->set('max', 'today');
        $this->assertEquals($dt, $this->obj['max']);

        $this->obj->setMax(null);
        $this->assertEquals(null, $this->obj['max']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setMax('foo');
    }

    public function testSetMaxInvalidObjectThrowsException()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->obj->setMax(new \StdClass());
    }

    /**
     * Assert that the `format()` method
     * - is chainable
     * and that the `setFormat()`:
     * - is chainable
     * - sets the format
     * - accepts empty string
     * - accepts null as a parameter (convert to empty string)
     * - throws an exception if not a string
     *
     * @return void
     */
    public function testSetFormat()
    {
        $this->assertEquals('Y-m-d H:i:s', $this->obj['format']);

        $ret = $this->obj->setFormat('Y/m/d');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Y/m/d', $this->obj['format']);

        $this->obj['format'] = 'd-m-Y';
        $this->assertEquals('d-m-Y', $this->obj['format']);

        $this->obj->set('format', 'Y');
        $this->assertEquals('Y', $this->obj['format']);

        $this->obj->setFormat('');
        $this->assertEquals('', $this->obj['format']);

        $this->obj->setFormat(null);
        $this->assertEquals('', $this->obj['format']);

        $this->expectException(InvalidArgumentException::class);
        $this->obj->setFormat(false);
    }

    /**
     * @return void
     */
    public function testSave()
    {
        $this->assertEquals(null, $this->obj->save(null));

        $expected = new DateTime('2015-01-01');
        $this->assertEquals($expected, $this->obj->save('2015-01-01'));
    }

    public function testValidationMethods()
    {
        $this->assertContains('min', $this->obj->validationMethods());
        $this->assertContains('max', $this->obj->validationMethods());
    }

    /**
     * Assert that the `validateMin` method:
     * - Returns true if no "min" is set
     * - Returns true when the value is equal or bigger
     * - Returns false when the value is smaller
     *
     * @return void
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
        $this->assertFalse($this->obj->validateMin());
    }

    /**
     * Assert that the `validateMax` method:
     * - Returns true if no "max" is set
     * - Returns true when the value is equal or smaller
     * - Returns false when the value is bigger
     *
     * @return void
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

    /**
     * @return void
     */
    public function testSqlExtra()
    {
        $this->assertSame(null, $this->obj->sqlExtra());
    }

    /**
     * @return void
     */
    public function testSqlType()
    {
        $this->assertEquals('DATETIME', $this->obj->sqlType());
    }

    /**
     * @return void
     */
    public function testSqlPdoType()
    {
        $this->assertEquals(PDO::PARAM_STR, $this->obj->sqlPdoType());
    }
}
