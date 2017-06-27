<?php

namespace Charcoal\Tests\Translator;

use ReflectionClass;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From `charcoal-translator`
use Charcoal\Translator\TranslatorAwareTrait;

/**
 *
 */
class TranslatorAwareTraitTest extends PHPUnit_Framework_TestCase
{
    private $obj;

    public function setUp()
    {
        $this->obj = $this->getMockForTrait(TranslatorAwareTrait::class);
    }

    private function callMethod($obj, $name, array $args = [])
    {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method->invokeArgs($obj, $args);
    }

    public function testTranslatorWithoutSettingThrowsException()
    {
        $this->setExpectedException('\Exception');
        $this->callMethod($this->obj, 'translator');
    }

    public function testSetTranslator()
    {
        $translator = $this->getMockBuilder('\Charcoal\Translator\Translator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->callMethod($this->obj, 'setTranslator', [$translator]);
        $this->assertEquals($translator, $this->callMethod($this->obj, 'translator'));
    }
}
