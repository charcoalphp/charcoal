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
     *
     * @var TranslatorAwareTrait
     */
    private $obj;

    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->obj = $this->getMockForTrait(TranslatorAwareTrait::class);
    }

    /**
     * @return void
     */
    public function testTranslatorWithoutSettingThrowsException()
    {
        $this->expectException(Exception::class);
        $this->callMethod($this->obj, 'translator');
    }

    /**
     * @return void
     */
    public function testSetTranslator()
    {
        $translator = $this->getMockBuilder(Translator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->callMethod($this->obj, 'setTranslator', [ $translator ]);
        $this->assertEquals($translator, $this->callMethod($this->obj, 'translator'));
    }
}
