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
        $this->assertTrue($this->obj['enabled']);
        $this->assertEquals('charcoal_user_login', $this->obj['tokenName']);
        $this->assertEquals('15 days', $this->obj['tokenDuration']);
        $this->assertFalse($this->obj['httpsOnly']);
    }

    /**
     * @return void
     */
    public function testSetEnabled()
    {
        $ret = $this->obj->setEnabled(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj['enabled']);
    }

    /**
     * @return void
     */
    public function testSetTokenName()
    {
        $ret = $this->obj->setTokenName('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj['tokenName']);

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setTokenName(false);
    }

    /**
     * @return void
     */
    public function testSetTokenDuration()
    {
        $ret = $this->obj->setTokenDuration('2 month');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('2 month', $this->obj['tokenDuration']);

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setTokenDuration(false);
    }
}
