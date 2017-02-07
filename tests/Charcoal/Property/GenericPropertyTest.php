<?php

namespace Charcoal\Tests\Model;

use PHPUnit_Framework_TestCase;

use \PDO;

use \Psr\Log\NullLogger;

use \Charcoal\Property\PropertyFactory;
use \Charcoal\Property\GenericProperty;

class PropertyTest extends PHPUnit_Framework_TestCase
{
    public $factory;
    public $obj;

    public function getObj()
    {
        return $this->factory->create(GenericProperty::class, [
            'database' => new PDO('sqlite::memory:'),
            'logger' => new NullLogger(),
            'translator' => $GLOBALS['translator']
        ]);
    }

    public function setUp()
    {
        $this->factory   = new PropertyFactory();
        $this->obj       = $this->getObj();
    }

    public function testDefaultValues()
    {
        $obj = $this->obj;

        // Check default Value
        $this->assertEquals(false, $obj->l10n());
        $this->assertEquals(false, $obj->multiple());
        $this->assertEquals(false, $obj->required());
        $this->assertEquals(false, $obj->unique());
        $this->assertEquals(false, $obj->hidden());
        $this->assertEquals(true, $obj->active());
    }

    public function testStaticGetWithoutParameter()
    {
        $obj = $this->obj;

        $this->assertInstanceOf('\Charcoal\Property\GenericProperty', $obj);
    }

    public function testToStringReturnsVal()
    {
        $obj = $this->obj;

        $obj->setVal('foo');
        $this->assertEquals('foo', sprintf($obj));
    }

    public function testToStringReturnsEmptyIfValIsNotAString()
    {
        $obj = $this->obj;

        $obj->setVal([1, 2, 3]);
        $this->assertEquals('', sprintf($obj));
    }

    public function testSetData()
    {
        $obj = $this->obj;

        $obj->setData(['label' => 'foo']);
        $this->assertEquals('foo', $obj->label());

        $obj->setData(
            [
                'val'   => '123',
                'label' => 'bar'
            ]
        );
        $this->assertEquals('123', $obj->val());
        $this->assertEquals('bar', $obj->label());

        // Full data et
        $data = [
            'val'      => [1, 2, 3],
            'label'    => 'baz',
            'l10n'     => true,
            'multiple' => false,
            'required' => true,
            'unique'   => false,
            'hidden'   => true,
            'active'   => false
        ];
        $obj->setData($data);

        $this->assertEquals([1, 2, 3], $obj->val());
        $this->assertEquals('baz', $obj->label());
        $this->assertEquals(true, $obj->l10n());
        $this->assertEquals(false, $obj->multiple());
        $this->assertEquals(true, $obj->required());
        $this->assertEquals(false, $obj->unique());
        $this->assertEquals(true, $obj->hidden());
        $this->assertEquals(false, $obj->active());
    }

    /**
     * @dataProvider providerVals
     */
    public function testSetVal($val)
    {
        $obj = $this->obj;

        $obj->setVal($val);
        $this->assertEquals($val, $obj->val());
    }

    public function testSetValIsChainable()
    {
        $obj = $this->obj;

        $ret = $obj->setVal('foo');
        $this->assertSame($ret, $obj);
    }

    public function testSetL10n()
    {
        $obj = $this->obj;

        $obj->setL10n(true);
        $this->assertEquals(true, $obj->l10n());

        $obj->setL10n(false);
        $this->assertEquals(false, $obj->l10n());
    }

    public function testSetL10nIsChainable()
    {
        $obj = $this->obj;
        $ret = $obj->setL10n(true);
        $this->assertSame($ret, $obj);
    }

    public function testSetHidden()
    {
        $obj = $this->obj;

        $obj->setHidden(true);
        $this->assertEquals(true, $obj->hidden());

        $obj->setHidden(false);
        $this->assertEquals(false, $obj->hidden());
    }

    public function testSetHiddenIsChainable()
    {
        $obj = $this->obj;
        $ret = $obj->setHidden(true);
        $this->assertSame($ret, $obj);
    }

    public function testSetMultiple()
    {
        $obj = $this->obj;

        $obj->setMultiple(true);
        $this->assertEquals(true, $obj->multiple());

        $obj->setMultiple(false);
        $this->assertEquals(false, $obj->multiple());
    }

    public function testSetMultipleIsChainable()
    {
        $obj = $this->obj;
        $ret = $obj->setMultiple(true);
        $this->assertSame($ret, $obj);
    }

    public function testSetUnique()
    {
        $obj = $this->obj;

        $obj->setUnique(true);
        $this->assertEquals(true, $obj->unique());

        $obj->setUnique(false);
        $this->assertEquals(false, $obj->unique());
    }

    public function testSetUniqueIsChainable()
    {
        $obj = $this->obj;
        $ret = $obj->setUnique(true);
        $this->assertSame($ret, $obj);
    }

    public function testSetRequired()
    {
        $obj = $this->obj;

        $obj->setRequired(true);
        $this->assertEquals(true, $obj->required());

        $obj->setRequired(false);
        $this->assertEquals(false, $obj->required());
    }

    public function testSetRequiredIsChainable()
    {
        $obj = $this->obj;
        $ret = $obj->setRequired(true);
        $this->assertSame($ret, $obj);
    }

    public function testSetActive()
    {
        $obj = $this->obj;

        $obj->setActive(true);
        $this->assertEquals(true, $obj->active());

        $obj->setActive(false);
        $this->assertEquals(false, $obj->active());
    }

    public function testSetActiveIsChainable()
    {
        $obj = $this->obj;
        $ret = $obj->setActive(true);
        $this->assertSame($ret, $obj);
    }

    public function providerVals()
    {
        $obj = new \StdClass();
        return [
            ['foor'],
            [['array']],
            [null],
            [0],
            [1],
            [(-1)],
            [42],
            ['42'],
            [1, 2, 3],
            [$obj]
        ];
    }

    public function providerInvalidBools()
    {
        $obj = new \StdClass();
        return [
            ['foor'],
            [['array']],
            [null],
            [0],
            [1],
            [(-1)],
            [42],
            ['42'],
            [1, 2, 3],
            [$obj]
        ];
    }
}
