<?php

namespace Charcoal\Tests\Translator;

use Exception;
use ReflectionClass;

// From 'charcoal-translator'
use Charcoal\Translator\TranslatorAwareTrait;
use Charcoal\Translator\Translator;
use Charcoal\Tests\AbstractTestCase;

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
    public function setUp()
    {
        $this->obj = $this->getMockForTrait(TranslatorAwareTrait::class);
    }

    /**
     * @expectedException Exception
     *
     * @return void
     */
    public function testTranslatorWithoutSettingThrowsException()
    {
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
