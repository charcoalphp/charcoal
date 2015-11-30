<?php

namespace Charcoal\Tests\Model;

use \Charcoal\Property\GenericProperty;

class PropertyTest extends \PHPUnit_Framework_TestCase
{
    /**
    * Hello world
    */
    public function testConstructor()
    {
        $obj = new GenericProperty();
        $this->assertInstanceOf('\Charcoal\Property\GenericProperty', $obj);

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
        $obj = new GenericProperty();
        $this->assertInstanceOf('\Charcoal\Property\GenericProperty', $obj);
    }

    public function testToStringReturnsVal()
    {
        $obj = new GenericProperty();
        $obj->set_val('foo');
        $this->assertEquals('foo', sprintf($obj));
    }

    public function testToStringReturnsEmptyIfValIsNotAString()
    {
        $obj = new GenericProperty();
        $obj->set_val([1, 2, 3]);
        $this->assertEquals('', sprintf($obj));
    }

    public function testSetData()
    {
        $obj = new GenericProperty();

        $obj->set_data(['label' => 'foo']);
        $this->assertEquals('foo', $obj->label());

        $obj->set_data(
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
        $obj->set_data($data);

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
        $obj = new GenericProperty();

        $obj->set_val($val);
        $this->assertEquals($val, $obj->val());
    }

    public function testSetValIsChainable()
    {
        $obj = new GenericProperty();
        $ret = $obj->set_val('foo');
        $this->assertSame($ret, $obj);
    }

    public function testSetL10n()
    {
        $obj = new GenericProperty();

        $obj->set_l10n(true);
        $this->assertEquals(true, $obj->l10n());

        $obj->set_l10n(false);
        $this->assertEquals(false, $obj->l10n());
    }

    /**
    * @dataProvider providerInvalidBools
    */
    public function testSetL10nInvalidParameterThrowException($invalid)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new GenericProperty();
        $obj->set_l10n($invalid);
    }

    public function testSetL10nIsChainable()
    {
        $obj = new GenericProperty();
        $ret = $obj->set_l10n(true);
        $this->assertSame($ret, $obj);
    }

    public function testSetHidden()
    {
        $obj = new GenericProperty();

        $obj->set_hidden(true);
        $this->assertEquals(true, $obj->hidden());

        $obj->set_hidden(false);
        $this->assertEquals(false, $obj->hidden());
    }

    /**
    * @dataProvider providerInvalidBools
    */
    public function testSetHiddenInvalidParameterThrowException($invalid)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new GenericProperty();
        $obj->set_hidden($invalid);
    }

    public function testSetHiddenIsChainable()
    {
        $obj = new GenericProperty();
        $ret = $obj->set_hidden(true);
        $this->assertSame($ret, $obj);
    }

    public function testSetMultiple()
    {
        $obj = new GenericProperty();

        $obj->set_multiple(true);
        $this->assertEquals(true, $obj->multiple());

        $obj->set_multiple(false);
        $this->assertEquals(false, $obj->multiple());
    }

    /**
    * @dataProvider providerInvalidBools
    */
    public function testSetMultipleInvalidParameterThrowException($invalid)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new GenericProperty();
        $obj->set_multiple($invalid);
    }

    public function testSetMultipleIsChainable()
    {
        $obj = new GenericProperty();
        $ret = $obj->set_multiple(true);
        $this->assertSame($ret, $obj);
    }

    public function testSetUnique()
    {
        $obj = new GenericProperty();

        $obj->set_unique(true);
        $this->assertEquals(true, $obj->unique());

        $obj->set_unique(false);
        $this->assertEquals(false, $obj->unique());
    }

    /**
    * @dataProvider providerInvalidBools
    */
    public function testSetUniqueInvalidParameterThrowException($invalid)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new GenericProperty();
        $obj->set_unique($invalid);
    }

    public function testSetUniqueIsChainable()
    {
        $obj = new GenericProperty();
        $ret = $obj->set_unique(true);
        $this->assertSame($ret, $obj);
    }

    public function testSetRequired()
    {
        $obj = new GenericProperty();

        $obj->set_required(true);
        $this->assertEquals(true, $obj->required());

        $obj->set_required(false);
        $this->assertEquals(false, $obj->required());
    }

    /**
    * @dataProvider providerInvalidBools
    */
    public function testSetRequiredInvalidParameterThrowException($invalid)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new GenericProperty();
        $obj->set_required($invalid);
    }

    public function testSetRequiredIsChainable()
    {
        $obj = new GenericProperty();
        $ret = $obj->set_required(true);
        $this->assertSame($ret, $obj);
    }

    public function testSetActive()
    {
        $obj = new GenericProperty();

        $obj->set_active(true);
        $this->assertEquals(true, $obj->active());

        $obj->set_active(false);
        $this->assertEquals(false, $obj->active());
    }

    /**
    * @dataProvider providerInvalidBools
    */
    public function testSetActiveInvalidParameterThrowException($invalid)
    {
        $this->setExpectedException('\InvalidArgumentException');

        $obj = new GenericProperty();
        $obj->set_active($invalid);
    }

    public function testSetActiveIsChainable()
    {
        $obj = new GenericProperty();
        $ret = $obj->set_active(true);
        $this->assertSame($ret, $obj);
    }

    public function testRenderWithoutReplacements()
    {
        $obj= new GenericProperty();
        $this->assertEquals('empty', $obj->render('empty'));
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
