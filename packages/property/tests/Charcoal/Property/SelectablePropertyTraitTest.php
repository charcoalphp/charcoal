<?php

namespace Charcoal\Tests\Property;

use Charcoal\Tests\Property\Mocks\SelectablePropertyTestDouble;
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
     */
    private SelectablePropertyTestDouble $obj;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->obj = $this->createPartialMock(SelectablePropertyTestDouble::class, ['translator']);
        $this->obj->method('translator')
                  ->willReturn($container['translator']);
    }

    /**
     * @param  mixed $val The translation string.
     */
    public function translation($val): \Charcoal\Translator\Translation
    {
        $container = $this->getContainer();
        $locales   = $container['locales/manager'];

        return new Translation($val, $locales);
    }

    public function testEmptyChoices(): void
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

    public function testChoices(): void
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

    public function testChoiceLabelStructException(): void
    {
        $this->expectException('\InvalidArgumentException');
        $this->obj->choiceLabel([]);
    }

    public function testChoiceLabelKeyException(): void
    {
        $this->expectException('\InvalidArgumentException');
        $this->obj->choiceLabel(0);
    }

    public function testParseChoices(): void
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

    public function testParseChoiceStructException(): void
    {
        $this->expectException('\InvalidArgumentException');
        $this->callMethod($this->obj, 'parseChoice', [ null, 'foo' ]);
    }

    public function testParseChoiceKeyException(): void
    {
        $this->expectException('\InvalidArgumentException');
        $this->callMethod($this->obj, 'parseChoice', [ 'foo', 0 ]);
    }
}
