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
     *
     * @var NewsCategory
     */
    private $obj;

    /**
     * Set up the test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $dependencies = $this->getModelDependenciesWithContainer();

        $this->obj = new NewsCategory($dependencies);
    }

    /**
     * @return void
     */
    public function testItemType()
    {
        $this->assertEquals(News::class, $this->obj->itemType());
    }
}
