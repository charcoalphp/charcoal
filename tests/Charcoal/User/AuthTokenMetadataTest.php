<?php

namespace Charcoal\Tests\User;

// From Pimple
use Pimple\Container;

// From 'charcoal-user'
use Charcoal\User\AuthTokenMetadata;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\User\ContainerProvider;

/**
 *
 */
class AuthTokenMetadataTest extends AbstractTestCase
{
    /**
     * Tested Class.
     *
     * @var AuthTokenMetadata
     */
    private $obj;

    /**
     * Set up the test.
     *
     * @return void
     */
    public function setUp()
    {
        $this->obj = new AuthTokenMetadata();
    }

    /**
     * @return void
     */
    public function testDefaults()
    {
        $this->assertTrue($this->obj->enabled());
        $this->assertEquals('charcoal_user_login', $this->obj->cookieName());
        $this->assertEquals('15 days', $this->obj->cookieDuration());
        $this->assertFalse($this->obj->httpsOnly());
    }

    /**
     * @return void
     */
    public function testSetEnabled()
    {
        $ret = $this->obj->setEnabled(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj->enabled());
    }

    /**
     * @return void
     */
    public function testSetCookieName()
    {
        $ret = $this->obj->setCookieName('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj->cookieName());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setCookieName(false);
    }

    /**
     * @return void
     */
    public function testSetCookieDuration()
    {
        $ret = $this->obj->setCookieDuration('2 month');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('2 month', $this->obj->cookieDuration());

        $this->expectException('\InvalidArgumentException');
        $this->obj->setCookieDuration(false);
    }
}
