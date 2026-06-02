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
     */
    private \Charcoal\User\AuthTokenMetadata $obj;

    /**
     * Set up the test.
     */
    protected function setUp(): void
    {
        $this->obj = new AuthTokenMetadata();
    }

    public function testDefaults(): void
    {
        $this->assertTrue($this->obj['enabled']);
        $this->assertEquals('charcoal_user_login', $this->obj['tokenName']);
        $this->assertEquals('15 days', $this->obj['tokenDuration']);
        $this->assertFalse($this->obj['httpsOnly']);
    }

    public function testSetEnabled(): void
    {
        $ret = $this->obj->setEnabled(false);
        $this->assertSame($ret, $this->obj);
        $this->assertFalse($this->obj['enabled']);
    }

    public function testSetTokenName(): void
    {
        $ret = $this->obj->setTokenName('foobar');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('foobar', $this->obj['tokenName']);

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setTokenName(false);
    }

    public function testSetTokenDuration(): void
    {
        $ret = $this->obj->setTokenDuration('2 month');
        $this->assertSame($ret, $this->obj);
        $this->assertEquals('2 month', $this->obj['tokenDuration']);

        $this->expectException(\InvalidArgumentException::class);
        $this->obj->setTokenDuration(false);
    }
}
