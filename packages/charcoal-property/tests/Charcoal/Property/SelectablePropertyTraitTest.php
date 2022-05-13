<?php

namespace Charcoal\Tests\Property;

use ReflectionClass;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

// From 'charcoal-property'
use Charcoal\Property\SelectablePropertyTrait;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;
use Charcoal\Tests\Property\ContainerIntegrationTrait;

/**
 * Selectable Property Test
 */
class SelectablePropertyTraitTest extends AbstractTestCase
{
    use ReflectionsTrait;
    use ContainerIntegrationTrait;

    /**
     * Tested Class.
     *
     * @var SelectablePropertyTrait
     */
    private $obj;

    /**
     * Set up the test.
     *
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = $this->getMockForTrait(SelectablePropertyTrait::class);
        $this->obj->expects($this->any())
                  ->method('translator')
                  ->will($this->returnValue($container['translator']));
    }

    /**
     * @param  mixed $val The translation string.
     * @return Translation
     */
    public function translation($val)
    {
        $container = $this->getContainer();
        $locales   = $container['locales/manager'];

        return new Translation($val, $locales);
    }

    /**
     * @return void
     */
    public function testEmptyChoices()
    {
        $this->assertEquals([], $this->obj->choices());

        $this->assertFalse($this->obj->hasChoices());
        $this->assertFalse($this->obj->hasChoice('foo'));

        $this->assertEquals([ 'value' => 'foo', 'label' => '' ], $this->obj->choice('foo'));

        $this->assertNull($this->obj->choiceLabel(null));

        $this->assertEquals('xuq', $this->obj->choiceLabel([ 'value' => 'qux', 'label' => 'xuq' ]));
        $this->assertEquals('qux', $this->obj->choiceLabel([ 'value' => 'qux', 'label' => null ]));
        $this->assertEquals('', $this->obj->choiceLabel([ 'value' => 'qux', 'label' => '' ]));
        $this->assertEquals('qux', $this->obj->choiceLabel([ 'value' => 'qux' ]));

        $this->assertEquals('qux', $this->obj->choiceLabel('qux'));
    }

    /**
     * @return void
     */
    public function testChoices()
    {
        $choices = [
            'foo' => 'oof',
            'bar' => 'rab'
        ];
        $expected = [
            'foo' => [
                'value' => 'foo',
                'label' => $this->translation('oof')
            ],
            'bar' => [
                'value' => 'bar',
                'label' => $this->translation('rab')
            ]
        ];

        $ret = $this->obj->setChoices($choices);
        $this->assertSame($ret, $this->obj);

        $this->assertEquals($expected, $this->obj->choices());

        $this->assertTrue($this->obj->hasChoices());
        $this->assertTrue($this->obj->hasChoice('foo'));
        $this->assertTrue($this->obj->hasChoice('bar'));
        $this->assertFalse($this->obj->hasChoice('qux'));

        $this->assertEquals($expected['foo'], $this->obj->choice('foo'));
        $this->assertEquals($expected['bar'], $this->obj->choice('bar'));

        $this->assertNull($this->obj->choiceLabel(null));

        $this->assertEquals($expected['foo']['label'], $this->obj->choiceLabel('foo'));
        $this->assertEquals($expected['bar']['label'], $this->obj->choiceLabel('bar'));
    }

    /**
     * @return void
     */
    public function testChoiceLabelStructException()
    {
        $this->expectException('\InvalidArgumentException');
        $this->obj->choiceLabel([]);
    }

    /**
     * @return void
     */
    public function testChoiceLabelKeyException()
    {
        $this->expectException('\InvalidArgumentException');
        $this->obj->choiceLabel(0);
    }

    /**
     * @return void
     */
    public function testParseChoices()
    {
        $choices = [
            'foo' => 'oof',
            'bar' => 'rab'
        ];
        $expected = [
            'foo' => [
                'value' => 'foo',
                'label' => $this->translation('oof')
            ],
            'bar' => [
                'value' => 'bar',
                'label' => $this->translation('rab')
            ]
        ];

        $parsed = $this->callMethod($this->obj, 'parseChoices', [ $choices ]);
        $this->assertEquals($expected, $parsed);

        $qux = [
            'value' => 'qux',
            'label' => $this->translation('xuq')
        ];

        $parsed = $this->callMethod($this->obj, 'parseChoice', [ 'xuq', 'qux' ]);
        $this->assertEquals($qux, $parsed);

        $parsed = $this->callMethod($this->obj, 'parseChoice', [ [ 'label' => 'xuq' ], 'qux' ]);
        $this->assertEquals($qux, $parsed);

        $parsed = $this->callMethod($this->obj, 'parseChoice', [ $qux, 'qux' ]);
        $this->assertEquals($qux, $parsed);

        $baz = [
            'value' => 'baz',
            'label' => $this->translation('baz')
        ];

        $parsed = $this->callMethod($this->obj, 'parseChoice', [ [ 'value' => 'baz' ], 'baz' ]);
        $this->assertEquals($baz, $parsed);
    }

    /**
     * @return void
     */
    public function testParseChoiceStructException()
    {
        $this->expectException('\InvalidArgumentException');
        $this->callMethod($this->obj, 'parseChoice', [ null, 'foo' ]);
    }

    /**
     * @return void
     */
    public function testParseChoiceKeyException()
    {
        $this->expectException('\InvalidArgumentException');
        $this->callMethod($this->obj, 'parseChoice', [ 'foo', 0 ]);
    }
}
