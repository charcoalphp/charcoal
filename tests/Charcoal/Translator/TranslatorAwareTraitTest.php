<?php

namespace Charcoal\Tests\Translator;

use Exception;
use ReflectionClass;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From 'charcoal-translator'
use Charcoal\Translator\TranslatorAwareTrait;
use Charcoal\Translator\Translator;

/**
 *
 */
class TranslatorAwareTraitTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var TranslatorAwareTrait
     */
    private $obj;

    /**
     * Set up the test.
     */
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
        $this->setExpectedException(Exception::class);
        $this->callMethod($this->obj, 'translator');
    }

    public function testSetTranslator()
    {
        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->callMethod($this->obj, 'setTranslator', [$translator]);
        $this->assertEquals($translator, $this->callMethod($this->obj, 'translator'));
    }
}
