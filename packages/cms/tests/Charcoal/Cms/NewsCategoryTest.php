<?php

namespace Charcoal\Cms\Tests;

// From 'charcoal-cms'
use Charcoal\Cms\NewsCategory;
use Charcoal\Cms\News;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Cms\ContainerIntegrationTrait;

/**
 *
 */
class NewsCategoryTest extends AbstractTestCase
{
    use ContainerIntegrationTrait;

    /**
     * Tested Class.
     */
    private \Charcoal\Cms\NewsCategory $obj;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $dependencies = $this->getModelDependenciesWithContainer();

        $this->obj = new NewsCategory($dependencies);
    }

    public function testItemType(): void
    {
        $this->assertEquals(News::class, $this->obj->itemType());
    }
}
