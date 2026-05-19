<?php

namespace Charcoal\Tests\User\Acl;

// From Pimple
use Pimple\Container;

// From 'charcoal-user'
use Charcoal\User\Acl\Role;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\User\ContainerProvider;

/**
 *
 */
class RoleTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\User\Acl\Role $obj;

    /**
     * Store the service container.
     */
    private ?\Pimple\Container $container = null;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $container = $this->container();

        $this->obj = $container['model/factory']->create(Role::class);
    }

    public function testToString(): void
    {
        $this->assertEquals('', (string)$this->obj);
        $this->obj->ident = 'foobar';
        $this->assertEquals('foobar', (string)$this->obj);

        $this->obj['ident'] = 'foo';
        $this->assertEquals('foo', (string)$this->obj);
    }

    /**
     * Assert that the object's key is the "ident" property.
     */
    public function testKey(): void
    {
        $this->assertEquals('ident', $this->obj->key());
    }

    public function testSetParent(): void
    {
        $ret = $this->obj->setParent('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj['parent']);
    }

    public function testSetAllowed(): void
    {
        $this->assertNull($this->obj['allowed']);
        $ret = $this->obj->setAllowed('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['foo'], $this->obj['allowed']);

        $this->obj->setAllowed(['bar', 'baz']);
        $this->assertSame(['bar', 'baz'], $this->obj['allowed']);
    }

    public function testSuperuser(): void
    {
        $this->assertFalse($this->obj['superuser']);
        $ret = $this->obj->setSuperuser(1);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj['superuser']);
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

            $this->container = $container;
        }

        return $this->container;
    }
}
