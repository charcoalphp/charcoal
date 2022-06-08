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
     *
     * @var Permission
     */
    private $obj;

    /**
     * Store the service container.
     *
     * @var Container
     */
    private $container;

    /**
     * Set up the test.
     *
     * @return void
     */
    public function setUp()
    {
        $container = $this->container();

        $this->obj = new Permission([
            'container' => $container,
            'logger'    => $container['logger']
        ]);
    }

    /**
     * @return void
     */
    public function testToString()
    {
        $this->assertEquals('', (string)$this->obj);
        $this->obj->setIdent('foobar');
        $this->assertEquals('foobar', (string)$this->obj);

        $this->obj['ident'] = 'foo';
        $this->assertEquals('foo', (string)$this->obj);
    }

    /**
     * Assert that the object's key is the "ident" property.
     *
     * @return void
     */
    public function testKey()
    {
        $this->assertEquals('ident', $this->obj->key());
    }

    /**
     * @return void
     */
    public function testSetIdent()
    {
        $ret = $this->obj->setIdent('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj['ident']);

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setIdent(false);
    }

    /**
     * @return void
     */
    public function testSetName()
    {
        $ret = $this->obj->setName('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', (string)$this->obj['name']);
    }

    /**
     * @return void
     */
    public function testCastToString()
    {
        $this->obj->setIdent('foobar');
        $this->assertEquals('foobar', (string)$this->obj);
        $this->obj->setIdent('baz');
        $this->assertEquals('baz', (string)$this->obj);
    }

    /**
     * Set up the service container.
     *
     * @return Container
     */
    private function container()
    {
        if ($this->container === null) {
            $container = new Container();
            $containerProvider = new ContainerProvider();
            $containerProvider->registerBaseServices($container);
            $containerProvider->registerModelFactory($container);

            $this->container = $container;
        }

        return $this->container;
    }
}
