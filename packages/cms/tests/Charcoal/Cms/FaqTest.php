<?php

namespace Charcoal\Cms\Tests;

// From 'charcoal-cms'
use Charcoal\Cms\Faq;
use Charcoal\Cms\FaqCategory;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Cms\ContainerIntegrationTrait;

/**
 *
 */
class FaqTest extends AbstractTestCase
{
    use ContainerIntegrationTrait;

    /**
     * Tested Class.
     */
    private \Charcoal\Cms\Faq $obj;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $dependencies = $this->getModelDependenciesWithContainer();

        $this->obj = new Faq($dependencies);
    }

    public function testSetData(): void
    {
        $ret = $this->obj->setData([
            'question' => 'Foo?',
            'answer'   => 'Bar',
        ]);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Foo?', (string)$this->obj->question());
        $this->assertEquals('Bar', (string)$this->obj->answer());
    }

    public function testSetQuestion(): void
    {
        $ret = $this->obj->setQuestion('Foo?');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Foo?', $this->obj->question());
    }

    public function testSetAnswer(): void
    {
        $ret = $this->obj->setAnswer('Bar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Bar', $this->obj->answer());
    }

    public function testCategoryType(): void
    {
        $this->assertEquals(FaqCategory::class, $this->obj->categoryType());
    }
}
