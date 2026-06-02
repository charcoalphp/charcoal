<?php

namespace Charcoal\Tests\Admin\Widget;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-admin'
use Charcoal\Admin\Widget\CollectionMapWidget;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class CollectionMapWidgetTest extends AbstractTestCase
{
    /**
     * @var CollectionMapWidget
     */
    public $obj;

    public function setUp(): void
    {
        $logger = new NullLogger();
        $this->obj = new CollectionMapWidget([
            'logger' => $logger
        ]);
    }

    public function testSetLatProperty(): void
    {
        $ret = $this->obj->setLatProperty('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->latProperty());
    }

    public function testSetLonProperty(): void
    {
        $ret = $this->obj->setLonProperty('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->lonProperty());
    }

    public function testSetPolygonProperty(): void
    {
        $ret = $this->obj->setPolygonProperty('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->polygonProperty());
    }

    public function testSetInfoboxTemplate(): void
    {
        $ret = $this->obj->setInfoboxTemplate('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->infoboxTemplate());
    }
}
