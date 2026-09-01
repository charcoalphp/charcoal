<?php

namespace Charcoal\Tests\Admin\User;

// From PSR-3
use Psr\Log\NullLogger;

// From 'charcoal-admin'
use Charcoal\Admin\User\LostPasswordToken;
use Charcoal\Tests\AbstractTestCase;
use Charcoal\Tests\ReflectionsTrait;

/**
 *
 */
class LostPasswordTokenTest extends AbstractTestCase
{
    use ReflectionsTrait;

    /**
     * @var LostPasswordToken
     */
    private $obj;

    /**
     * @return void
     */
    public function setUp(): void
    {
        $this->obj = new LostPasswordToken([
            'logger' => new NullLogger(),
        ]);
    }

    /**
     * @return void
     */
    public function testKeyIsIdent()
    {
        $this->assertEquals('ident', $this->obj->key());
    }

    /**
     * @return void
     */
    public function testGenerateCreatesPublicTokenAndSecret()
    {
        $this->obj->generate('user-1');

        $ident      = $this->obj->ident();
        $plainToken = $this->obj->plainToken();
        $public     = $this->obj->publicToken();

        $this->assertSame(LostPasswordToken::IDENT_BYTES * 2, strlen($ident));
        $this->assertSame(LostPasswordToken::TOKEN_BYTES * 2, strlen($plainToken));
        $this->assertTrue(ctype_xdigit($ident));
        $this->assertTrue(ctype_xdigit($plainToken));
        $this->assertSame($ident . $plainToken, $public);
        $this->assertSame($plainToken, $this->obj->token());
        $this->assertSame('user-1', $this->obj->user());
    }

    /**
     * @return void
     */
    public function testParsePublicToken()
    {
        $this->obj->generate('user-1');
        $parsed = $this->obj->parsePublicToken($this->obj->publicToken());

        $this->assertSame($this->obj->ident(), $parsed['ident']);
        $this->assertSame($this->obj->plainToken(), $parsed['token']);
    }

    /**
     * @return void
     */
    public function testParsePublicTokenRejectsInvalidValue()
    {
        $this->assertNull($this->obj->parsePublicToken('not-a-token'));
        $this->assertNull($this->obj->parsePublicToken(''));
        $this->assertNull($this->obj->parsePublicToken(null));
    }

    /**
     * @return void
     */
    public function testHashTokenUsesPasswordHash()
    {
        $this->obj->generate('user-1');
        $plain = $this->obj->plainToken();

        $this->callMethod($this->obj, 'hashToken');

        $hash = $this->obj->token();
        $this->assertNotSame($plain, $hash);
        $this->assertTrue(password_verify($plain, $hash));
        $this->assertFalse(password_verify('wrong-token', $hash));
    }

    /**
     * @return void
     */
    public function testHashTokenDoesNotRehashValidHash()
    {
        $this->obj->generate('user-1');
        $this->callMethod($this->obj, 'hashToken');
        $hash = $this->obj->token();

        $this->callMethod($this->obj, 'hashToken');
        $this->assertSame($hash, $this->obj->token());
    }
}
