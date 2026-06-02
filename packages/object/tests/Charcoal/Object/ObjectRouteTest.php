<?php

namespace Charcoal\Tests\Object;

use DateTime;

// From Pimple
use Pimple\Container;

// From 'charcoal-object'
use Charcoal\Object\ObjectRoute;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\Object\ContainerProvider;

/**
 *
 */
class ObjectRouteTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\Object\ObjectRoute $obj;

    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    /**
     * Set up the test.
     */
    public function setUp(): void
    {
        $container = $this->container();

        $this->obj = $container['model/factory']->create(ObjectRoute::class);
    }

    public function testDefaults(): void
    {
        $this->assertNull($this->obj['id']);
    }

    public function testSetData(): void
    {
        $ret = $this->obj->setData([
            'id' => 42,
            'creationDate' => 'today',
            'last_modification_date' => 'today',
            'lang' => 'es',
            'slug' => 'foobar',
            'route_obj_type' => 'foo',
            'route_obj_id' => 3,
            'route_template' => 'baz'
        ]);

        $this->assertSame($ret, $this->obj);

        $this->assertEquals(42, $this->obj['id']);

        $expected = new DateTime('today');
        $this->assertEquals($expected, $this->obj->getCreationDate());
        $this->assertEquals($expected, $this->obj->getLastModificationDate());

        $this->assertEquals('es', $this->obj->getLang());
        $this->assertEquals('foobar', $this->obj->getSlug());
        $this->assertEquals('foo', $this->obj->getRouteObjType());
        $this->assertEquals(3, $this->obj->getRouteObjId());
        $this->assertEquals('baz', $this->obj->getRouteTemplate());
    }

    public function testSetId(): void
    {
        $ret = $this->obj->setId(3);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(3, $this->obj['id']);

        $this->obj['id'] = 42;
        $this->assertEquals(42, $this->obj['id']);

        $this->obj->set('id', 10);
        $this->assertEquals(10, $this->obj['id']);
    }

    public function testSetCreationDate(): void
    {
        $this->assertNull($this->obj->getCreationDate());
    }

    public function testLastModificationDate(): void
    {
        $date = $this->obj->getLastModificationDate();
        $this->obj->update();
        $date2 = $this->obj->getLastModificationDate();

        $this->assertIsBool($date2 > $date);
    }

    public function testLang(): void
    {
        $ret = $this->obj->setLang('en');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('en', $this->obj['lang']);

        $this->obj['lang'] = 'fr';
        $this->assertEquals('fr', $this->obj['lang']);

        $this->obj->set('lang', 'jp');
        $this->assertEquals('jp', $this->obj['lang']);
    }

    public function testSetSlug(): void
    {
        $this->assertNull($this->obj['slug']);
        $ret = $this->obj->setSlug('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj['slug']);

        $this->obj['slug'] = 'foobar';
        $this->assertEquals('foobar', $this->obj['slug']);

        $this->obj->set('slug', 'bar');
        $this->assertEquals('bar', $this->obj['slug']);

        $this->obj['slug'] = null;
        $this->assertNull($this->obj['slug']);

        $this->expectException('\InvalidArgumentException');
        $this->obj->setSlug(false);
    }

    /**
     * Set up the service container.
     */
    private function container(): \Pimple\Container
    {
        if (!$this->container instanceof \Pimple\Container) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerModelFactory($container);
            $containerProvider->registerModelCollectionLoader($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
