<?php

declare(strict_types=1);

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

    protected function setUp(): void
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
     */
    public function testDefaultValues(): void
    {
        $this->assertEquals(0, $this->obj['minLength']);
        $this->assertEquals(16, $this->obj['maxLength']);
    }

    public function testType(): void
    {
        $this->assertEquals('phone', $this->obj->type());
    }

    public function testSanitize(): void
    {
        $this->assertEquals('5145551234', $this->obj->sanitize('(514) 555-1234'));
    }

    public function testDisplayVal(): void
    {
        $this->assertEquals('(514) 555-1234', $this->obj->displayVal('5145551234'));
        $this->assertEquals('(514) 555-1234', $this->obj->displayVal('514-555-1234'));
    }
}
