<?php

namespace Charcoal\Tests\Property;

use \Charcoal\Property\BooleanProperty as BooleanProperty;

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
        $this->obj = new BooleanProperty();
    }

    /**
     *
     */
    public function testConstructor()
    {
        $obj = $this->obj;
        $this->assertInstanceOf('\Charcoal\Property\BooleanProperty', $obj);
    }

    /**
     *
     */
    public function testType()
    {
        $obj = $this->obj;
        $this->assertEquals('boolean', $obj->type());
    }

    /**
     *
     */
    public function testDisplayVal()
    {
        $obj = $this->obj;
        $this->assertEquals('False', $obj->displayVal());
        $obj->setVal(true);
        $this->assertEquals('True', $obj->displayVal());

        $obj->setTrueLabel('Oui');
        $obj->setFalseLabel('Non');

        $this->assertEquals('Oui', $obj->displayVal(true));
        $this->assertEquals('Non', $obj->displayVal(false));
    }

    /**
     * Assert that the `setMultiple()` method:
     * - set the multiple to false, if false or falsish value
     * - throws exception otherwise (truthish or invalid value)
     * - is chainable
     */
    public function testSetMultiple()
    {
        $obj = $this->obj;
        $ret = $obj->setMultiple(0);
        $this->assertSame($ret, $obj);
        $this->assertSame(false, $ret->multiple());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMultiple(1);
    }

    /**
     *
     */
    public function testMultiple()
    {
        $obj = $this->obj;
        $this->assertSame(false, $obj->multiple());
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

        //$this->setExpectedException('\InvalidArgumentException');
        //$obj->setTrueLabel(false);
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

        //$this->setExpectedException('\InvalidArgumentException');
        //$obj->setFalseLabel(false);
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
        $obj = $this->obj;
        $this->assertEquals('TINYINT(1) UNSIGNED', $obj->sqlType());
    }

    /**
     *
     */
    public function testSqlPdoType()
    {
        $obj = $this->obj;
        $this->assertEquals(\PDO::PARAM_BOOL, $obj->sqlPdoType());
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
        $obj = $this->obj;

        $obj->setVal(true);
        $this->assertTrue($obj->save());

        $obj->setVal(false);
        $this->assertNotTrue($obj->save());
    }
}
