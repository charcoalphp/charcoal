<?php

namespace Charcoal\Tests\User;

use DateTime;

// From Pimple
use Pimple\Container;

// From 'charcoal-user'
use Charcoal\User\AuthToken;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\User\ContainerProvider;

/**
 *
 */
class AuthTokenTest extends AbstractTestCase
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
     *
     * @return void
     */
    public function setUp()
    {
        $container = $this->container();

        $this->obj = $container['model/factory']->create(AuthToken::class);
        $this->obj = new AuthToken([
            'logger'          => $container['logger'],
            'metadata_loader' => $container['metadata/loader']
        ]);
    }

    /**
     * @return void
     */
    public function testSetKeyIsIdent()
    {
        $this->assertEquals('ident', $this->obj->key());
    }

    /**
     * @return void
     */
    public function testSetIdent()
    {
        $ret = $this->obj->setIdent('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj['ident']);
    }

    /**
     * @return void
     */
    public function testSetToken()
    {
        $ret = $this->obj->setToken('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj['token']);
    }

    /**
     * @return void
     */
    public function testSetUserId()
    {
        $ret = $this->obj->setUserId('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj['userId']);

        $this->expectException('\Exception');
        $this->obj->setUserId([]);
    }

    /**
     * @return void
     */
    public function testSetExpiry()
    {
        $date = new DateTime('tomorrow');
        $ret = $this->obj->setExpiry($date);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($date, $this->obj['expiry']);

        $this->expectException('\Exception');
        $this->obj->setExpiry('fsdjkfsadg');
    }

    /**
     * @return void
     */
    public function testSetCreated()
    {
        $date = new DateTime('tomorrow');
        $ret = $this->obj->setCreated($date);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($date, $this->obj['created']);

        $this->expectException('\Exception');
        $this->obj->setCreated('fsdjkfsadg');
    }

    /**
     * @return void
     */
    public function testSetLastModified()
    {
        $date = new DateTime('tomorrow');
        $ret = $this->obj->setLastModified($date);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($date, $this->obj['lastModified']);

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
