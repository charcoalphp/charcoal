<?php

namespace Charcoal\Tests\Property;

use PHPUnit_Framework_TestCase;

use Charcoal\Translation\TranslationString;

use Charcoal\Property\SelectablePropertyTrait;

/**
 *
 */
class SelectablePropertyTraitTest extends PHPUnit_Framework_TestCase
{
    private $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForTrait(SelectablePropertyTrait::class, []);
    }

    public function testSetChoices()
    {
        $this->assertEquals([], $this->obj->choices());

        $this->assertFalse($this->obj->hasChoice('foo'));
        $this->assertFalse($this->obj->hasChoice('bar'));

        $ret = $this->obj->choice('foo');
        $this->assertEquals(['value'=>'foo', 'label'=>''], $ret);

        $choices = [
            'foo'=>'bar',
            'bar'=>'baz'
        ];
        $expected = [
            'foo' => [
                'value' => 'foo',
                'label' => new TranslationString('bar')
            ],
            'bar' => [
                'value' =>'bar',
                'label' => new TranslationString('baz')
            ]
        ];
        $ret = $this->obj->setChoices($choices);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($expected, $this->obj->choices());

        $this->assertTrue($this->obj->hasChoice('foo'));
        $this->assertTrue($this->obj->hasChoice('bar'));

        $this->assertEquals($expected['foo'], $this->obj->choice('foo'));
        $this->assertEquals($expected['bar'], $this->obj->choice('bar'));
    }
}
