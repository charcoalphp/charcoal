<?php

namespace Charcoal\User\Tests;

// From PHPUnit
use PHPUnit_Framework_TestCase;

// From Pimple
use Pimple\Container;

// From 'charcoal-user'
use Charcoal\User\Authenticator;
use Charcoal\User\AuthToken;
use Charcoal\User\GenericUser as User;
use Charcoal\User\Tests\ContainerProvider;

/**
 *
 */
class AuthenticatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tested Class.
     *
     * @var Authenticator
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
        if (session_id()) {
            session_unset();
        }

        $container = $this->container();

        $this->obj = new Authenticator([
            'logger'        => $container['logger'],
            'user_type'     => User::class,
            'user_factory'  => $container['model/factory'],
            'token_type'    => AuthToken::class,
            'token_factory' => $container['model/factory']
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
