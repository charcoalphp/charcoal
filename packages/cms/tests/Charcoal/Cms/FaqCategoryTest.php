<?php

namespace Charcoal\Cms\Tests;

// From 'charcoal-cms'
use Charcoal\Cms\FaqCategory;
use Charcoal\Cms\Faq;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Cms\ContainerIntegrationTrait;

/**
 *
 */
class FaqCategoryTest extends AbstractTestCase
{
    use ContainerIntegrationTrait;

    /**
     * Tested Class.
     */
    private \Charcoal\Cms\FaqCategory $obj;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $dependencies = $this->getModelDependenciesWithContainer();

        $this->obj = new FaqCategory($dependencies);
    }

    public function testItemType(): void
    {
        $this->assertEquals(Faq::class, $this->obj->itemType());
    }
}
