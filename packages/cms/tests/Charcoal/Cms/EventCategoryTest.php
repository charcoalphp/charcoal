<?php

namespace Charcoal\Cms\Tests;

// From 'charcoal-cms'
use Charcoal\Cms\EventCategory;
use Charcoal\Cms\Event;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Cms\ContainerIntegrationTrait;

/**
 *
 */
class EventCategoryTest extends AbstractTestCase
{
    use ContainerIntegrationTrait;

    /**
     * Tested Class.
     */
    private \Charcoal\Cms\EventCategory $obj;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $dependencies = $this->getModelDependenciesWithContainer();

        $this->obj = new EventCategory($dependencies);
    }

    public function testItemType(): void
    {
        $this->assertEquals(Event::class, $this->obj->itemType());
    }

    public function testValidate(): void
    {
        $this->assertFalse($this->obj->validate());
        $this->obj->setName([ 'fr' => 'Titre' ]);
        $this->assertFalse($this->obj->validate());
        $this->obj->setName([ 'fr' => 'Titre', 'en' => 'Title' ]);
        $this->assertTrue($this->obj->validate());
    }
}
