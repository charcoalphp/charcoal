<?php

namespace Charcoal\Tests\Admin\Widget;

// From PSR-3
use Psr\Log\NullLogger;

// From Pimple
use Pimple\Container;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\TextWidget;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Admin\ContainerProvider;

/**
 *
 */
class TextWidgetTest extends AbstractTestCase
{
    public $obj;
    public function setUp(): void
    {
        $container = new Container();
        $containerProvider = new ContainerProvider();
        $containerProvider->registerWidgetDependencies($container);

        $this->obj = new TextWidget([
            'logger' => $container['logger'],
            'container' => $container
        ]);
    }

    public function testSetShowTitle(): void
    {
        $this->assertFalse($this->obj->showTitle());
        $ret = $this->obj->setShowTitle(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showTitle());

        $this->obj->setShowTitle(true);
        $this->obj->setTitle('foo');
        $this->assertTrue($this->obj->showTitle());
    }

    public function testSetShowSubtitle(): void
    {
        $this->assertFalse($this->obj->showSubtitle());
        $ret = $this->obj->setShowSubtitle(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showSubtitle());

        $this->obj->setShowSubtitle(true);
        $this->obj->setSubtitle('foo');
        $this->assertTrue($this->obj->showSubtitle());
    }

    public function testSetShowDescription(): void
    {
        $this->assertFalse($this->obj->showDescription());
        $ret = $this->obj->setShowDescription(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showDescription());

        $this->obj->setShowDescription(true);
        $this->obj->setDescription('foo');
        $this->assertTrue($this->obj->showDescription());
    }

    public function testSetShowNotes(): void
    {
        $this->assertFalse($this->obj->showNotes());
        $ret = $this->obj->setShowNotes(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showNotes());

        $this->obj->setShowNotes(true);
        $this->obj->setNotes('foo');
        $this->assertTrue($this->obj->showNotes());
    }

    public function testSetTitle(): void
    {
        $ret = $this->obj->setTitle('Fôö title');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Fôö title', (string)$this->obj->title());
    }

    public function testSetSubtitle(): void
    {
        $ret = $this->obj->setSubtitle('Fôö subtitle');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Fôö subtitle', (string)$this->obj->subtitle());
    }

    public function testSetDescription(): void
    {
        $ret = $this->obj->setDescription('Fôö description');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Fôö description', (string)$this->obj->description());
    }

    public function testSetNotes(): void
    {
        $ret = $this->obj->setNotes('Fôö notes');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Fôö notes', (string)$this->obj->notes());
    }
}
