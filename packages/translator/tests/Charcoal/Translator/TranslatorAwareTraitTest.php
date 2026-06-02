<?php

namespace Charcoal\Tests\Translator;

use Exception;
use ReflectionClass;

// From 'charcoal-translator'
use Charcoal\Translator\TranslatorAwareTrait;
use Charcoal\Translator\Translator;
use Charcoal\Tests\Translator\AbstractTestCase;

/**
 *
 */
class TranslatorAwareTraitTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private $obj;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $this->obj = new class () {
            use TranslatorAwareTrait;
        };
    }

    public function testTranslatorWithoutSettingThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->callMethod($this->obj, 'translator');
    }

    public function testSetTranslator(): void
    {
        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->callMethod($this->obj, 'setTranslator', [ $translator ]);
        $this->assertEquals($translator, $this->callMethod($this->obj, 'translator'));
    }
}
