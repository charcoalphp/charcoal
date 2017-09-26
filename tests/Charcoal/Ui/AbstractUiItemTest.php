<?php

namespace Charcoal\Tests\Ui;

use ReflectionClass;

// From 'charcoal-ui'
use Charcoal\Ui\AbstractUiItem;

/**
 *
 */
class AbstractUiItemTest extends \PHPUnit_Framework_TestCase
{
    use \Charcoal\Tests\Ui\ContainerIntegrationTrait;

    public $obj;

    public function setUp()
    {
        $container = $this->getContainer();

        $this->obj = $this->getMockForAbstractClass(AbstractUiItem::class, [[
            'container' => $container
        ]]);

        $setAuthDependencies = self::getMethod($this->obj, 'setAuthDependencies');
        $setAuthDependencies->invoke($this->obj, $container);
    }

    public static function getMethod($obj, $name)
    {
        $class = new ReflectionClass($obj);
        $method = $class->getMethod($name);
        $method->setAccessible(true);
        return $method;
    }

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


    public function testSetType()
    {
        $ret = $this->obj->setType('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->type());
    }

    public function testSetAcive()
    {
        $ret = $this->obj->setActive(false);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(false, $this->obj->active());
    }

    public function testSetPriority()
    {
        $ret = $this->obj->setPriority(42);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(42, $this->obj->priority());
    }

    public function testSetTemplate()
    {
        $ret = $this->obj->setTemplate('foo/bar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo/bar', $this->obj->template());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setTemplate(false);
    }

    public function testNoTemplateReturnsType()
    {
        $ret = $this->obj->setType('foobar/baz');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar/baz', $this->obj->template());
    }

    public function testSetTitle()
    {
        $ret = $this->obj->setTitle('Hello');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Hello', (string)$this->obj->title());
    }

    public function testSetSubtitle()
    {
        $ret = $this->obj->setSubtitle('Hello');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Hello', (string)$this->obj->subtitle());
    }

    public function testSetDescription()
    {
        $ret = $this->obj->setDescription('Hello');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Hello', (string)$this->obj->description());
    }

    public function testSetNotes()
    {
        $ret = $this->obj->setNotes('Hello');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('Hello', (string)$this->obj->notes());
    }

    public function testShowTitle()
    {
        $this->assertFalse($this->obj->showTitle());
        $this->obj->setTitle('Foo');
        $this->assertTrue($this->obj->showTitle());
        $ret = $this->obj->setShowTitle(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showTitle());
    }

    public function testShowSubtitle()
    {
        $this->assertFalse($this->obj->showSubtitle());
        $this->obj->setSubtitle('Foo');
        $this->assertTrue($this->obj->showSubtitle());
        $ret = $this->obj->setShowSubtitle(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showSubtitle());
    }

    public function testShowDescription()
    {
        $this->assertFalse($this->obj->showDescription());
        $this->obj->setDescription('Foo');
        $this->assertTrue($this->obj->showDescription());
        $ret = $this->obj->setShowDescription(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->showDescription());
    }

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
