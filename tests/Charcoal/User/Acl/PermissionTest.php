<?php

namespace Charcoal\User\Tests\Acl;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From Pimple
use Pimple\Container;

// From 'charcoal-user'
use Charcoal\User\Acl\Permission;
use Charcoal\User\Tests\ContainerProvider;

/**
 *
 */
class PermissionTest extends PHPUnit_Framework_TestCase
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
     */
    public function setUp()
    {
        $container = $this->container();

        $this->obj = new Permission([
            'container' => $container,
            'logger'    => $container['logger']
        ]);
    }

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
     */
    public function testKey()
    {
        $this->assertEquals('ident', $this->obj->key());
    }

    public function testSetIdent()
    {
        $ret = $this->obj->setIdent('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->ident());

        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->setIdent(false);
    }

    public function testSetName()
    {
        $ret = $this->obj->setName('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', (string)$this->obj->name());
    }

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
