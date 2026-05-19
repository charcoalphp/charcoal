<?php

namespace Charcoal\Tests\User\Acl;

// From Pimple
use Pimple\Container;

// From 'charcoal-user'
use Charcoal\User\Acl\Permission;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\User\ContainerProvider;

/**
 *
 */
class PermissionTest extends AbstractTestCase
{
    /**
     * Tested Class.
     */
    private \Charcoal\User\Acl\Permission|array $obj;

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

        $this->obj = new Permission([
            'container' => $container,
            'logger'    => $container['logger']
        ]);
    }

    public function testToString(): void
    {
        $this->assertEquals('', (string)$this->obj);
        $this->obj->setIdent('foobar');
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

    public function testSetIdent(): void
    {
        $ret = $this->obj->setIdent('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj['ident']);

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setIdent(false);
    }

    public function testSetName(): void
    {
        $ret = $this->obj->setName('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', (string)$this->obj['name']);
    }

    public function testCastToString(): void
    {
        $this->obj->setIdent('foobar');
        $this->assertEquals('foobar', (string)$this->obj);
        $this->obj->setIdent('baz');
        $this->assertEquals('baz', (string)$this->obj);
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
