<?php

namespace Charcoal\Cms\Tests;

use DateTime;

// From 'charcoal-object'
use Charcoal\Object\ObjectRoute;

// From 'charcoal-cms'
use Charcoal\Cms\News;
use Charcoal\Cms\NewsCategory;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Cms\ContainerIntegrationTrait;

/**
 *
 */
class NewsTest extends AbstractTestCase
{
    use ContainerIntegrationTrait;

    /**
     * Tested Class.
     */
    private \Charcoal\Cms\News|array $obj;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $container = $this->getContainer();

        $route = $container['model/factory']->get(ObjectRoute::class);
        if ($route->source()->tableExists() === false) {
            $route->source()->createTable();
        }

        $dependencies = $this->getModelDependenciesWithContainer();

        $this->obj = new News($dependencies);
    }

    public function testSetData(): void
    {
        $ret = $this->obj->setData([
            'title'     => 'Example title',
            'subtitle'  => 'Subtitle',
            'content'   => 'foobar',
            'news_date' => '2015-01-01 20:00:00',
        ]);

        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Example title', (string)$this->obj->title());
        $this->assertEquals('Subtitle', (string)$this->obj->subtitle());
        $this->assertEquals('foobar', (string)$this->obj->content());
        $this->assertEquals(new DateTime('2015-01-01 20:00:00'), $this->obj->newsDate());
    }

    public function testSetTitle(): void
    {
        $this->assertEquals('', (string)$this->obj->title());
        $ret = $this->obj->setTitle('Foo bar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Foo bar', (string)$this->obj->title());

        $this->obj['title'] = 'Bar';
        $this->assertEquals('Bar', (string)$this->obj->title());

        $this->obj->set('title', 'Hello');
        $this->assertEquals('Hello', (string)$this->obj['title']);
    }

    public function testSetSubtitle(): void
    {
        $this->assertEquals('', (string)$this->obj->subtitle());
        $ret = $this->obj->setSubtitle('Bar foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Bar foo', (string)$this->obj->subtitle());

        $this->obj['subtitle'] = 'Foobar';
        $this->assertEquals('Foobar', (string)$this->obj->subtitle());

        $this->obj->set('subtitle', 'foo');
        $this->assertEquals('foo', (string)$this->obj['subtitle']);
    }

    public function testSetSummary(): void
    {
        $this->assertEquals('', (string)$this->obj->summary());
        $ret = $this->obj->setSummary('Bar foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Bar foo', (string)$this->obj->summary());

        $this->obj['summary'] = 'Foobar';
        $this->assertEquals('Foobar', (string)$this->obj->summary());

        $this->obj->set('summary', 'foo');
        $this->assertEquals('foo', (string)$this->obj['summary']);
    }

    public function testSetContent(): void
    {
        $this->assertEquals('', (string)$this->obj->content());
        $ret = $this->obj->setContent('Bar foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Bar foo', (string)$this->obj->content());

        $this->obj['content'] = 'Foobar';
        $this->assertEquals('Foobar', (string)$this->obj->content());

        $this->obj->set('content', 'foo');
        $this->assertEquals('foo', (string)$this->obj['content']);
    }

    public function testSetNewsDate(): void
    {
        $this->assertEquals(null, $this->obj->newsDate());
        $ret = $this->obj->setNewsDate('2016-02-02');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(new DateTime('2016-02-02'), $ret->newsDate());

        $this->obj->setNewsDate(null);
        $this->assertEquals(null, $this->obj->newsDate());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setNewsDate([]);
    }

    public function testSetNewsDateInvalidString(): void
    {
        $this->expectException('\Exception');
        $this->obj->setNewsDate('foo.bar');
    }

    public function testMetaTitleDefaultsToTitle(): void
    {
        $this->assertEquals('', (string)$this->obj->metaTitle());

        $this->obj->setTitle('Foo Bar');
        $this->obj->generateDefaultMetaTags();
        $this->assertEquals('Foo Bar', (string)$this->obj->metaTitle());

        $this->obj->setMetaTitle('Barfoo');
        $this->assertEquals('Barfoo', (string)$this->obj->metaTitle());
    }

    public function testMetaDescriptionDefaultsToDescription(): void
    {
        $this->assertEquals('', (string)$this->obj->metaDescription());

        $this->obj->setContent('Foo Bar');
        $this->obj->generateDefaultMetaTags();
        $this->assertEquals('Foo Bar', (string)$this->obj->metaDescription());

        $this->obj->setMetaDescription('Barfoo');
        $this->assertEquals('Barfoo', (string)$this->obj->metaDescription());
    }

    /*
    public function testMetaImageDefaultsToImage()
    {
        $this->assertEquals('', (string)$this->obj->metaImage());
    
        $this->obj->setImage('Foo.png');
        $this->assertSame($this->obj->image(), $this->obj->metaImage());
        $this->assertEquals('Foo.png', (string)$this->obj->metaImage());
    
        $this->obj->setMetaImage('Bar.jpg');
        $this->assertEquals('Bar.jpg', (string)$this->obj->metaImage());
    }
    */
    public function testCategoryType(): void
    {
        $this->assertEquals(NewsCategory::class, $this->obj->categoryType());
    }

    /*
    public function testSave()
    {
        $this->obj->save();
    }
    */
    public function testSaveGeneratesSlug(): void
    {
        $this->assertEquals('', $this->obj['slug']);
        $this->obj->setData([
            'title' => 'foo',
        ]);
        $this->obj->save();

        $this->assertEquals('en/news/foo', (string)$this->obj['slug']);
    }

    public function testUpdateGeneratesSlug(): void
    {
        $this->assertEquals('', $this->obj['slug']);
        $this->obj->setData([
            'title' => 'foo',
        ]);
        $this->obj->update();

        $this->assertEquals('en/news/foo', (string)$this->obj['slug']);
    }
}
