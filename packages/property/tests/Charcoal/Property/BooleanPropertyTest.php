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

    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->obj = new BooleanProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    public function testType(): void
    {
        $this->assertEquals('boolean', $this->obj->type());
    }

    /**
     * Assert that the boolean property 's `displayVal()` method:
     * - return the proper label
     */
    public function testDisplayVal(): void
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
     */
    public function testSetMultiple(): void
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
     */
    public function testMultiple(): void
    {
        $obj = $this->obj;
        $this->assertFalse($obj['multiple']);
    }

    public function testSetData(): void
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

    public function testSetTrueLabel(): void
    {
        $obj = $this->obj;
        $ret = $obj->setTrueLabel('foo');
        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj['trueLabel']);
    }

    public function testSetFalseLabel(): void
    {
        $obj = $this->obj;
        $ret = $obj->setFalseLabel('foo');
        $this->assertSame($ret, $obj);

        $this->assertEquals('foo', $obj['falseLabel']);
    }

    public function testSqlExtra(): void
    {
        $obj = $this->obj;
        $this->assertSame(null, $obj->sqlExtra());
    }

    public function testSqlType(): void
    {
        //$this->assertEquals('TINYINT(1) UNSIGNED', $this->obj->sqlType());
        $this->assertEquals('INT', $this->obj->sqlType());
    }

    public function testSqlPdoType(): void
    {
        $this->assertEquals(PDO::PARAM_BOOL, $this->obj->sqlPdoType());
    }

    public function testChoices(): void
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

    public function testSave(): void
    {
        $this->assertTrue($this->obj->save(true));
        $this->assertFalse($this->obj->save(false));
    }
}
