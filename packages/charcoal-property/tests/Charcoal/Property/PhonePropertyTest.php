<?php

namespace Charcoal\Tests\Property;

// From 'charcoal-property'
use Charcoal\Property\PhoneProperty;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class PhonePropertyTest extends AbstractTestCase
{
    use \Charcoal\Tests\Property\ContainerIntegrationTrait;

    /**
     * @var PhoneProperty
     */
    public $obj;

    /**
     * @return void
     */
    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = new PhoneProperty([
            'database'   => $container['database'],
            'logger'     => $container['logger'],
            'translator' => $container['translator']
        ]);
    }

    /**
     * Hello world
     *
     * @return void
     */
    public function testDefaultValues()
    {
        $this->assertEquals(0, $this->obj['minLength']);
        $this->assertEquals(16, $this->obj['maxLength']);
    }

    /**
     * @return void
     */
    public function testType()
    {
        $this->assertEquals('phone', $this->obj->type());
    }

    /**
     * @return void
     */
    public function testSanitize()
    {
        $this->assertEquals('5145551234', $this->obj->sanitize('(514) 555-1234'));
    }

    /**
     * @return void
     */
    public function testDisplayVal()
    {
        $this->assertEquals('(514) 555-1234', $this->obj->displayVal('5145551234'));
        $this->assertEquals('(514) 555-1234', $this->obj->displayVal('514-555-1234'));
    }
}
