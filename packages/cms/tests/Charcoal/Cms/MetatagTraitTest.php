<?php

namespace Charcoal\Tests\Cms;

// From 'charcoal-object'
use Charcoal\Object\ObjectRoute;

// From 'charcoal-object'
use Charcoal\Cms\MetatagInterface;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Cms\ContainerIntegrationTrait;
use Charcoal\Tests\Cms\Mock\WebPage;

/**
 *
 */
class MetatagTraitTest extends AbstractTestCase
{
    use ContainerIntegrationTrait;

    /**
     * @var WebPage
     */
    public $obj;

    protected function setUp(): void
    {
        $container = $this->getContainer();

        $route = $container['model/factory']->get(ObjectRoute::class);
        if ($route->source()->tableExists() === false) {
            $route->source()->createTable();
        }

        $dependencies = $this->getModelDependenciesWithContainer();

        $this->obj = new WebPage($dependencies);
    }

    /**
     * Asserts that the object implements MetatagInterface.
     */
    #[\PHPUnit\Framework\Attributes\CoversNothing]
    public function testMetatagInterface(): void
    {
        $this->assertInstanceOf(MetatagInterface::class, $this->obj);
    }

    public function testSaveGeneratesMetaTags(): void
    {
        $this->assertEquals('', (string)$this->obj->metaTitle());
        $this->assertEquals('', (string)$this->obj->metaDescription());
        $this->assertEquals('', (string)$this->obj->metaImage());

        $this->obj->setData([
            'title'   => 'foo',
            'content' => '<p>Foo bar</p>',
            'image'   => 'x.jpg',
        ]);
        $this->obj->save();

        $this->assertEquals('foo', (string)$this->obj->metaTitle());
        $this->assertEquals('Foo bar', (string)$this->obj->metaDescription());
        $this->assertEquals('x.jpg', (string)$this->obj->metaImage());
    }

    public function testUpdateGeneratesMetaTags(): void
    {
        $this->assertEquals('', (string)$this->obj->metaTitle());
        $this->assertEquals('', (string)$this->obj->metaDescription());
        $this->assertEquals('', (string)$this->obj->metaImage());

        $this->obj->setData([
            'title'   => 'foo',
            'content' => '<p>Foo bar</p>',
            'image'   => 'x.jpg',
        ]);
        $this->obj->update();

        $this->assertEquals('foo', (string)$this->obj->metaTitle());
        $this->assertEquals('Foo bar', (string)$this->obj->metaDescription());
        $this->assertEquals('x.jpg', (string)$this->obj->metaImage());
    }
}
