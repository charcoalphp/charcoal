<?php

namespace Charcoal\Cms\Tests;

use DateTime;

// From 'charcoal-object'
use Charcoal\Object\ObjectRoute;

// From 'charcoal-cms'
use Charcoal\Cms\Event;
use Charcoal\Cms\EventCategory;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Cms\ContainerIntegrationTrait;

/**
 *
 */
class EventTest extends AbstractTestCase
{
    use ContainerIntegrationTrait;

    /**
     * Tested Class.
     */
    private \Charcoal\Cms\Event|array $obj;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $container = $this->getContainer();
        $route = $container['model/factory']->create(ObjectRoute::class);

        if ($route->source()->tableExists() === false) {
            $route->source()->createTable();
        }

        $dependencies = $this->getModelDependenciesWithContainer();

        $this->obj = new Event($dependencies);
    }

    public function testSetData(): void
    {
        $ret = $this->obj->setData([
            'title'            => 'Example title',
            'subtitle'         => 'Subtitle',
            'summary'          => 'Summary <p>yeah</p>',
            'content'          => 'foobar',
            'image'            => 'foo.png',
            'start_date'       => '2015-01-01 20:00:00',
            'end_date'         => '2015-01-01 21:30:00',
            'info_url'         => 'https://example.com/event',
            'info_phone'       => '514 555-1212',
            'ticket_price_min' => 25,
            'ticket_price_max' => 50,
            'ticket_summary'   => 'Infos ticket',
            'ticket_url'       => 'https://example.com/tickets',
            'ticket_phone'     => '1-555-555-1234',
        ]);

        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Example title', (string)$this->obj->title());
        $this->assertEquals('Subtitle', (string)$this->obj->subtitle());
        $this->assertEquals('foobar', (string)$this->obj->content());
        $this->assertEquals(new DateTime('2015-01-01 20:00:00'), $this->obj->startDate());
        $this->assertEquals(new DateTime('2015-01-01 21:30:00'), $this->obj->endDate());
        $this->assertEquals('https://example.com/event', $this->obj->infoUrl());
        $this->assertEquals('514 555-1212', $this->obj->infoPhone());
        $this->assertEquals(25, $this->obj->ticketPriceMin());
        $this->assertEquals(50, $this->obj->ticketPriceMax());
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
        $ret = $this->obj->setSummary('Bar foo baz');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Bar foo baz', (string)$this->obj->summary());

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

    public function testSetStartDate(): void
    {
        $this->assertEquals(null, $this->obj->startDate());
        $ret = $this->obj->setStartDate('2016-02-02');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(new DateTime('2016-02-02'), $ret->startDate());

        $this->obj->setStartDate(null);
        $this->assertEquals(null, $this->obj->startDate());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setStartDate([]);
    }

    public function testSetStartDateInvalidString(): void
    {
        $this->expectException('\Exception');
        $this->obj->setStartDate('foo.bar');
    }

    public function testSetEndDate(): void
    {
        $this->assertEquals(null, $this->obj->endDate());
        $ret = $this->obj->setEndDate('2016-02-02');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(new DateTime('2016-02-02'), $ret->endDate());

        $this->obj->setEndDate(null);
        $this->assertEquals(null, $this->obj->endDate());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setEndDate([]);
    }

    public function testSetEndDateInvalidString(): void
    {
        $this->expectException('\Exception');
        $this->obj->setEndDate('foo.bar');
    }

    public function testCategoryType(): void
    {
        $this->assertEquals(EventCategory::class, $this->obj->categoryType());
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
    public function testSaveGeneratesSlug(): void
    {
        $this->assertEquals('', $this->obj['slug']);
        $this->obj->setData([
            'title' => 'foo',
        ]);
        $this->obj->save();

        $this->assertEquals('en/events/foo', (string)$this->obj['slug']);
    }

    public function testUpdateGeneratesSlug(): void
    {
        $this->assertEquals('', $this->obj['slug']);
        $this->obj->setData([
            'title' => 'foo',
        ]);
        $this->obj->update();

        $this->assertEquals('en/events/foo', (string)$this->obj['slug']);
    }
}
