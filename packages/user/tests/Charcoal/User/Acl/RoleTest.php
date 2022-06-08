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
     *
     * @var Role
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

        $this->obj = new Role([
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
        $this->obj->ident = 'foobar';
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
    public function testSetParent()
    {
        $ret = $this->obj->setParent('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj['parent']);
    }

    /**
     * @return void
     */
    public function testSetAllowed()
    {
        $this->assertNull($this->obj['allowed']);
        $ret = $this->obj->setAllowed('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals(['foo'], $this->obj['allowed']);

        $this->obj->setAllowed(['bar', 'baz']);
        $this->assertSame(['bar', 'baz'], $this->obj['allowed']);
    }

    /**
     * @return void
     */
    public function testSuperuser()
    {
        $this->assertFalse($this->obj['superuser']);
        $ret = $this->obj->setSuperuser(1);
        $this->assertSame($ret, $this->obj);
        $this->assertTrue($this->obj['superuser']);
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
