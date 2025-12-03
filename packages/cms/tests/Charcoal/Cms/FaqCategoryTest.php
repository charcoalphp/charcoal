<?php

namespace Charcoal\Cms\Tests;

// From 'charcoal-cms'
use Charcoal\Cms\FaqCategory;
use Charcoal\Cms\Faq;
use Charcoal\Tests\Cms\ContainerIntegrationTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use Charcoal\Tests\Cms\AbstractCmsTestCase;

#[CoversClass(FaqCategory::class)]
class FaqCategoryTest extends AbstractCmsTestCase
{
    use ContainerIntegrationTrait;

    /**
     * Tested Class.
     *
     * @var FaqCategory
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

        $this->obj = new FaqCategory($dependencies);
    }

    /**
     * @return void
     */
    public function testItemType()
    {
        $this->assertEquals(Faq::class, $this->obj->itemType());
    }
}
