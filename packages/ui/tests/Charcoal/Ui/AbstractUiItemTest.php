<?php

namespace Charcoal\Tests\Ui;

use ReflectionMethod;

// From 'charcoal-ui'
use Charcoal\Ui\AbstractUiItem;
use Charcoal\Tests\AbstractTestCase;

/**
 *
 */
class AbstractUiItemTest extends AbstractTestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    /**
     * @var AbstractUiItem
     */
    public $obj;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $container = $this->getContainer();

        $this->obj = $this->getMockForAbstractClass(AbstractUiItem::class, [[
            'container' => $container
        ]]);

        $method = new ReflectionMethod($this->obj, 'setAuthDependencies');
        $method->setAccessible(true);
        $method->invoke($this->obj, $container);
    }

    /**
     * @return void
     */
    public function testDefaults()
    {
        $this->assertTrue($this->obj->active());
        $this->assertEquals(0, $this->obj->priority());
        //$this->assertEquals(AbstractUiItem::class, $this->obj->template());
        //$this->assertEquals(AbstractUiItem::class, $this->obj->type());
        $this->assertNull($this->obj->icon());
        $this->assertEquals('', $this->obj->title());
        $this->assertEquals('', $this->obj->subtitle());
        $this->assertEquals('', $this->obj->description());
        $this->assertEquals('', $this->obj->notes());
    }

    /**
     * @return void
     */
    public function testSetType()
    {
        $ret = $this->obj->setType('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->type());
    }

    /**
     * @return void
     */
    public function testSetAcive()
    {
        $ret = $this->obj->setActive(false);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(false, $this->obj->active());
    }

    /**
     * @return void
     */
    public function testSetPriority()
    {
        $ret = $this->obj->setPriority(42);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(42, $this->obj->priority());
    }

    /**
     * @return void
     */
    public function testSetTemplate()
    {
        $ret = $this->obj->setTemplate('foo/bar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo/bar', $this->obj->template());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setTemplate(false);
    }

    /**
     * @return void
     */
    public function testNoTemplateReturnsType()
    {
        $ret = $this->obj->setType('foobar/baz');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar/baz', $this->obj->template());
    }

    /**
     * @return void
     */
    public function testSetTitle()
    {
        $ret = $this->obj->setTitle('Hello');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Hello', (string)$this->obj->title());
    }

    /**
     * @return void
     */
    public function testSetSubtitle()
    {
        $ret = $this->obj->setSubtitle('Hello');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Hello', (string)$this->obj->subtitle());
    }

    /**
     * @return void
     */
    public function testSetDescription()
    {
        $ret = $this->obj->setDescription('Hello');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Hello', (string)$this->obj->description());
    }

    /**
     * @return void
     */
    public function testSetNotes()
    {
        $ret = $this->obj->setNotes('Hello');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Hello', (string)$this->obj->notes());
    }

    /**
     * @return void
     */
    public function testShowTitle()
    {
        $this->assertFalse($this->obj->showTitle());
        $this->obj->setTitle('Foo');
        $this->assertTrue($this->obj->showTitle());
        $ret = $this->obj->setShowTitle(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showTitle());
    }

    /**
     * @return void
     */
    public function testShowSubtitle()
    {
        $this->assertFalse($this->obj->showSubtitle());
        $this->obj->setSubtitle('Foo');
        $this->assertTrue($this->obj->showSubtitle());
        $ret = $this->obj->setShowSubtitle(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showSubtitle());
    }

    /**
     * @return void
     */
    public function testShowDescription()
    {
        $this->assertFalse($this->obj->showDescription());
        $this->obj->setDescription('Foo');
        $this->assertTrue($this->obj->showDescription());
        $ret = $this->obj->setShowDescription(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showDescription());
    }

    /**
     * @return void
     */
    public function testShowNotes()
    {
        $this->assertFalse($this->obj->showNotes());
        $this->obj->setNotes('Foo');
        $this->assertTrue($this->obj->showNotes());
        $ret = $this->obj->setShowNotes(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showNotes());
    }
}
