<?php

namespace Charcoal\Tests\Property;

use \Charcoal\Property\BooleanProperty as BooleanProperty;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class BooleanPropertyTest extends \PHPUnit_Framework_TestCase
{
    public function testConstructor()
    {
        $obj = new BooleanProperty();
        $this->assertInstanceOf('\Charcoal\Property\BooleanProperty', $obj);
    }

    public function testType()
    {
        $obj = new BooleanProperty();
        $this->assertEquals('boolean', $obj->type());
    }

    public function testDisplayVal()
    {
        $obj = new BooleanProperty();
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
        $obj = new BooleanProperty();
        $ret = $obj->setMultiple(0);
        $this->assertSame($ret, $obj);
        $this->assertSame(false, $ret->multiple());

        $this->setExpectedException('\InvalidArgumentException');
        $obj->setMultiple(1);
    }

    public function testMultiple()
    {
        $obj = new BooleanProperty();
        $this->assertSame(false, $obj->multiple());
    }

    public function testSetData()
    {
        $obj = new BooleanProperty();
        $data = [
            'true_label'=>'foo',
            'false_label'=>'bar'
        ];
        $ret = $obj->setData($data);

        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj->trueLabel());
        $this->assertEquals('bar', $obj->falseLabel());
    }

    public function testSetTrueLabel()
    {
        $obj = new BooleanProperty();
        $ret = $obj->setTrueLabel('foo');
        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj->trueLabel());

        //$this->setExpectedException('\InvalidArgumentException');
        //$obj->setTrueLabel(false);
    }

    public function testSetFalseLabel()
    {
        $obj = new BooleanProperty();
        $ret = $obj->setFalseLabel('foo');
        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj->falseLabel());

        //$this->setExpectedException('\InvalidArgumentException');
        //$obj->setFalseLabel(false);
    }

    public function testSqlExtra()
    {
        $obj = new BooleanProperty();
        $this->assertSame('', $obj->sqlExtra());
    }

    public function testSqlType()
    {
        $obj = new BooleanProperty();
        $this->assertEquals('TINYINT(1) UNSIGNED', $obj->sqlType());
    }

    public function testSqlPdoType()
    {
        $obj = new BooleanProperty();
        $this->assertEquals(\PDO::PARAM_BOOL, $obj->sqlPdoType());
    }

    public function testChoices()
    {
        $obj = new BooleanProperty();
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

    public function testSave()
    {
        $obj = new BooleanProperty();

        $obj->setVal(true);
        $this->assertTrue($obj->save());

        $obj->setVal(false);
        $this->assertNotTrue($obj->save());

    }
}
