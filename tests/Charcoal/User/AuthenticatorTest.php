<?php

namespace Charcoal\User\Tests;

use PHPUnit_Framework_TestCase;

use Psr\Log\NullLogger;
use Cache\Adapter\Void\VoidCachePool;

use Charcoal\Model\Service\MetadataLoader;

use Charcoal\Factory\GenericFactory as Factory;

use Charcoal\User\Authenticator;

class AuthenticatorTest extends PHPUnit_Framework_TestCase
{
    public $obj;

    public function setUp()
    {
        $logger = new NullLogger();

        $metadataLoader = new MetadataLoader([
            'logger'    => $logger,
            'base_path' => realpath(__DIR__.'/../../..'),
            'paths'     => ['metadata'],
            //'config'    => new AppConfig(),
            'cache'     => new VoidCachePool()
        ]);

        $factory = new Factory([
            'arguments' => [[
                'logger'=> $logger,
                'metadata_loader' => $metadataLoader,
                'source_factory'  => new Factory([])
            ]]
        ]);

        $this->obj = new Authenticator([
            'logger'            => $logger,
            'user_type'         => 'charcoal/user/generic-user',
            'user_factory'      => $factory,
            'token_type'        => 'charcoal/user/auth-token',
            'token_factory'     => $factory
        ]);
    }

    public function testConstructor()
    {
        $this->assertInstanceOf(Authenticator::class, $this->obj);
    }

    public function testAuthenticate()
    {
        $ret = $this->obj->authenticate();
        $this->assertNull($ret);
    }

    public function testAuthenticateByPasswordInvalidUsername()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->authenticateByPassword([], '');
    }

    public function testAuthenticateByPasswordInvalidPassword()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->authenticateByPassword('', []);
    }

    public function testAuthenticateByPasswordEmpty()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->authenticateByPassword('', '');
    }

    public function testAuthenticateByPassword()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $this->obj->authenticateByPassword('test', 'password');
    }
}
