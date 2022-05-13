<?php

namespace Charcoal\Tests\Property;

use PDO;

// From 'charcoal-property'
use Charcoal\Property\BooleanProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 * ## TODOs
 * - 2015-03-12:
 */
class BooleanPropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var BooleanProperty
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new BooleanProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('boolean', $this->obj->type());
    }

    /**
     * Assert that the boolean property 's `displayVal()` method:
     * - return the proper label
     *
     * @return void
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

        $this->assertEquals('V', $this->obj->displayVal(true, ['true_label'=>'V']));
        $this->assertEquals('F', $this->obj->displayVal(false, ['false_label'=>'F']));
    }

    /**
     * Assert that the boolean property's `setMultiple()` method:
     * - set the multiple to false, if false or falsish value
     * - throws exception otherwise (truthish or invalid value)
     * - is chainable
     *
     * @return void
     */
    public function testSetMultiple()
    {
        $obj = $this->obj;
        $ret = $obj->setMultiple(0);
        $this->assertSame($ret, $obj);
        $this->assertFalse($ret['multiple']);

        $this->expectException('\InvalidArgumentException');
        $obj->setMultiple(1);
    }

    /**
     * Asserts that the boolean property is multiple by default
     *
     * @return void
     */
    public function testMultiple()
    {
        $obj = $this->obj;
        $this->assertFalse($obj['multiple']);
    }

    /**
     * @return void
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

        $this->assertEquals('foo', $obj['trueLabel']);
        $this->assertEquals('bar', $obj['falseLabel']);
    }

    /**
     * @return void
     */
    public function testSetTrueLabel()
    {
        $obj = $this->obj;
        $ret = $obj->setTrueLabel('foo');
        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj['trueLabel']);
    }

    /**
     * @return void
     */
    public function testSetFalseLabel()
    {
        $obj = $this->obj;
        $ret = $obj->setFalseLabel('foo');
        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj['falseLabel']);
    }

    /**
     * @return void
     */
    public function testSqlExtra()
    {
        $obj = $this->obj;
        $this->assertSame(null, $obj->sqlExtra());
    }

    /**
     * @return void
     */
    public function testSqlType()
    {
        //$this->assertEquals('TINYINT(1) UNSIGNED', $this->obj->sqlType());
        $this->assertEquals('INT', $this->obj->sqlType());
    }

    /**
     * @return void
     */
    public function testSqlPdoType()
    {
        $this->assertEquals(PDO::PARAM_BOOL, $this->obj->sqlPdoType());
    }

    /**
     * @return void
     */
    public function testChoices()
    {
        $obj = $this->obj;
        $obj->setVal(false);
        $choices = [
            [
                'label'    => 'True',
                'selected' => false,
                'value'    => 1,
            ],
            [
                'label'    => 'False',
                'selected' => true,
                'value'    => 0,
            ],
        ];
        $this->assertEquals($choices, $obj->choices());
    }

    /**
     * @return void
     */
    public function testSave()
    {
        $this->assertTrue($this->obj->save(true));
        $this->assertFalse($this->obj->save(false));
    }
}
