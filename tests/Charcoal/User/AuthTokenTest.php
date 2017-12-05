<?php

namespace Charcoal\User\Tests;

use DateTime;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From Pimple
use Pimple\Container;

// From 'charcoal-user'
use Charcoal\User\AuthToken;
use Charcoal\User\Tests\ContainerProvider;

/**
 *
 */
class AuthTokenTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var AuthToken
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

        $this->obj = $container['model/factory']->create(AuthToken::class);
        $this->obj = new AuthToken([
            # 'container'        => $container,
            'logger'           => $container['logger'],
            # 'property_factory' => $container['property/factory'],
            'metadata_loader'  => $container['metadata/loader']
        ]);
    }

    public function testSetKeyIsIdent()
    {
        $this->assertEquals('ident', $this->obj->key());
    }

    public function testSetIdent()
    {
        $ret = $this->obj->setIdent('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->ident());
    }

    public function testSetToken()
    {
        $ret = $this->obj->setToken('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->token());
    }

    public function testSetUsername()
    {
        $ret = $this->obj->setUsername('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj->username());

        $this->expectException('\Exception');
        $this->obj->setUsername([]);
    }

    public function testSetUsernameIsLowercase()
    {
        $this->obj->setUsername('FÔOBÄR');
        $this->assertEquals('fôobär', $this->obj->username());
    }

    public function testSetExpiry()
    {
        $date = new DateTime('tomorrow');
        $ret = $this->obj->setExpiry($date);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($date, $this->obj->expiry());

        $this->expectException('\Exception');
        $this->obj->setExpiry('fsdjkfsadg');
    }

    public function testSetCreated()
    {
        $date = new DateTime('tomorrow');
        $ret = $this->obj->setCreated($date);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($date, $this->obj->created());

        $this->expectException('\Exception');
        $this->obj->setCreated('fsdjkfsadg');
    }

    public function testSetLastModified()
    {
        $date = new DateTime('tomorrow');
        $ret = $this->obj->setLastModified($date);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($date, $this->obj->lastModified());

        $this->expectException('\Exception');
        $this->obj->setLastModified('fsdjkfsadg');
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
