<?php

namespace Charcoal\Tests\Property;

use ReflectionClass;

// From 'charcoal-translator'
use Charcoal\Translator\Translation;

// From 'charcoal-property'
use Charcoal\Property\SelectablePropertyTrait;

/**
 * Selectable Property Test
 */
class SelectablePropertyTraitTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * Tested Class.
     *
     * @var SelectablePropertyTrait
     */
    private $obj;

    /**
     * Set up the test.
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = $this->getMockForTrait(SelectablePropertyTrait::class);
        $this->obj->expects($this->any())
                  ->method('translator')
                  ->will($this->returnValue($container['translator']));
    }

    public function translation($val)
    {
        $container = $this->getContainer();
        $locales   = $container['language/manager'];

        return new Translation($val, $locales);
    }

    public static function getMethod($obj, $name)
    {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }

    public static function callMethod($obj, $name, array $args = [])
    {
        $method = static::getMethod($obj, $name);

        return $method->invokeArgs($obj, $args);
    }

    public function testEmptyChoices()
    {
        $this->assertEquals([], $this->obj->choices());

        $this->assertFalse($this->obj->hasChoices());
        $this->assertFalse($this->obj->hasChoice('foo'));

        $this->assertEquals([ 'value' => 'foo', 'label' => '' ], $this->obj->choice('foo'));

        $this->assertNull($this->obj->choiceLabel(null));

        $this->assertEquals('xuq', $this->obj->choiceLabel([ 'value' => 'qux', 'label' => 'xuq' ]));
        $this->assertEquals('qux', $this->obj->choiceLabel([ 'value' => 'qux', 'label' => null ]));
        $this->assertEquals('',    $this->obj->choiceLabel([ 'value' => 'qux', 'label' => '' ]));
        $this->assertEquals('qux', $this->obj->choiceLabel([ 'value' => 'qux' ]));

        $this->assertEquals('qux', $this->obj->choiceLabel('qux'));
    }

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

    public function testChoiceLabelStructException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->choiceLabel([]);
    }

    public function testChoiceLabelKeyException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->choiceLabel(0);
    }

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

        $parsed = static::callMethod($this->obj, 'parseChoices', [ $choices ]);
        $this->assertEquals($expected, $parsed);

        $qux = [
            'value' => 'qux',
            'label' => $this->translation('xuq')
        ];

        $parsed = static::callMethod($this->obj, 'parseChoice', [ 'xuq', 'qux' ]);
        $this->assertEquals($qux, $parsed);

        $parsed = static::callMethod($this->obj, 'parseChoice', [ [ 'label' => 'xuq' ], 'qux' ]);
        $this->assertEquals($qux, $parsed);

        $parsed = static::callMethod($this->obj, 'parseChoice', [ $qux, 'qux' ]);
        $this->assertEquals($qux, $parsed);

        $baz = [
            'value' => 'baz',
            'label' => $this->translation('baz')
        ];

        $parsed = static::callMethod($this->obj, 'parseChoice', [ [ 'value' => 'baz' ], 'baz' ]);
        $this->assertEquals($baz, $parsed);

    }

    public function testParseChoiceStructException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        static::callMethod($this->obj, 'parseChoice', [ null, 'foo' ]);
    }

    public function testParseChoiceKeyException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        static::callMethod($this->obj, 'parseChoice', [ 'foo', 0 ]);
    }
}
