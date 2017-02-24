<?php

namespace Charcoal\Tests\Property;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

// From 'charcoal-property'
use Charcoal\Property\SelectablePropertyTrait;

/**
 *
 */
class SelectablePropertyTraitTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    private $obj;

    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = $this->getMockForTrait(SelectablePropertyTrait::class);
        $this->obj
            ->expects($this->any())
            ->method('translator')
            ->will($this->returnValue($container['translator']));
    }

    public function testSetChoices()
    {
        $container = $this->getContainer();
        $locales   = $container['language/manager'];
        $locales->setCurrentLocale($locales->currentLocale());

        $this->assertEquals([], $this->obj->choices());

        $this->assertFalse($this->obj->hasChoice('foo'));
        $this->assertFalse($this->obj->hasChoice('bar'));

        $ret = $this->obj->choice('foo');
        $this->assertEquals(['value' => 'foo', 'label' => ''], $ret);

        $choices = [
            'foo' => 'bar',
            'bar' => 'baz'
        ];
        $expected = [
            'foo' => [
                'value' => 'foo',
                'label' => new Translation('bar', $locales)
            ],
            'bar' => [
                'value' => 'bar',
                'label' => new Translation('baz', $locales)
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
