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
     */
    private \Charcoal\User\AuthToken $obj;

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

        $this->obj = $container['model/factory']->create(AuthToken::class);
        $this->obj = new AuthToken([
            'logger'          => $container['logger'],
            'metadata_loader' => $container['metadata/loader']
        ]);
    }

    public function testSetKeyIsIdent(): void
    {
        $this->assertEquals('ident', $this->obj->key());
    }

    public function testSetIdent(): void
    {
        $ret = $this->obj->setIdent('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj['ident']);
    }

    public function testSetToken(): void
    {
        $ret = $this->obj->setToken('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj['token']);
    }

    public function testSetUserId(): void
    {
        $ret = $this->obj->setUserId('foo');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foo', $this->obj['userId']);

        $this->expectException('\Exception');
        $this->obj->setUserId([]);
    }

    public function testSetExpiry(): void
    {
        $date = new DateTime('tomorrow');
        $ret = $this->obj->setExpiry($date);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($date, $this->obj['expiry']);

        $this->expectException('\Exception');
        $this->obj->setExpiry('fsdjkfsadg');
    }

    public function testSetCreated(): void
    {
        $date = new DateTime('tomorrow');
        $ret = $this->obj->setCreated($date);
        $this->assertSame($ret, $this->obj);
        $this->assertEquals($date, $this->obj['created']);

        $this->expectException('\Exception');
        $this->obj->setCreated('fsdjkfsadg');
    }

    public function testSetLastModified(): void
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
