<?php

namespace Charcoal\Tests\Property;

use PDO;

use Psr\Log\NullLogger;

use Charcoal\Property\BooleanProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class BooleanPropertyTest extends \PHPUnit_Framework_TestCase
{
    public $obj;

    /**
     *
     */
    public function setUp()
    {
        $this->obj = new BooleanProperty([
            'database'  => new PDO('sqlite::memory:'),
            'logger'    => new NullLogger(),
            'translator' => $GLOBALS['translator']
        ]);
    }

    /**
     *
     */
    public function testType()
    {
        $this->assertEquals('boolean', $this->obj->type());
    }

    /**
     * Assert that the boolean property 's `displayVal()` method:
     * - return the proper label
     */
    public function testDisplayVal()
    {
        $this->obj->setTrueLabel('Oui');
        $this->obj->setFalseLabel('Non');

        $this->assertEquals('Oui', $this->obj->displayVal(true));
        $this->assertEquals('Non', $this->obj->displayVal(false));

        $this->obj['true_label'] = 'Yes';
        $this->obj['false_label'] = 'No';

        $this->assertEquals('Yes', $this->obj->displayVal(true));
        $this->assertEquals('No', $this->obj->displayVal(false));
    }

    /**
     * Assert that the boolean property's `setMultiple()` method:
     * - set the multiple to false, if false or falsish value
     * - throws exception otherwise (truthish or invalid value)
     * - is chainable
     */
    public function testSetMultiple()
    {
        $obj = $this->obj;
        $ret = $obj->setMultiple(0);
        $this->assertSame($ret, $obj);
        $this->assertFalse($ret->multiple());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMultiple(1);
    }

    /**
     * Asserts that the boolean property is multiple by default
     */
    public function testMultiple()
    {
        $obj = $this->obj;
        $this->assertFalse($obj->multiple());
    }

    /**
     *
     */
    public function testSetData()
    {
        $obj = $this->obj;
        $data = [
            'true_label'=>'foo',
            'false_label'=>'bar'
        ];
        $ret = $obj->setData($data);

        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj->trueLabel());
        $this->assertEquals('bar', $obj->falseLabel());
    }

    /**
     *
     */
    public function testSetTrueLabel()
    {
        $obj = $this->obj;
        $ret = $obj->setTrueLabel('foo');
        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj->trueLabel());
    }

    /**
     *
     */
    public function testSetFalseLabel()
    {
        $obj = $this->obj;
        $ret = $obj->setFalseLabel('foo');
        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj->falseLabel());
    }

    /**
     *
     */
    public function testSqlExtra()
    {
        $obj = $this->obj;
        $this->assertSame('', $obj->sqlExtra());
    }

    /**
     *
     */
    public function testSqlType()
    {
        //$this->assertEquals('TINYINT(1) UNSIGNED', $this->obj->sqlType());
        $this->assertEquals('INT', $this->obj->sqlType());
    }

    /**
     *
     */
    public function testSqlPdoType()
    {
        $this->assertEquals(PDO::PARAM_BOOL, $this->obj->sqlPdoType());
    }

    /**
     *
     */
    public function testChoices()
    {
        $obj = $this->obj;
        $obj->setVal(false);
        $choices = [
            [
                'label'=>'True',
                'selected'=>false,
                'value'=>1
            ],
            [
                'label'=>'False',
                'selected'=>true,
                'value'=>0
            ]
        ];
        $this->assertEquals($choices, $obj->choices());
    }

    /**
     *
     */
    public function testSave()
    {
        $this->assertTrue($this->obj->save(true));
        $this->assertFalse($this->obj->save(false));
    }
}
