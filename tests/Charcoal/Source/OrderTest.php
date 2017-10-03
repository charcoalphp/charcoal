<?php

namespace Charcoal\Tests\Loader\CollectionLoader;

// From 'charcoal-core'
use Charcoal\Source\Order;

/**
 *
 */
class OrderTest extends \PHPUnit_Framework_TestCase
{

    public function testContructor()
    {
        $obj = new Order();
        $this->assertInstanceOf('\Charcoal\Source\Order', $obj);

        // Default values
        $this->assertEquals('', $obj->property());
        $this->assertEquals('', $obj->mode());
        $this->assertEquals('', $obj->values());
    }

    public function testSetData()
    {
        $obj = new Order();

        $obj->setData(['property' => 'foo']);
        $this->assertEquals('foo', $obj->property());

        $obj->setData(
            [
                'property' => 'bar',
                'mode' => 'asc'
            ]
        );
        $this->assertEquals('bar', $obj->property());
        $this->assertEquals('asc', $obj->mode());
    }

    public function testDataIsChainable()
    {
        $obj = new Order();
        $ret = $obj->setData([]);

        $this->assertSame($ret, $obj);
    }

    public function testSetProperty()
    {
        $obj = new Order();
        $obj->setProperty('foo');

        $this->assertEquals('foo', $obj->property());
    }

    public function testSetPropertyIsChainable()
    {
        $obj = new Order();
        $ret = $obj->setProperty('foo');

        $this->assertSame($obj, $ret);
    }

    /**
     * @dataProvider providerInvalidProperties
     */
    public function testSetInvalidPropertyThrowsException($property)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new Order();
        $obj->setProperty($property);
    }

    public function testSetMode()
    {
        $obj = new Order();
        $obj->setMode('asc');

        $this->assertEquals('asc', $obj->mode());
    }

    public function testSetModeIsChainable()
    {
        $obj = new Order();
        $ret = $obj->setMode('asc');

        $this->assertSame($obj, $ret);
    }

    /**
     * @dataProvider providerInvalidModes
     */
    public function testSetInvalidModeThrowsException($mode)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new Order();
        $obj->setMode($mode);
    }

    public function testSetValues()
    {
        $obj = new Order();
        $obj->setValues(['foo']);

        $this->assertEquals(['foo'], $obj->values());
    }

    public function testSetValuesByStringExplodesArray()
    {
        $obj = new Order();
        $obj->setValues('foo,bar,val');

        $this->assertEquals(['foo','bar','val'], $obj->values());
    }

    public function testSetValuesByStringTrim()
    {
        $obj = new Order();
        $obj->setValues('foo, bar, val');
// Spaces between values

        $this->assertEquals(['foo','bar','val'], $obj->values());
    }

    public function testSetValuesIsChainable()
    {
        $obj = new Order();
        $ret = $obj->setValues(['foo']);

        $this->assertSame($obj, $ret);
    }

    /**
     * @dataProvider providerInvalidValues
     */
    public function testSetInvalidValuesThrowsException($values)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new Order();
        $obj->setValues($values);
    }



    /**
     * Invalid arguments for operator, func and operand
     */
    public function providerInvalidProperties()
    {
        $obj = new \StdClass();
        return [
            [''],
            [null],
            [true],
            [false],
            [1],
            [0],
            [321],
            [[]],
            [['foo']],
            [1,2,3],
            [$obj]
        ];
    }

    /**
     * Invalid arguments for operator, func and operand
     */
    public function providerInvalidModes()
    {
        $obj = new \StdClass();
        return [
            ['invalid string'],
            [''],
            [null],
            [true],
            [false],
            [1],
            [0],
            [321],
            [[]],
            [['foo']],
            [1,2,3],
            [$obj]
        ];
    }

    /**
     * Invalid arguments for operator, func and operand
     */
    public function providerInvalidValues()
    {
        $obj = new \StdClass();
        return [
            [''],
// empty strings are invalid
            [null],
            [true],
            [false],
            [1],
            [0],
            [321],
            [[]],
// empty arrays are invalid
            [$obj]
        ];
    }

    public function providerAscDesc()
    {
        return [
            ['asc'],
            ['desc']
        ];
    }
}
